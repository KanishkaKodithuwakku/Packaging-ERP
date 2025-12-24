<?php

namespace App\Livewire\UOM;

use App\Models\Uom;
use App\Models\UomConversion;
use App\Repositories\UomRepository;
use Livewire\Component;
use Livewire\WithPagination;

class UomConversionManagement extends Component
{
    use WithPagination;

    public $search = '';
    
    // Form fields
    public $conversionId;
    public $fromUomId = '';
    public $toUomId = '';
    public $factor = '';
    public $isBidirectional = true;
    public $notes = '';
    
    public $showModal = false;
    public $isEditing = false;

    protected $rules = [
        'fromUomId' => 'required|exists:uoms,id',
        'toUomId' => 'required|exists:uoms,id|different:fromUomId',
        'factor' => 'required|numeric|min:0.000001',
        'isBidirectional' => 'boolean',
        'notes' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'fromUomId.required' => 'Source UOM is required.',
        'toUomId.required' => 'Target UOM is required.',
        'toUomId.different' => 'Source and target UOMs must be different.',
        'factor.required' => 'Conversion factor is required.',
        'factor.min' => 'Conversion factor must be greater than 0.',
    ];

    public function mount()
    {
        // Check permissions
        if (!auth()->user()->hasAnyRole(['admin', 'planner'])) {
            abort(403, 'Unauthorized access to UOM conversion management.');
        }
    }

    public function render()
    {
        $query = UomConversion::with(['fromUom', 'toUom']);

        // Apply search filter
        if ($this->search) {
            $query->whereHas('fromUom', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            })->orWhereHas('toUom', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        $conversions = $query->orderBy('created_at', 'desc')->paginate(10);
        $uoms = Uom::active()->orderBy('name')->get();

        return view('livewire.uom.uom-conversion-management', compact('conversions', 'uoms'));
    }

    public function create()
    {
        $this->resetForm();
        $this->isEditing = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $conversion = UomConversion::findOrFail($id);
        $this->conversionId = $conversion->id;
        $this->fromUomId = $conversion->from_uom_id;
        $this->toUomId = $conversion->to_uom_id;
        $this->factor = $conversion->factor;
        $this->isBidirectional = $conversion->is_bidirectional;
        $this->notes = $conversion->notes;
        $this->isEditing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'from_uom_id' => $this->fromUomId,
            'to_uom_id' => $this->toUomId,
            'factor' => $this->factor,
            'is_bidirectional' => $this->isBidirectional,
            'notes' => $this->notes,
        ];

        if ($this->isEditing) {
            $conversion = UomConversion::findOrFail($this->conversionId);
            $conversion->update($data);
            session()->flash('message', 'Conversion updated successfully!');
        } else {
            // Check if conversion already exists
            $existing = UomConversion::where('from_uom_id', $this->fromUomId)
                ->where('to_uom_id', $this->toUomId)
                ->first();
            
            if ($existing) {
                session()->flash('error', 'Conversion between these UOMs already exists.');
                return;
            }

            $repository = new UomRepository();
            $repository->createGlobalConversion($data);
            session()->flash('message', 'Conversion created successfully!');
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        $conversion = UomConversion::findOrFail($id);
        $conversion->delete();
        session()->flash('message', 'Conversion deleted successfully!');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->conversionId = null;
        $this->fromUomId = '';
        $this->toUomId = '';
        $this->factor = '';
        $this->isBidirectional = true;
        $this->notes = '';
        $this->isEditing = false;
        $this->resetErrorBag();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFromUomId()
    {
        $this->toUomId = '';
    }
}