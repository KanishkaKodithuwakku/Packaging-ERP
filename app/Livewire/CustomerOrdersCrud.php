<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderItem;
use App\Services\OrderService;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerOrdersCrud extends Component
{
    use WithPagination;

    protected $layout = 'components.layouts.app';

    public $showModal = false;
    public $showItemsModal = false;
    public $editing = false;
    public $customerOrderId;
    
    // Order form fields
    public $customer_id;
    public $order_no;
    public $status = 'pending';
    public $notes = '';
    
    // Item form fields
    public $orderItems = [];
    public $currentItem = [];
    
    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'order_no' => 'required|string|max:255|unique:customer_orders,order_no',
        'status' => 'required|in:pending,confirmed,in_production,completed,delivered',
    ];

    protected $itemRules = [
        'orderItems' => 'required|array|min:1',
        'orderItems.*.item_description' => 'required|string|max:255',
        'orderItems.*.length_mm' => 'required|integer|min:1',
        'orderItems.*.width_mm' => 'required|integer|min:1',
        'orderItems.*.height_mm' => 'required|integer|min:1',
        'orderItems.*.ply' => 'required|integer|min:1',
        'orderItems.*.qty_ordered' => 'required|numeric|min:0.01',
        'orderItems.*.unit_price' => 'nullable|numeric|min:0',
    ];

    protected $messages = [
        'customer_id.required' => 'Please select a customer.',
        'customer_id.exists' => 'Selected customer does not exist.',
        'order_no.required' => 'Order number is required.',
        'order_no.unique' => 'This order number already exists.',
        'orderItems.required' => 'At least one item is required.',
        'orderItems.min' => 'At least one item is required.',
        'orderItems.*.item_description.required' => 'Item description is required.',
        'orderItems.*.length_mm.required' => 'Length is required.',
        'orderItems.*.length_mm.integer' => 'Length must be a whole number.',
        'orderItems.*.width_mm.required' => 'Width is required.',
        'orderItems.*.width_mm.integer' => 'Width must be a whole number.',
        'orderItems.*.height_mm.required' => 'Height is required.',
        'orderItems.*.height_mm.integer' => 'Height must be a whole number.',
        'orderItems.*.ply.required' => 'Ply is required.',
        'orderItems.*.ply.integer' => 'Ply must be a whole number.',
        'orderItems.*.qty_ordered.required' => 'Quantity ordered is required.',
        'orderItems.*.qty_ordered.numeric' => 'Quantity must be a number.',
        'orderItems.*.qty_ordered.min' => 'Quantity must be greater than 0.',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->customer_id = '';
        $this->order_no = '';
        $this->status = 'pending';
        $this->notes = '';
        $this->orderItems = [];
        $this->currentItem = [];
        $this->editing = false;
        $this->customerOrderId = null;
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $customerOrder = CustomerOrder::with('orderItems')->findOrFail($id);
        
        $this->customerOrderId = $id;
        $this->customer_id = $customerOrder->customer_id;
        $this->order_no = $customerOrder->order_no;
        $this->status = $customerOrder->status;
        $this->notes = $customerOrder->notes ?? '';
        
        // Load existing items
        $this->orderItems = $customerOrder->orderItems->map(function ($item) {
            return [
                'id' => $item->id,
                'item_description' => $item->item_description,
                'length_mm' => $item->length_mm,
                'width_mm' => $item->width_mm,
                'height_mm' => $item->height_mm,
                'ply' => $item->ply,
                'flute_type' => $item->flute_type,
                'gsm_layers' => $item->gsm_layers ?? [],
                'qty_ordered' => $item->qty_ordered,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
                'notes' => $item->notes,
            ];
        })->toArray();
        
        $this->editing = true;
        $this->showModal = true;
    }

    public function addItem()
    {
        $this->orderItems[] = [
            'item_description' => '',
            'length_mm' => '',
            'width_mm' => '',
            'height_mm' => '',
            'ply' => '',
            'flute_type' => '',
            'gsm_layers' => [],
            'qty_ordered' => '',
            'unit_price' => '',
            'total_price' => '',
            'notes' => '',
        ];
    }

    public function removeItem($index)
    {
        unset($this->orderItems[$index]);
        $this->orderItems = array_values($this->orderItems);
    }

    public function addGsmLayer($itemIndex)
    {
        if (!isset($this->orderItems[$itemIndex]['gsm_layers'])) {
            $this->orderItems[$itemIndex]['gsm_layers'] = [];
        }
        $this->orderItems[$itemIndex]['gsm_layers'][] = '';
    }

    public function removeGsmLayer($itemIndex, $layerIndex)
    {
        unset($this->orderItems[$itemIndex]['gsm_layers'][$layerIndex]);
        $this->orderItems[$itemIndex]['gsm_layers'] = array_values($this->orderItems[$itemIndex]['gsm_layers']);
    }

    public function save()
    {
        $this->validate();

        if ($this->editing) {
            $this->rules['order_no'] = 'required|string|max:255|unique:customer_orders,order_no,' . $this->customerOrderId;
        }

        $orderData = [
            'customer_id' => $this->customer_id,
            'order_no' => $this->order_no,
            'status' => $this->status,
            'notes' => $this->notes,
        ];

        if ($this->editing) {
            $customerOrder = CustomerOrder::findOrFail($this->customerOrderId);
            $customerOrder->update($orderData);
            session()->flash('message', 'Customer order updated successfully!');
        } else {
            $customerOrder = CustomerOrder::create($orderData);
            session()->flash('message', 'Customer order created successfully! You can now add items to this order.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete($id)
    {
        CustomerOrder::findOrFail($id)->delete();
        session()->flash('message', 'Customer order deleted successfully!');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function addItems($id)
    {
        $customerOrder = CustomerOrder::with('orderItems')->findOrFail($id);
        
        $this->customerOrderId = $id;
        $this->customer_id = $customerOrder->customer_id;
        $this->order_no = $customerOrder->order_no;
        $this->status = $customerOrder->status;
        $this->notes = $customerOrder->notes ?? '';
        
        // Load existing items
        $this->orderItems = $customerOrder->orderItems->map(function ($item) {
            return [
                'id' => $item->id,
                'item_description' => $item->item_description,
                'length_mm' => $item->length_mm,
                'width_mm' => $item->width_mm,
                'height_mm' => $item->height_mm,
                'ply' => $item->ply,
                'flute_type' => $item->flute_type,
                'gsm_layers' => $item->gsm_layers ?? [],
                'qty_ordered' => $item->qty_ordered,
                'unit_price' => $item->unit_price,
                'total_price' => $item->total_price,
                'notes' => $item->notes,
            ];
        })->toArray();
        
        // If no items exist, add one empty item
        if (empty($this->orderItems)) {
            $this->addItem();
        }
        
        $this->showItemsModal = true;
    }

    public function saveItems()
    {
        $this->validate($this->itemRules);

        $customerOrder = CustomerOrder::findOrFail($this->customerOrderId);
        
        // Delete existing items and create new ones
        $customerOrder->orderItems()->delete();
        
        foreach ($this->orderItems as $itemData) {
            $customerOrder->orderItems()->create([
                'item_description' => $itemData['item_description'],
                'length_mm' => $itemData['length_mm'],
                'width_mm' => $itemData['width_mm'],
                'height_mm' => $itemData['height_mm'],
                'ply' => $itemData['ply'],
                'flute_type' => $itemData['flute_type'],
                'gsm_layers' => $itemData['gsm_layers'],
                'qty_ordered' => $itemData['qty_ordered'],
                'unit_price' => $itemData['unit_price'],
                'notes' => $itemData['notes'],
            ]);
        }
        
        session()->flash('message', 'Order items updated successfully!');
        $this->showItemsModal = false;
        $this->resetForm();
    }

    public function closeItemsModal()
    {
        $this->showItemsModal = false;
        $this->resetForm();
    }

    public function render()
    {
        $customerOrders = CustomerOrder::with(['customer', 'quotation', 'orderItems'])
            ->whereHas('customer')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $customers = Customer::orderBy('name')->get();

        return view('livewire.customer-orders-crud', [
            'customerOrders' => $customerOrders,
            'customers' => $customers,
        ]);
    }
}