<?php

namespace App\Livewire\JobOrders;

use App\Models\CustomerOrder;
use App\Models\JobOrder;
use App\Services\OrderService;
use Livewire\Component;
use Livewire\WithPagination;

class JobOrdersCrud extends Component
{
    use WithPagination;

    protected $layout = 'components.layouts.app';

    public $showModal = false;
    public $editing = false;
    public $jobOrderId;
    
    // Form fields
    public $customer_order_id;
    public $jo_no;
    public $item_desc;
    public $size_mm;
    public $ply;
    public $qty_to_make;
    public $status = 'pending';

    protected $rules = [
        'customer_order_id' => 'required|exists:customer_orders,id',
        'jo_no' => 'required|string|max:255|unique:job_orders,jo_no',
        'item_desc' => 'required|string|max:255',
        'size_mm' => 'required|integer|min:1',
        'ply' => 'required|integer|min:1',
        'qty_to_make' => 'required|numeric|min:0.01',
        'status' => 'required|in:pending,in_production,completed',
    ];

    protected $messages = [
        'customer_order_id.required' => 'Please select a customer order.',
        'customer_order_id.exists' => 'Selected customer order does not exist.',
        'jo_no.required' => 'Job order number is required.',
        'jo_no.unique' => 'This job order number already exists.',
        'item_desc.required' => 'Item description is required.',
        'size_mm.required' => 'Size in mm is required.',
        'size_mm.integer' => 'Size must be a whole number.',
        'ply.required' => 'Ply is required.',
        'ply.integer' => 'Ply must be a whole number.',
        'qty_to_make.required' => 'Quantity to make is required.',
        'qty_to_make.numeric' => 'Quantity must be a number.',
        'qty_to_make.min' => 'Quantity must be greater than 0.',
    ];

    public function mount()
    {
        $this->resetForm();
        
        // Check if we're creating from a customer order
        if (request()->has('create_from')) {
            $this->createFromCustomerOrder(request()->get('create_from'));
        }
    }

    public function resetForm()
    {
        $this->customer_order_id = '';
        $this->jo_no = '';
        $this->item_desc = '';
        $this->size_mm = '';
        $this->ply = '';
        $this->qty_to_make = '';
        $this->status = 'pending';
        $this->editing = false;
        $this->jobOrderId = null;
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function createFromCustomerOrder($customerOrderId)
    {
        $customerOrder = CustomerOrder::findOrFail($customerOrderId);
        
        $this->customer_order_id = $customerOrder->id;
        $this->item_desc = $customerOrder->item_desc;
        $this->size_mm = $customerOrder->size_mm;
        $this->ply = $customerOrder->ply;
        $this->qty_to_make = $customerOrder->qty_ordered;
        $this->jo_no = app(OrderService::class)->generateJobOrderNumber();
        $this->status = 'pending';
        $this->editing = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $jobOrder = JobOrder::findOrFail($id);
        
        $this->jobOrderId = $id;
        $this->customer_order_id = $jobOrder->customer_order_id;
        $this->jo_no = $jobOrder->jo_no;
        $this->item_desc = $jobOrder->item_desc;
        $this->size_mm = $jobOrder->size_mm;
        $this->ply = $jobOrder->ply;
        $this->qty_to_make = $jobOrder->qty_to_make;
        $this->status = $jobOrder->status;
        $this->editing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editing) {
            $this->rules['jo_no'] = 'required|string|max:255|unique:job_orders,jo_no,' . $this->jobOrderId;
        }

        $data = [
            'customer_order_id' => $this->customer_order_id,
            'jo_no' => $this->jo_no,
            'item_desc' => $this->item_desc,
            'size_mm' => $this->size_mm,
            'ply' => $this->ply,
            'qty_to_make' => $this->qty_to_make,
            'status' => $this->status,
        ];

        if ($this->editing) {
            JobOrder::findOrFail($this->jobOrderId)->update($data);
            session()->flash('message', 'Job order updated successfully!');
        } else {
            JobOrder::create($data);
            session()->flash('message', 'Job order created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete($id)
    {
        JobOrder::findOrFail($id)->delete();
        session()->flash('message', 'Job order deleted successfully!');
    }

    public function complete($id)
    {
        app(OrderService::class)->completeJobOrder($id);
        session()->flash('message', 'Job order completed successfully!');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function render()
    {
        $jobOrders = JobOrder::with('customerOrder.customer')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $customerOrders = CustomerOrder::where('status', '!=', 'delivered')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.job-orders.job-orders-crud', [
            'jobOrders' => $jobOrders,
            'customerOrders' => $customerOrders,
        ]);
    }
}
