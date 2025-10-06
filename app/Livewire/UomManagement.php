<?php

namespace App\Livewire;

use App\Models\Uom;
use App\Repositories\UomRepository;
use Livewire\Component;
use Livewire\WithPagination;

class UomManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $typeFilter = '';
    public $statusFilter = 'active';
    
    // Form fields
    public $uomId;
    public $code = '';
    public $name = '';
    public $type = '';
    public $status = 'active';
    public $description = '';
    
    public $showModal = false;
    public $isEditing = false;

    protected $rules = [
        'code' => 'required|string|max:10|unique:uoms,code',
        'name' => 'required|string|max:100',
        'type' => 'required|in:weight,volume,count,length,area',
        'status' => 'required|in:active,inactive',
        'description' => 'nullable|string',
    ];

    protected $messages = [
        'code.required' => 'UOM code is required.',
        'code.unique' => 'This UOM code already exists.',
        'name.required' => 'UOM name is required.',
        'type.required' => 'UOM type is required.',
    ];

    public function mount()
    {
        // Check permissions
        if (!auth()->user()->hasAnyRole(['admin', 'planner'])) {
            abort(403, 'Unauthorized access to UOM management.');
        }
    }

    public function render()
    {
        $query = Uom::query();

        // Apply filters
        if ($this->search) {
            $query->where(function($q) {
                $q->where('code', 'like', '%' . $this->search . '%')
                  ->orWhere('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->typeFilter) {
            $query->where('type', $this->typeFilter);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $uoms = $query->orderBy('name')->paginate(10);
        $types = ['weight', 'volume', 'count', 'length', 'area'];

        return view('livewire.uom-management', compact('uoms', 'types'));
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $uom = Uom::findOrFail($id);
        $this->uomId = $uom->id;
        $this->code = $uom->code;
        $this->name = $uom->name;
        $this->type = $uom->type;
        $this->status = $uom->status;
        $this->description = $uom->description;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        // Update validation rules for editing
        if ($this->isEditing) {
            $this->rules['code'] = 'required|string|max:10|unique:uoms,code,' . $this->uomId;
        }

        $this->validate();

        $data = [
            'code' => strtoupper($this->code),
            'name' => $this->name,
            'type' => $this->type,
            'status' => $this->status,
            'description' => $this->description,
        ];

        if ($this->isEditing) {
            $uom = Uom::findOrFail($this->uomId);
            $uom->update($data);
            session()->flash('message', 'UOM updated successfully!');
        } else {
            Uom::create($data);
            session()->flash('message', 'UOM created successfully!');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $uom = Uom::findOrFail($id);
        
        // Check if UOM is in use
        $repository = new UomRepository();
        if ($repository->isUomInUse($uom)) {
            session()->flash('error', 'Cannot delete UOM as it is being used in conversions or inventory.');
            return;
        }

        $uom->delete();
        session()->flash('message', 'UOM deleted successfully!');
    }

    public function toggleStatus($id)
    {
        $uom = Uom::findOrFail($id);
        $uom->update(['status' => $uom->status === 'active' ? 'inactive' : 'active']);
        session()->flash('message', 'UOM status updated successfully!');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->uomId = null;
        $this->code = '';
        $this->name = '';
        $this->type = '';
        $this->status = 'active';
        $this->description = '';
        $this->isEditing = false;
        $this->resetErrorBag();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedTypeFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }
}