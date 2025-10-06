<?php

namespace App\Livewire;

use App\Models\Supplier;
use App\Models\SupplierOrder;
use App\Services\OrderService;
use Livewire\Component;
use Livewire\WithPagination;

class SupplierOrdersCrud extends Component
{
    use WithPagination;

    protected $layout = 'components.layouts.app';

    public $showModal = false;
    public $editing = false;
    public $supplierOrderId;
    
    // Form fields
    public $supplier_id;
    public $po_no;
    public $material_code;
    public $gsm;
    public $width_mm;
    public $qty_kg;
    public $status = 'pending';

    protected $rules = [
        'supplier_id' => 'required|exists:suppliers,id',
        'po_no' => 'required|string|max:255|unique:supplier_orders,po_no',
        'material_code' => 'required|string|max:255',
        'gsm' => 'required|integer|min:1',
        'width_mm' => 'required|integer|min:1',
        'qty_kg' => 'required|numeric|min:0.01',
        'status' => 'required|in:pending,ordered,received,completed',
    ];

    protected $messages = [
        'supplier_id.required' => 'Please select a supplier.',
        'supplier_id.exists' => 'Selected supplier does not exist.',
        'po_no.required' => 'PO number is required.',
        'po_no.unique' => 'This PO number already exists.',
        'material_code.required' => 'Material code is required.',
        'gsm.required' => 'GSM is required.',
        'gsm.integer' => 'GSM must be a whole number.',
        'width_mm.required' => 'Width is required.',
        'width_mm.integer' => 'Width must be a whole number.',
        'qty_kg.required' => 'Quantity is required.',
        'qty_kg.numeric' => 'Quantity must be a number.',
        'qty_kg.min' => 'Quantity must be greater than 0.',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->supplier_id = '';
        $this->po_no = '';
        $this->material_code = '';
        $this->gsm = '';
        $this->width_mm = '';
        $this->qty_kg = '';
        $this->status = 'pending';
        $this->editing = false;
        $this->supplierOrderId = null;
    }

    public function create()
    {
        $this->resetForm();
        $this->po_no = app(OrderService::class)->generateSupplierPONumber();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $supplierOrder = SupplierOrder::findOrFail($id);
        
        $this->supplierOrderId = $id;
        $this->supplier_id = $supplierOrder->supplier_id;
        $this->po_no = $supplierOrder->po_no;
        $this->material_code = $supplierOrder->material_code;
        $this->gsm = $supplierOrder->gsm;
        $this->width_mm = $supplierOrder->width_mm;
        $this->qty_kg = $supplierOrder->qty_kg;
        $this->status = $supplierOrder->status;
        $this->editing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editing) {
            $this->rules['po_no'] = 'required|string|max:255|unique:supplier_orders,po_no,' . $this->supplierOrderId;
        }

        $data = [
            'supplier_id' => $this->supplier_id,
            'po_no' => $this->po_no,
            'material_code' => $this->material_code,
            'gsm' => $this->gsm,
            'width_mm' => $this->width_mm,
            'qty_kg' => $this->qty_kg,
            'status' => $this->status,
        ];

        if ($this->editing) {
            SupplierOrder::findOrFail($this->supplierOrderId)->update($data);
            session()->flash('message', 'Supplier order updated successfully!');
        } else {
            app(OrderService::class)->createSupplierOrder($data);
            session()->flash('message', 'Supplier order created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete($id)
    {
        SupplierOrder::findOrFail($id)->delete();
        session()->flash('message', 'Supplier order deleted successfully!');
    }

    public function markAsOrdered($id)
    {
        $supplierOrder = SupplierOrder::findOrFail($id);
        $supplierOrder->update(['status' => 'ordered']);
        session()->flash('message', 'Supplier order marked as ordered!');
    }

    public function markAsReceived($id)
    {
        $supplierOrder = SupplierOrder::findOrFail($id);
        $supplierOrder->update(['status' => 'received']);
        session()->flash('message', 'Supplier order marked as received!');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function render()
    {
        $supplierOrders = SupplierOrder::with('supplier')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $suppliers = Supplier::orderBy('name')->get();

        return view('livewire.supplier-orders-crud', [
            'supplierOrders' => $supplierOrders,
            'suppliers' => $suppliers,
        ]);
    }
}
