<?php

namespace App\Livewire;

use App\Models\Entry;
use App\Models\EntryType;
use App\Models\Tag;
use App\Models\Ledger;
use App\Models\EntryItem;
use Livewire\Component;
use Livewire\WithPagination;

class EntrySearch extends Component
{
    use WithPagination;

    // Search form properties
    public $ledgerIds = [0]; // Default to "ALL"
    public $entrytypeIds = [0]; // Default to "ALL"
    public $entryNumberRestriction = 1; // Equal to
    public $entryNumber1 = '';
    public $entryNumber2 = '';
    public $amountDc = '0'; // ANY
    public $amountRestriction = 1; // Equal to
    public $amount1 = '';
    public $amount2 = '';
    public $fromDate = '';
    public $toDate = '';
    public $tagIds = [0]; // Default to "ALL"
    public $narration = '';
    
    public $showEntries = false;
    
    protected $layout = 'components.layouts.app';

    public function mount()
    {
        // Set default date range to current financial year
        $this->fromDate = \Carbon\Carbon::now()->startOfYear()->format('Y-m-d');
        $this->toDate = \Carbon\Carbon::now()->endOfYear()->format('Y-m-d');
    }

    public function search()
    {
        $this->showEntries = true;
        $this->resetPage();
    }

    public function clear()
    {
        $this->reset([
            'ledgerIds', 'entrytypeIds', 'entryNumberRestriction', 'entryNumber1', 'entryNumber2',
            'amountDc', 'amountRestriction', 'amount1', 'amount2', 'fromDate', 'toDate',
            'tagIds', 'narration', 'showEntries'
        ]);
        
        // Reset to defaults
        $this->ledgerIds = [0];
        $this->entrytypeIds = [0];
        $this->entryNumberRestriction = 1;
        $this->amountDc = '0';
        $this->amountRestriction = 1;
        $this->tagIds = [0];
        
        // Set default date range
        $this->fromDate = \Carbon\Carbon::now()->startOfYear()->format('Y-m-d');
        $this->toDate = \Carbon\Carbon::now()->endOfYear()->format('Y-m-d');
        
        $this->resetPage();
    }

    public function render()
    {
        // Load dropdown options
        $ledgers = Ledger::with('group')->orderBy('name')->get();
        $entryTypes = EntryType::orderBy('name')->get();
        $tags = Tag::orderBy('title')->get();
        
        // Build ledger options for multi-select
        $ledgerOptions = [0 => '(ALL)'];
        foreach ($ledgers as $ledger) {
            $ledgerOptions[$ledger->id] = "[{$ledger->code}] {$ledger->name}";
        }
        
        // Build entrytype options for multi-select
        $entrytypeOptions = [0 => '(ALL)'];
        foreach ($entryTypes as $entryType) {
            $entrytypeOptions[$entryType->id] = $entryType->name;
        }
        
        // Build tag options for multi-select
        $tagOptions = [0 => '(ALL)'];
        foreach ($tags as $tag) {
            $tagOptions[$tag->id] = $tag->title;
        }
        
        $entries = collect();
        
        if ($this->showEntries) {
            $entries = $this->performSearch();
        }

        return view('livewire.entry-search', [
            'ledgerOptions' => $ledgerOptions,
            'entrytypeOptions' => $entrytypeOptions,
            'tagOptions' => $tagOptions,
            'entries' => $entries,
        ]);
    }

