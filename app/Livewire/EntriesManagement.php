<?php

namespace App\Livewire;

use App\Models\Entry;
use App\Models\EntryType;
use App\Models\Tag;
use App\Models\Ledger;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;

class EntriesManagement extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editingEntry = false;
    public $selectedEntryType = '';
    public $search = '';
    public $filterEntryType = '';
    public $filterTag = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';
    public $showAll = 'all';
    
    // Form data
    public $form = [
        'date' => '',
        'entrytype_id' => '',
        'number' => '',
        'tag_id' => '',
        'narration' => '',
        'entry_items' => []
    ];
    
    // Computed properties
    public function getDrTotalProperty()
    {
        return collect($this->form['entry_items'])->sum('dr_amount');
    }
    
    public function getCrTotalProperty()
    {
        return collect($this->form['entry_items'])->sum('cr_amount');
    }

    public function mount()
    {
        $this->form['date'] = now()->format('Y-m-d');
    }

    public function openAddEntryModal($entryTypeId = null)
    {
        $this->resetForm();
        if ($entryTypeId) {
            $this->form['entrytype_id'] = $entryTypeId;
        }
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->editingEntry = false;
        $this->resetForm();
    }

    public function addEntryItem()
    {
        $this->form['entry_items'][] = [
            'ledger_id' => '',
            'dr_amount' => 0,
            'cr_amount' => 0,
            'dc' => 'D',
            'reconciliation_date' => ''
        ];
    }

    public function removeEntryItem($index)
    {
        unset($this->form['entry_items'][$index]);
        $this->form['entry_items'] = array_values($this->form['entry_items']);
    }

    public function updatedFormEntryItems($value, $index)
    {
        // Handle when the Dr/Cr dropdown changes - clear amounts when switching
        if (str_contains($index, '.dc')) {
            $itemIndex = explode('.', $index)[1];
            if (isset($this->form['entry_items'][$itemIndex])) {
                // Clear amounts when switching between Dr/Cr
                $this->form['entry_items'][$itemIndex]['dr_amount'] = 0;
                $this->form['entry_items'][$itemIndex]['cr_amount'] = 0;
            }
        }
    }

    public function testSubmit()
    {
        Log::info('testSubmit method called!');
        session()->flash('success', 'Test submit works!');
    }

    public function addEntry()
    {
        // Debug: Log the form data
        Log::info('addEntry method called!');
        Log::info('Form data before validation:', $this->form);
        Log::info('Dr Total: ' . $this->drTotal);
        Log::info('Cr Total: ' . $this->crTotal);
        Log::info('Entry items count: ' . count($this->form['entry_items']));

        try {
            $this->validate([
                'form.date' => 'required|date',
                'form.entrytype_id' => 'required|exists:entrytypes,id',
                'form.number' => 'nullable|integer',
                'form.tag_id' => 'nullable|exists:tags,id',
                'form.narration' => 'nullable|string|max:500',
                'form.entry_items' => 'required|array|min:2',
                'form.entry_items.*.ledger_id' => 'required|exists:ledgers,id',
                'form.entry_items.*.dr_amount' => 'nullable|numeric|min:0',
                'form.entry_items.*.cr_amount' => 'nullable|numeric|min:0',
                'form.entry_items.*.dc' => 'required|in:D,C',
                'form.entry_items.*.reconciliation_date' => 'nullable|date',
            ]);
            Log::info('Validation passed successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed: ' . json_encode($e->errors()));
            throw $e;
        }

        // Custom validation: ensure each entry item has a non-zero amount
        foreach ($this->form['entry_items'] as $index => $item) {
            $drAmount = $item['dr_amount'] ?? 0;
            $crAmount = $item['cr_amount'] ?? 0;
            
            if ($drAmount <= 0 && $crAmount <= 0) {
                $this->addError("form.entry_items.{$index}.dr_amount", 'Either debit or credit amount must be greater than 0.');
                return;
            }
            
            // Ensure only one amount is set based on DC selection
            if ($item['dc'] == 'D' && $crAmount > 0) {
                $this->addError("form.entry_items.{$index}.cr_amount", 'Credit amount should be 0 for debit entries.');
                return;
            }
            
            if ($item['dc'] == 'C' && $drAmount > 0) {
                $this->addError("form.entry_items.{$index}.dr_amount", 'Debit amount should be 0 for credit entries.');
                return;
            }
        }

        // Check if entry is balanced
        if ($this->drTotal != $this->crTotal) {
            $this->addError('form.entry_items', 'Entry must be balanced. Total debits must equal total credits.');
            return;
        }

        try {
            Log::info('Attempting to create entry...');
            $entry = Entry::create([
                'date' => $this->form['date'],
                'entrytype_id' => $this->form['entrytype_id'],
                'number' => $this->form['number'],
                'tag_id' => $this->form['tag_id'] ?: null,
                'narration' => $this->form['narration'],
                'dr_total' => $this->drTotal,
                'cr_total' => $this->crTotal,
            ]);
            Log::info('Entry created successfully with ID: ' . $entry->id);
        } catch (\Exception $e) {
            Log::error('Failed to create entry: ' . $e->getMessage());
            session()->flash('error', 'Failed to create entry: ' . $e->getMessage());
            return;
        }

        // Create entry items
        try {
            Log::info('Creating entry items...');
            foreach ($this->form['entry_items'] as $index => $item) {
                $drAmount = $item['dr_amount'] ?? 0;
                $crAmount = $item['cr_amount'] ?? 0;
                
                // Determine the amount and DC based on which amount is greater than 0
                if ($drAmount > 0) {
                    $amount = $drAmount;
                    $dc = 'D';
                } elseif ($crAmount > 0) {
                    $amount = $crAmount;
                    $dc = 'C';
                } else {
                    // Skip items with no amount (this shouldn't happen due to validation)
                    Log::warning("Skipping entry item {$index} - no amount");
                    continue;
                }
                
                Log::info("Creating entry item {$index}: ledger_id={$item['ledger_id']}, amount={$amount}, dc={$dc}");
                $entry->entryItems()->create([
                    'ledger_id' => $item['ledger_id'],
                    'amount' => $amount,
                    'dc' => $dc,
                    'reconciliation_date' => $item['reconciliation_date'] ?: null,
                ]);
            }
            Log::info('Entry items created successfully');
        } catch (\Exception $e) {
            Log::error('Failed to create entry items: ' . $e->getMessage());
            session()->flash('error', 'Failed to create entry items: ' . $e->getMessage());
            return;
        }

        $this->resetForm();
        $this->showModal = false;
        session()->flash('success', 'Entry created successfully!');
        
        // Debug: Log final success
        Log::info('Entry creation completed successfully with ID: ' . $entry->id);
        Log::info('Total entry items created: ' . $entry->entryItems()->count());
    }

    public function updateEntry()
    {
        // Implementation for updating entries
        session()->flash('message', 'Update functionality coming soon.');
    }

    public function viewEntry($id)
    {
        // Implementation for viewing entries
        session()->flash('message', 'View functionality coming soon.');
    }

    public function editEntry($id)
    {
        // Implementation for editing entries
        session()->flash('message', 'Edit functionality coming soon.');
    }

    public function deleteEntry($id)
    {
        $entry = Entry::findOrFail($id);
        
        // Delete associated entry items
        $entry->entryItems()->delete();
        $entry->delete();
        
        session()->flash('success', 'Entry deleted successfully.');
    }

    private function resetForm()
    {
        $this->form = [
            'date' => now()->format('Y-m-d'),
            'entrytype_id' => '',
            'number' => '',
            'tag_id' => '',
            'narration' => '',
            'entry_items' => []
        ];
        $this->editingEntry = false;
        $this->resetErrorBag();
    }

    public function render()
    {
        $query = Entry::with(['entryType', 'tag', 'entryItems.ledger']);
        
        if ($this->search) {
            $query->where('narration', 'like', '%' . $this->search . '%')
                  ->orWhere('number', 'like', '%' . $this->search . '%');
        }
        
        if ($this->filterEntryType) {
            $query->where('entrytype_id', $this->filterEntryType);
        }
        
        if ($this->filterTag) {
            $query->where('tag_id', $this->filterTag);
        }
        
        if ($this->filterDateFrom) {
            $query->where('date', '>=', $this->filterDateFrom);
        }
        
        if ($this->filterDateTo) {
            $query->where('date', '<=', $this->filterDateTo);
        }
        
        $entries = $query->orderBy('date', 'desc')
                        ->orderBy('created_at', 'desc')
                        ->paginate(15);

        $entryTypes = EntryType::orderBy('name')->get();
        $tags = Tag::orderBy('title')->get();
        $ledgers = Ledger::with('group')->orderBy('name')->get();

        return view('livewire.entries-management', [
            'entries' => $entries,
            'entryTypes' => $entryTypes,
            'tags' => $tags,
            'ledgers' => $ledgers,
        ]);
    }
}
