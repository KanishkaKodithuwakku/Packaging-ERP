<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Ledger;
use App\Models\Entry;
use App\Models\EntryItem;
use App\Models\EntryType;
use App\Models\Tag;
use Carbon\Carbon;
use Livewire\WithPagination;

class ReconciliationReport extends Component
{
    use WithPagination;

    public $selectedLedgerId = null;
    public $startDate = null;
    public $endDate = null;
    public $showAll = false;
    public $companyName = 'Kings Packaging ERP';
    public $subtitle = '';
    public $openingTitle = '';
    public $closingTitle = '';
    public $recPendingTitle = '';
    
    // Report data
    public $ledger = null;
    public $openingBalance = ['amount' => 0, 'dc' => 'D'];
    public $closingBalance = ['amount' => 0, 'dc' => 'D'];
    public $reconciliationPending = ['dr_total' => 0, 'cr_total' => 0];
    public $entries = [];
    public $showEntries = false;
    public $options = false;

    // Available ledgers for dropdown (only reconciliation enabled ledgers)
    public $availableLedgers = [];

    // Reconciliation form data
    public $reconciliationData = [];

    public function mount()
    {
        // Set default dates
        $this->startDate = now()->startOfYear()->format('Y-m-d');
        $this->endDate = now()->endOfYear()->format('Y-m-d');
        
        // Load available ledgers (only reconciliation enabled)
        $this->loadAvailableLedgers();
        
        // Check if ledger ID is provided in URL
        if (request()->has('ledgerid')) {
            $this->selectedLedgerId = request()->get('ledgerid');
            $this->generateReconciliationReport();
        }
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['selectedLedgerId', 'startDate', 'endDate', 'showAll'])) {
            if ($this->selectedLedgerId) {
                $this->generateReconciliationReport();
            }
        }
    }

    public function loadAvailableLedgers()
    {
        // Load only reconciliation enabled ledgers with their group information
        $this->availableLedgers = Ledger::with('group')
            ->where('reconciliation', true)
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function ($ledger) {
                $groupName = $ledger->group ? $ledger->group->name : 'Unknown';
                $label = "[{$ledger->code}] {$ledger->name} ({$groupName})";
                return [$ledger->id => $label];
            })
            ->toArray();
    }

    public function generateReconciliationReport()
    {
        if (!$this->selectedLedgerId) {
            $this->showEntries = false;
            return;
        }

        // Find the selected ledger
        $this->ledger = Ledger::find($this->selectedLedgerId);
        if (!$this->ledger) {
            $this->showEntries = false;
            return;
        }

        // Set subtitle
        $startDate = $this->startDate ? Carbon::parse($this->startDate)->format('d-M-Y') : now()->startOfYear()->format('d-M-Y');
        $endDate = $this->endDate ? Carbon::parse($this->endDate)->format('d-M-Y') : now()->endOfYear()->format('d-M-Y');
        $this->subtitle = "Reconciliation report for [{$this->ledger->code}] {$this->ledger->name} from {$startDate} to {$endDate}";

        // Set opening and closing titles
        $this->openingTitle = "Opening balance as on {$startDate}";
        $this->closingTitle = "Closing balance as on {$endDate}";

        // Set reconciliation pending title
        if (!$this->startDate && !$this->endDate) {
            $this->recPendingTitle = "Reconciliation pending from " . now()->startOfYear()->format('d-M-Y') . " to " . now()->endOfYear()->format('d-M-Y');
        } else if ($this->startDate && $this->endDate) {
            $this->recPendingTitle = "Reconciliation pending from {$startDate} to {$endDate}";
        } else if (!$this->startDate) {
            $this->recPendingTitle = "Reconciliation pending from " . now()->startOfYear()->format('d-M-Y') . " to {$endDate}";
        } else if (!$this->endDate) {
            $this->recPendingTitle = "Reconciliation pending from {$startDate} to " . now()->endOfYear()->format('d-M-Y');
        }

        // Calculate opening balance
        $this->openingBalance = $this->ledger->openingBalance($this->startDate);
        
        // Calculate closing balance
        $this->closingBalance = $this->ledger->closingBalance($this->startDate, $this->endDate);

        // Calculate reconciliation pending balance
        $this->reconciliationPending = $this->ledger->reconciliationPending($this->startDate, $this->endDate);

        // Load entries for the selected ledger and date range
        $this->loadEntries();
        
        $this->showEntries = true;
        $this->options = true;
    }

    private function loadEntries()
    {
        $query = EntryItem::with(['entry.entryType', 'entry.tag', 'ledger'])
            ->where('ledger_id', $this->selectedLedgerId);

        // Apply date filters
        if ($this->startDate) {
            $query->whereHas('entry', function ($q) {
                $q->where('date', '>=', $this->startDate);
            });
        }
        
        if ($this->endDate) {
            $query->whereHas('entry', function ($q) {
                $q->where('date', '<=', $this->endDate);
            });
        }

        // Apply reconciliation filter
        if (!$this->showAll) {
            $query->whereNull('reconciliation_date');
        }

        // Order by date DESCENDING (newest first) - same as Webzash
        $query->whereHas('entry', function ($q) {
            $q->orderBy('date', 'desc');
        });

        $this->entries = $query->get();

        // Initialize reconciliation data
        $this->reconciliationData = [];
        foreach ($this->entries as $index => $entryItem) {
            $this->reconciliationData[$index] = [
                'id' => $entryItem->id,
                'recdate' => $entryItem->reconciliation_date ? $entryItem->reconciliation_date->format('Y-m-d') : ''
            ];
        }
    }

    public function reconcile()
    {
        // Update reconciliation dates
        foreach ($this->reconciliationData as $index => $data) {
            if (!empty($data['id']) && !empty($data['recdate'])) {
                $entryItem = EntryItem::find($data['id']);
                if ($entryItem) {
                    $entryItem->update(['reconciliation_date' => $data['recdate']]);
                }
            }
        }

        // Refresh the report
        $this->generateReconciliationReport();
        
        session()->flash('message', 'Reconciliation updated successfully!');
    }

    public function formatCurrency($dc, $amount)
    {
        $formattedAmount = number_format($amount, 2);
        return $dc . ' ' . $formattedAmount;
    }

    public function formatEntryNumber($number, $entryTypeId)
    {
        $entryType = EntryType::find($entryTypeId);
        if ($entryType) {
            return $entryType->prefix . str_pad($number, $entryType->zero_padding, '0', STR_PAD_LEFT) . $entryType->suffix;
        }
        return $number;
    }

    public function getEntryTypeName($entryTypeId)
    {
        $entryType = EntryType::find($entryTypeId);
        return $entryType ? $entryType->name : 'Unknown';
    }

    public function getTagName($tagId)
    {
        if (!$tagId) return '';
        $tag = Tag::find($tagId);
        return $tag ? $tag->name : '';
    }

    public function render()
    {
        return view('livewire.reconciliation-report');
    }
}