    private function performSearch()
    {
        $query = Entry::with(['entryType', 'tag', 'entryItems.ledger'])
            ->join('entryitems', 'entries.id', '=', 'entryitems.entry_id')
            ->select('entries.*', 'entryitems.id as entryitem_id', 'entryitems.ledger_id as entryitem_ledger_id', 
                    'entryitems.amount as entryitem_amount', 'entryitems.dc as entryitem_dc', 
                    'entryitems.reconciliation_date as entryitem_reconciliation_date');

        // Ledger filter
        if (!empty($this->ledgerIds) && !in_array(0, $this->ledgerIds)) {
            $query->whereIn('entryitems.ledger_id', $this->ledgerIds);
        }

        // Entrytype filter
        if (!empty($this->entrytypeIds) && !in_array(0, $this->entrytypeIds)) {
            $query->whereIn('entries.entrytype_id', $this->entrytypeIds);
        }

        // Entry number filter
        if (!empty($this->entryNumber1)) {
            switch ($this->entryNumberRestriction) {
                case 1: // Equal to
                    $query->where('entries.number', $this->entryNumber1);
                    break;
                case 2: // Less than or equal to
                    $query->where('entries.number', '<=', $this->entryNumber1);
                    break;
                case 3: // Greater than or equal to
                    $query->where('entries.number', '>=', $this->entryNumber1);
                    break;
                case 4: // In between
                    $query->where('entries.number', '>=', $this->entryNumber1);
                    if (!empty($this->entryNumber2)) {
                        $query->where('entries.number', '<=', $this->entryNumber2);
                    }
                    break;
            }
        }

        // Dr/Cr filter
        if ($this->amountDc === 'D') {
            $query->where('entryitems.dc', 'D');
        } elseif ($this->amountDc === 'C') {
            $query->where('entryitems.dc', 'C');
        }

        // Amount filter
        if (!empty($this->amount1)) {
            switch ($this->amountRestriction) {
                case 1: // Equal to
                    $query->where('entryitems.amount', $this->amount1);
                    break;
                case 2: // Less than or equal to
                    $query->where('entryitems.amount', '<=', $this->amount1);
                    break;
                case 3: // Greater than or equal to
                    $query->where('entryitems.amount', '>=', $this->amount1);
                    break;
                case 4: // In between
                    $query->where('entryitems.amount', '>=', $this->amount1);
                    if (!empty($this->amount2)) {
                        $query->where('entryitems.amount', '<=', $this->amount2);
                    }
                    break;
            }
        }

        // Date range filter
        if (!empty($this->fromDate)) {
            $query->where('entries.date', '>=', $this->fromDate);
        }
        if (!empty($this->toDate)) {
            $query->where('entries.date', '<=', $this->toDate);
        }

        // Tag filter
        if (!empty($this->tagIds) && !in_array(0, $this->tagIds)) {
            $query->whereIn('entries.tag_id', $this->tagIds);
        }

        // Narration filter
        if (!empty($this->narration)) {
            $query->where('entries.narration', 'like', '%' . $this->narration . '%');
        }

        return $query->orderBy('entries.date', 'asc')
                    ->orderBy('entries.id', 'asc')
                    ->paginate(15);
    }

    public function getEntryTypeName($entrytypeId)
    {
        $entryType = EntryType::find($entrytypeId);
        return $entryType ? $entryType->name : '';
    }

    public function getTagName($tagId)
    {
        if (!$tagId) return '';
        $tag = Tag::find($tagId);
        return $tag ? $tag->title : '';
    }

    public function formatEntryNumber($number, $entrytypeId)
    {
        $entryType = EntryType::find($entrytypeId);
        $prefix = $entryType ? $entryType->prefix : '';
        return $prefix . $number;
    }

    public function getLedgerDetails($entryId, $entryItemId)
    {
        $entryItems = EntryItem::with('ledger')
            ->where('entry_id', $entryId)
            ->get();

        $details = [];
        foreach ($entryItems as $item) {
            $details[] = [
                'dc' => $item->dc,
                'ledger_code' => $item->ledger->code,
                'ledger_name' => $item->ledger->name,
                'amount' => $item->amount,
                'is_current' => $item->id == $entryItemId
            ];
        }

        return $details;
    }

    public function formatAmount($amount, $dc)
    {
        return ($dc == 'D' ? 'Dr' : 'Cr') . ' ' . number_format($amount, 2);
    }
}
