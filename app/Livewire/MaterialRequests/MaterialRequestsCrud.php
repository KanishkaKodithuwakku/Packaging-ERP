<?php

namespace App\Livewire\MaterialRequests;

use App\Models\JobOrder;
use App\Models\MaterialRequest;
use App\Models\Inventory;
use App\Services\OrderService;
use Livewire\Component;
use Livewire\WithPagination;

class MaterialRequestsCrud extends Component
{
    use WithPagination;

    protected $layout = 'components.layouts.app';

    public $showModal = false;
    public $editing = false;
    public $materialRequestId;
    
    // Form fields
    public $job_order_id;
    public $mr_no;
    public $material_code;
    public $qty_requested;
    public $uom = 'KG';
    public $status = 'pending';

    protected $rules = [
        'job_order_id' => 'required|exists:job_orders,id',
        'mr_no' => 'required|string|max:255|unique:material_requests,mr_no',
        'material_code' => 'required|string|max:255',
        'qty_requested' => 'required|numeric|min:0.01',
        'uom' => 'required|string|max:10',
        'status' => 'required|in:pending,approved,issued,completed',
    ];

    protected $messages = [
        'job_order_id.required' => 'Please select a job order.',
        'job_order_id.exists' => 'Selected job order does not exist.',
        'mr_no.required' => 'Material request number is required.',
        'mr_no.unique' => 'This material request number already exists.',
        'material_code.required' => 'Material code is required.',
        'qty_requested.required' => 'Quantity requested is required.',
        'qty_requested.numeric' => 'Quantity must be a number.',
        'qty_requested.min' => 'Quantity must be greater than 0.',
    ];

    public function mount()
    {
        $this->resetForm();
        
        // Check if we're creating from a job order
        if (request()->has('create_from')) {
            $this->createFromJobOrder(request()->get('create_from'));
        }
    }

    public function resetForm()
    {
        $this->job_order_id = '';
        $this->mr_no = '';
        $this->material_code = '';
        $this->qty_requested = '';
        $this->uom = 'KG';
        $this->status = 'pending';
        $this->editing = false;
        $this->materialRequestId = null;
    }

    public function create()
    {
        $this->resetForm();
        $this->mr_no = app(OrderService::class)->generateMaterialRequestNumber();
        $this->showModal = true;
    }

    public function createFromJobOrder($jobOrderId)
    {
        $jobOrder = JobOrder::findOrFail($jobOrderId);
        
        $this->job_order_id = $jobOrder->id;
        $this->mr_no = app(OrderService::class)->generateMaterialRequestNumber();
        $this->status = 'pending';
        $this->editing = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $materialRequest = MaterialRequest::findOrFail($id);
        
        $this->materialRequestId = $id;
        $this->job_order_id = $materialRequest->job_order_id;
        $this->mr_no = $materialRequest->mr_no;
        $this->material_code = $materialRequest->material_code;
        $this->qty_requested = $materialRequest->qty_requested;
        $this->uom = $materialRequest->uom;
        $this->status = $materialRequest->status;
        $this->editing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editing) {
            $this->rules['mr_no'] = 'required|string|max:255|unique:material_requests,mr_no,' . $this->materialRequestId;
        }

        $data = [
            'job_order_id' => $this->job_order_id,
            'mr_no' => $this->mr_no,
            'material_code' => $this->material_code,
            'qty_requested' => $this->qty_requested,
            'uom' => $this->uom,
            'status' => $this->status,
        ];

        if ($this->editing) {
            MaterialRequest::findOrFail($this->materialRequestId)->update($data);
            session()->flash('message', 'Material request updated successfully!');
        } else {
            MaterialRequest::create($data);
            session()->flash('message', 'Material request created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete($id)
    {
        MaterialRequest::findOrFail($id)->delete();
        session()->flash('message', 'Material request deleted successfully!');
    }

    public function approve($id)
    {
        $materialRequest = MaterialRequest::findOrFail($id);
        $materialRequest->update(['status' => 'approved']);
        session()->flash('message', 'Material request approved!');
    }

    public function issue($id)
    {
        $materialRequest = MaterialRequest::findOrFail($id);
        
        // Check if materials are available in inventory
        $availableStock = Inventory::where('item_code', $materialRequest->material_code)
            ->where('category', 'RAW')
            ->sum('qty_available');
            
        if ($availableStock < $materialRequest->qty_requested) {
            session()->flash('error', 'Insufficient stock! Available: ' . $availableStock . ' ' . $materialRequest->uom . ', Requested: ' . $materialRequest->qty_requested . ' ' . $materialRequest->uom);
            return;
        }

        // Process material issue
        app(OrderService::class)->processMaterialRequest($materialRequest->id);
        session()->flash('message', 'Materials issued successfully!');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function render()
    {
        $materialRequests = MaterialRequest::with('jobOrder.customerOrder.customer')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $jobOrders = JobOrder::where('status', '!=', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        $availableMaterials = Inventory::where('category', 'RAW')
            ->where('qty_available', '>', 0)
            ->select('item_code')
            ->distinct()
            ->pluck('item_code');

        return view('livewire.material-requests.material-requests-crud', [
            'materialRequests' => $materialRequests,
            'jobOrders' => $jobOrders,
            'availableMaterials' => $availableMaterials,
        ]);
    }
}
