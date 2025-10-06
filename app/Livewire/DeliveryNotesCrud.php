<?php

namespace App\Livewire;

use App\Models\CustomerOrder;
use App\Models\DeliveryNote;
use App\Models\Inventory;
use App\Services\OrderService;
use Livewire\Component;
use Livewire\WithPagination;

class DeliveryNotesCrud extends Component
{
    use WithPagination;

    protected $layout = 'components.layouts.app';

    public $showModal = false;
    public $editing = false;
    public $deliveryNoteId;
    
    // Form fields
    public $customer_order_id;
    public $dn_no;
    public $fg_code;
    public $qty_delivered;
    public $delivery_date;

    protected $rules = [
        'customer_order_id' => 'required|exists:customer_orders,id',
        'dn_no' => 'required|string|max:255|unique:delivery_notes,dn_no',
        'fg_code' => 'required|string|max:255',
        'qty_delivered' => 'required|numeric|min:0.01',
        'delivery_date' => 'required|date',
    ];

    protected $messages = [
        'customer_order_id.required' => 'Please select a customer order.',
        'customer_order_id.exists' => 'Selected customer order does not exist.',
        'dn_no.required' => 'Delivery note number is required.',
        'dn_no.unique' => 'This delivery note number already exists.',
        'fg_code.required' => 'Finished goods code is required.',
        'qty_delivered.required' => 'Quantity delivered is required.',
        'qty_delivered.numeric' => 'Quantity must be a number.',
        'qty_delivered.min' => 'Quantity must be greater than 0.',
        'delivery_date.required' => 'Delivery date is required.',
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
        $this->dn_no = '';
        $this->fg_code = '';
        $this->qty_delivered = '';
        $this->delivery_date = now()->format('Y-m-d');
        $this->editing = false;
        $this->deliveryNoteId = null;
    }

    public function create()
    {
        $this->resetForm();
        $this->dn_no = app(OrderService::class)->generateDeliveryNoteNumber();
        $this->showModal = true;
    }

    public function createFromCustomerOrder($customerOrderId)
    {
        $customerOrder = CustomerOrder::findOrFail($customerOrderId);
        
        $this->customer_order_id = $customerOrder->id;
        $this->fg_code = $customerOrder->item_desc; // Use item description as FG code
        $this->qty_delivered = $customerOrder->qty_ordered;
        $this->dn_no = app(OrderService::class)->generateDeliveryNoteNumber();
        $this->delivery_date = now()->format('Y-m-d');
        $this->editing = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $deliveryNote = DeliveryNote::findOrFail($id);
        
        $this->deliveryNoteId = $id;
        $this->customer_order_id = $deliveryNote->customer_order_id;
        $this->dn_no = $deliveryNote->dn_no;
        $this->fg_code = $deliveryNote->fg_code;
        $this->qty_delivered = $deliveryNote->qty_delivered;
        $this->delivery_date = $deliveryNote->delivery_date->format('Y-m-d');
        $this->editing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editing) {
            $this->rules['dn_no'] = 'required|string|max:255|unique:delivery_notes,dn_no,' . $this->deliveryNoteId;
        }

        $data = [
            'customer_order_id' => $this->customer_order_id,
            'dn_no' => $this->dn_no,
            'fg_code' => $this->fg_code,
            'qty_delivered' => $this->qty_delivered,
            'delivery_date' => $this->delivery_date,
        ];

        if ($this->editing) {
            DeliveryNote::findOrFail($this->deliveryNoteId)->update($data);
            session()->flash('message', 'Delivery note updated successfully!');
        } else {
            app(OrderService::class)->processDeliveryNote($data);
            session()->flash('message', 'Delivery note created and inventory updated successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete($id)
    {
        DeliveryNote::findOrFail($id)->delete();
        session()->flash('message', 'Delivery note deleted successfully!');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function render()
    {
        $deliveryNotes = DeliveryNote::with('customerOrder.customer')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $customerOrders = CustomerOrder::where('status', '!=', 'delivered')
            ->orderBy('created_at', 'desc')
            ->get();

        $availableFinishedGoods = Inventory::where('category', 'FG')
            ->where('qty_available', '>', 0)
            ->select('item_code')
            ->distinct()
            ->pluck('item_code');

        return view('livewire.delivery-notes-crud', [
            'deliveryNotes' => $deliveryNotes,
            'customerOrders' => $customerOrders,
            'availableFinishedGoods' => $availableFinishedGoods,
        ]);
    }
}
