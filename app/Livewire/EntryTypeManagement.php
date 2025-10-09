<?php

namespace App\Livewire;

use App\Models\EntryType;
use Livewire\Component;
use Livewire\WithPagination;

class EntryTypeManagement extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editingEntryType = false;
    public $search = '';
    public $filterBaseType = '';
    public $filterNumbering = '';
    
    public $form = [
        'label' => '',
        'name' => '',
        'description' => '',
        'base_type' => 0,
        'numbering' => 1,
        'prefix' => '',
        'suffix' => '',
        'zero_padding' => 0,
        'restriction_bankcash' => 1,
    ];

    public function openAddEntryTypeModal()
    {
        $this->resetForm();
        $this->showModal = true;
        session()->flash('message', 'Entry type modal opened!');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->editingEntryType = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->form = [
            'label' => '',
            'name' => '',
            'description' => '',
            'base_type' => 0,
            'numbering' => 1,
            'prefix' => '',
            'suffix' => '',
            'zero_padding' => 0,
            'restriction_bankcash' => 1,
        ];
        $this->editingEntryType = false;
        $this->resetErrorBag();
    }

    public function addEntryType()
    {
        $this->validate([
            'form.label' => 'required|string|max:255|unique:entry_types,label',
            'form.name' => 'required|string|max:255',
            'form.description' => 'required|string|max:500',
            'form.base_type' => 'required|integer|in:0,1,2,3',
            'form.numbering' => 'required|integer|in:1,2',
            'form.prefix' => 'nullable|string|max:50',
            'form.suffix' => 'nullable|string|max:50',
            'form.zero_padding' => 'required|integer|min:0|max:10',
            'form.restriction_bankcash' => 'required|integer|in:1,2,3',
        ]);

        EntryType::create([
            'label' => $this->form['label'],
            'name' => $this->form['name'],
            'description' => $this->form['description'],
            'base_type' => $this->form['base_type'],
            'numbering' => $this->form['numbering'],
            'prefix' => $this->form['prefix'] ?: null,
            'suffix' => $this->form['suffix'] ?: null,
            'zero_padding' => $this->form['zero_padding'],
            'restriction_bankcash' => $this->form['restriction_bankcash'],
        ]);

        $this->resetForm();
        $this->showModal = false;
        session()->flash('message', 'Entry type created successfully.');
    }

    public function updateEntryType()
    {
        // Implementation for updating entry types
        session()->flash('message', 'Update functionality coming soon.');
    }

    public function deleteEntryType($id)
    {
        $entryType = EntryType::findOrFail($id);
        
        // Check if entry type is being used
        if ($entryType->entries()->count() > 0) {
            session()->flash('error', 'Cannot delete entry type that is being used.');
            return;
        }
        
        $entryType->delete();
        session()->flash('message', 'Entry type deleted successfully.');
    }

    public function editEntryType($id)
    {
        // Implementation for editing entry types
        session()->flash('message', 'Edit functionality coming soon.');
    }

    public function render()
    {
        $query = EntryType::query();
        
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('label', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
        }
        
        if ($this->filterBaseType !== '') {
            $query->where('base_type', $this->filterBaseType);
        }
        
        if ($this->filterNumbering !== '') {
            $query->where('numbering', $this->filterNumbering);
        }
        
        $entryTypes = $query->orderBy('name')->paginate(10);

        return view('livewire.entry-type-management', [
            'entryTypes' => $entryTypes,
        ]);
    }
}
