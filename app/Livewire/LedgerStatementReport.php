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

class LedgerStatementReport extends Component
{
    use WithPagination;

    public $selectedLedgerId = null;
    public $startDate = null;
    public $endDate = null;
    public $companyName = 'Kings Packaging ERP';
    public $subtitle = '';
    public $openingTitle = '';
    public $closingTitle = '';
    
    // Report data
    public $ledger = null;
    public $openingBalance = ['amount' => 0, 'dc' => 'D'];
    public $closingBalance = ['amount' => 0, 'dc' => 'D'];
    public $currentOpeningBalance = ['amount' => 0, 'dc' => 'D'];
    public $entries = [];
    public $totalDr = 0;
    public $totalCr = 0;
    public $showEntries = false;
    public $options = false;

    // Available ledgers for dropdown
    public $availableLedgers = [];

    public function mount()
    {
        // Set default dates
        $this->startDate = now()->startOfYear()->format('Y-m-d');
        $this->endDate = now()->endOfYear()->format('Y-m-d');
        
        // Load available ledgers
        $this->loadAvailableLedgers();
        
        // Check if ledger ID is provided in URL
        if (request()->has('ledgerid')) {
            $this->selectedLedgerId = request()->get('ledgerid');
            $this->generateLedgerStatement();
        }
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['selectedLedgerId', 'startDate', 'endDate'])) {
            if ($this->selectedLedgerId) {
                $this->generateLedgerStatement();
            }
        }
    }

    public function loadAvailableLedgers()
    {
        // Load all ledgers with their group information
        $this->availableLedgers = Ledger::with('group')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function ($ledger) {
                $groupName = $ledger->group ? $ledger->group->name : 'Unknown';
                $label = "[{$ledger->code}] {$ledger->name} ({$groupName})";
                return [$ledger->id => $label];
            })
            ->toArray();
    }

    public function generateLedgerStatement()
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
        $this->subtitle = "Ledger statement for [{$this->ledger->code}] {$this->ledger->name} from {$startDate} to {$endDate}";

        // Set opening and closing titles
        $this->openingTitle = "Opening balance as on {$startDate}";
        $this->closingTitle = "Closing balance as on {$endDate}";

        // Calculate opening balance
        $this->openingBalance = $this->ledger->openingBalance($this->startDate);
        
        // Calculate closing balance
        $this->closingBalance = $this->ledger->closingBalance($this->startDate, $this->endDate);

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

        // Order by date
        $query->whereHas('entry', function ($q) {
            $q->orderBy('date', 'asc');
        });

        $this->entries = $query->get();

        // Calculate running balance
        $this->calculateRunningBalance();
    }

    private function calculateRunningBalance()
    {
        $currentBalance = $this->openingBalance;
        $this->totalDr = 0;
        $this->totalCr = 0;

        foreach ($this->entries as $entryItem) {
            if ($entryItem->dc == 'D') {
                $this->totalDr += $entryItem->amount;
                // For debit, increase debit balance
                if ($currentBalance['dc'] == 'D') {
                    $currentBalance['amount'] = bcadd($currentBalance['amount'], $entryItem->amount, 2);
                } else {
                    $diff = bcsub($entryItem->amount, $currentBalance['amount'], 2);
                    if (bccomp($diff, 0, 2) > 0) {
                        $currentBalance['amount'] = $diff;
                        $currentBalance['dc'] = 'D';
                    } else {
                        $currentBalance['amount'] = bcsub($currentBalance['amount'], $entryItem->amount, 2);
                        $currentBalance['dc'] = 'C';
                    }
                }
            } else {
                $this->totalCr += $entryItem->amount;
                // For credit, increase credit balance
                if ($currentBalance['dc'] == 'C') {
                    $currentBalance['amount'] = bcadd($currentBalance['amount'], $entryItem->amount, 2);
                } else {
                    $diff = bcsub($entryItem->amount, $currentBalance['amount'], 2);
                    if (bccomp($diff, 0, 2) > 0) {
                        $currentBalance['amount'] = $diff;
                        $currentBalance['dc'] = 'C';
                    } else {
                        $currentBalance['amount'] = bcsub($currentBalance['amount'], $entryItem->amount, 2);
                        $currentBalance['dc'] = 'D';
                    }
                }
            }
            
            // Store the running balance for this entry
            $entryItem->running_balance = [
                'amount' => $currentBalance['amount'],
                'dc' => $currentBalance['dc']
            ];
        }
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
        return view('livewire.ledger-statement-report');
    }
}
