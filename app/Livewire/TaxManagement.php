<?php

namespace App\Livewire;

use App\Models\Tax;
use Livewire\Component;
use Livewire\WithPagination;

class TaxManagement extends Component
{
    use WithPagination;

    protected $layout = 'components.layouts.app';

    public $showModal = false;
    public $editing = false;
    public $taxId;

    // Delete confirmation modal
    public $showDeleteConfirmModal = false;
    public $taxToDelete = null;

    // Search and filters
    public $search = '';
    public $filterStatus = 'all'; // all, active, inactive

    // Form fields
    public $description;
    public $abbreviation;
    public $percentage;
    public $reverse_calculation;
    public $tax_label;
    public $status = 1;

    protected $rules = [
        'description' => 'required|string|max:255',
        'abbreviation' => 'required|string|max:50|unique:taxes,abbreviation',
        'percentage' => 'required|numeric|min:0|max:100',
        'reverse_calculation' => 'nullable|numeric|min:0',
        'tax_label' => 'nullable|string|max:255',
        'status' => 'required|integer|in:0,1',
    ];

    protected $messages = [
        'description.required' => 'Tax description is required.',
        'abbreviation.required' => 'Tax abbreviation is required.',
        'abbreviation.unique' => 'This tax abbreviation already exists.',
        'percentage.required' => 'Tax percentage is required.',
        'percentage.numeric' => 'Tax percentage must be a number.',
        'percentage.min' => 'Tax percentage must be greater than or equal to 0.',
        'percentage.max' => 'Tax percentage must be less than or equal to 100.',
        'status.required' => 'Tax status is required.',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->description = '';
        $this->abbreviation = '';
        $this->percentage = 0;
        $this->reverse_calculation = null;
        $this->tax_label = '';
        $this->status = 1;
        $this->editing = false;
        $this->taxId = null;
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $tax = Tax::findOrFail($id);

        $this->taxId = $id;
        $this->description = $tax->description;
        $this->abbreviation = $tax->abbreviation;
        $this->percentage = $tax->percentage;
        $this->reverse_calculation = $tax->reverse_calculation;
        $this->tax_label = $tax->tax_label;
        $this->status = $tax->status;
        $this->editing = true;
        $this->showModal = true;
    }

    /**
     * Calculate reverse calculation value based on percentage
     */
    public function updatedPercentage($value)
    {
        if (is_numeric($value) && $value > 0) {
            $this->reverse_calculation = round(1 + ($value / 100), 4);
        } else {
            $this->reverse_calculation = null;
        }
    }

    public function save()
    {
        // Update unique rule for editing
        if ($this->editing) {
            $this->rules['abbreviation'] = 'required|string|max:50|unique:taxes,abbreviation,' . $this->taxId;
        }

        $this->validate();

        $data = [
            'description' => $this->description,
            'abbreviation' => $this->abbreviation,
            'percentage' => $this->percentage,
            'reverse_calculation' => $this->reverse_calculation,
            'tax_label' => $this->tax_label,
            'status' => $this->status,
        ];

        if ($this->editing) {
            Tax::findOrFail($this->taxId)->update($data);
            session()->flash('message', 'Tax updated successfully!');
        } else {
            Tax::create($data);
            session()->flash('message', 'Tax created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function openDeleteConfirmModal($id)
    {
        $this->taxToDelete = $id;
        $this->showDeleteConfirmModal = true;
    }

    public function closeDeleteConfirmModal()
    {
        $this->showDeleteConfirmModal = false;
        $this->taxToDelete = null;
    }

    public function delete($id)
    {
        $tax = Tax::findOrFail($id);
        
        // Check if tax is used by any customers
        if ($tax->customers()->count() > 0) {
            session()->flash('error', 'Cannot delete tax that is assigned to customers. Please deactivate it instead.');
            $this->closeDeleteConfirmModal();
            return;
        }

        $tax->delete();
        session()->flash('message', 'Tax deleted successfully!');
        $this->closeDeleteConfirmModal();
    }

    public function toggleStatus($id)
    {
        $tax = Tax::findOrFail($id);
        $tax->update(['status' => $tax->status === 1 ? 0 : 1]);
        session()->flash('message', 'Tax status updated successfully!');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function render()
    {
        $query = Tax::query();

        // Search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->where('description', 'like', '%' . $this->search . '%')
                  ->orWhere('abbreviation', 'like', '%' . $this->search . '%')
                  ->orWhere('tax_label', 'like', '%' . $this->search . '%');
            });
        }

        // Status filter
        if ($this->filterStatus === 'active') {
            $query->where('status', 1);
        } elseif ($this->filterStatus === 'inactive') {
            $query->where('status', 0);
        }

        $taxes = $query->orderBy('abbreviation')->paginate(15);

        return view('livewire.tax-management', [
            'taxes' => $taxes,
        ]);
    }
}
