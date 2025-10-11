<?php

namespace App\Livewire;

use App\Models\Entry;
use App\Models\EntryType;
use App\Models\Tag;
use App\Models\Ledger;
use Livewire\Component;
use Livewire\WithPagination;

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
        return collect($this->form['entry_items'])->where('dc', 'D')->sum('amount');
    }
    
    public function getCrTotalProperty()
    {
        return collect($this->form['entry_items'])->where('dc', 'C')->sum('amount');
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
            'amount' => 0,
            'dc' => 'D',
            'reconciliation_date' => ''
        ];
    }

    public function removeEntryItem($index)
    {
        unset($this->form['entry_items'][$index]);
        $this->form['entry_items'] = array_values($this->form['entry_items']);
    }

    public function addEntry()
    {
        $this->validate([
            'form.date' => 'required|date',
            'form.entrytype_id' => 'required|exists:entry_types,id',
            'form.number' => 'nullable|integer',
            'form.tag_id' => 'nullable|exists:tags,id',
            'form.narration' => 'nullable|string|max:500',
            'form.entry_items' => 'required|array|min:2',
            'form.entry_items.*.ledger_id' => 'required|exists:ledgers,id',
            'form.entry_items.*.amount' => 'required|numeric|min:0.01',
            'form.entry_items.*.dc' => 'required|in:D,C',
            'form.entry_items.*.reconciliation_date' => 'nullable|date',
        ]);

        // Check if entry is balanced
        if ($this->drTotal != $this->crTotal) {
            $this->addError('form.entry_items', 'Entry must be balanced. Total debits must equal total credits.');
            return;
        }

        $entry = Entry::create([
            'date' => $this->form['date'],
            'entrytype_id' => $this->form['entrytype_id'],
            'number' => $this->form['number'],
            'tag_id' => $this->form['tag_id'] ?: null,
            'narration' => $this->form['narration'],
            'dr_total' => $this->drTotal,
            'cr_total' => $this->crTotal,
        ]);

        // Create entry items
        foreach ($this->form['entry_items'] as $item) {
            $entry->entryItems()->create([
                'ledger_id' => $item['ledger_id'],
                'amount' => $item['amount'],
                'dc' => $item['dc'],
                'reconciliation_date' => $item['reconciliation_date'] ?: null,
            ]);
        }

        $this->resetForm();
        $this->showModal = false;
        session()->flash('success', 'Entry created successfully!');
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
