<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\JobOrder;
use App\Models\Supplier;
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
    
    // Basic fields
    public $customer_order_id;
    public $jo_no;
    public $item_desc;
    public $size_mm;
    public $ply;
    public $qty_to_make;
    public $status = 'pending';

    // Header Section
    public $order_date;
    public $supplier_id;
    public $supplier_po_ref;
    public $customer_po_no;

    // Order Details
    public $order_qty;
    public $selling_price;
    public $activity;
    public $finishing_type;

    // Box Specification
    public $box_length_cm;
    public $box_width_cm;
    public $box_height_cm;
    public $top_liner;
    public $combination;
    public $flute;
    public $sheet_width;
    public $sheet_length;
    public $no_of_ups;
    public $board_qty;

    // Printing Details
    public $printing_instruction;
    public $no_of_colours;
    public $sample_available;
    public $fsc_claim;
    public $notes;

    // Dropdown options
    public $activityOptions = [
        'Printing',
        'Die-Cutting',
        'Lamination',
        'Stitching',
        'Gluing',
        'Pasting',
        'Punching',
        'Strapping',
    ];

    public $finishingOptions = [
        'Stitched',
        'Glued',
        'Taped',
    ];

    public $printingInstructionOptions = [
        'Flexo Print',
        'Offset Print',
        'Digital Print',
        'Screen Print',
        'No Print',
    ];

    public $fscClaimOptions = [
        'FSC 100%',
        'FSC Mix',
        'FSC Recycled',
        'No FSC Claim',
    ];

    protected $rules = [
        'customer_order_id' => 'nullable|exists:customer_orders,id',
        'jo_no' => 'required|string|max:255|unique:job_orders,jo_no',
        'order_date' => 'nullable|date',
        'supplier_id' => 'nullable|exists:suppliers,id',
        'supplier_po_ref' => 'nullable|string|max:255',
        'customer_po_no' => 'nullable|string|max:255',
        'item_desc' => 'required|string|max:255',
        'size_mm' => 'nullable|integer|min:1',
        'ply' => 'nullable|integer|min:1',
        'qty_to_make' => 'required|numeric|min:0.01',
        'order_qty' => 'nullable|numeric|min:0',
        'selling_price' => 'nullable|numeric|min:0',
        'activity' => 'nullable|string|max:255',
        'finishing_type' => 'nullable|string|max:255',
        'box_length_cm' => 'nullable|numeric|min:0',
        'box_width_cm' => 'nullable|numeric|min:0',
        'box_height_cm' => 'nullable|numeric|min:0',
        'top_liner' => 'nullable|string|max:255',
        'combination' => 'nullable|string|max:255',
        'flute' => 'nullable|string|max:255',
        'sheet_width' => 'nullable|numeric|min:0',
        'sheet_length' => 'nullable|numeric|min:0',
        'no_of_ups' => 'nullable|integer|min:1',
        'board_qty' => 'nullable|numeric|min:0',
        'printing_instruction' => 'nullable|string',
        'no_of_colours' => 'nullable|integer|min:0',
        'sample_available' => 'nullable|string|max:255',
        'fsc_claim' => 'nullable|string|max:255',
        'notes' => 'nullable|string',
        'status' => 'required|in:pending,in_progress,completed',
    ];

    public function mount()
    {
        $this->resetForm();
        
        // Check if we're creating from a customer order
        if (request()->has('create_from')) {
            $this->createFromCustomerOrder(request()->get('create_from'));
        }
    }

    public function updated($propertyName)
    {
        // Auto-calculate board quantity when order_qty or no_of_ups changes
        if (in_array($propertyName, ['order_qty', 'no_of_ups'])) {
            $this->calculateBoardQty();
        }
    }

    public function updatedOrderQty()
    {
        $this->calculateBoardQty();
    }

    public function updatedNoOfUps()
    {
        $this->calculateBoardQty();
    }

    public function calculateBoardQty()
    {
        if ($this->order_qty && $this->no_of_ups && $this->no_of_ups > 0) {
            $this->board_qty = round($this->order_qty / $this->no_of_ups, 2);
        } else {
            $this->board_qty = null;
        }
    }

    public function resetForm()
    {
        $this->customer_order_id = '';
        $this->jo_no = '';
        $this->order_date = date('Y-m-d');
        $this->supplier_id = '';
        $this->supplier_po_ref = '';
        $this->customer_po_no = '';
        $this->item_desc = '';
        $this->size_mm = '';
        $this->ply = '';
        $this->qty_to_make = '';
        $this->order_qty = '';
        $this->selling_price = '';
        $this->activity = '';
        $this->finishing_type = '';
        $this->box_length_cm = '';
        $this->box_width_cm = '';
        $this->box_height_cm = '';
        $this->top_liner = '';
        $this->combination = '';
        $this->flute = '';
        $this->sheet_width = '';
        $this->sheet_length = '';
        $this->no_of_ups = '';
        $this->board_qty = '';
        $this->printing_instruction = '';
        $this->no_of_colours = '';
        $this->sample_available = '';
        $this->fsc_claim = '';
        $this->notes = '';
        $this->status = 'pending';
        $this->editing = false;
        $this->jobOrderId = null;
    }

    public function create()
    {
        $this->resetForm();
        $this->jo_no = app(OrderService::class)->generateJobOrderNumber();
        $this->showModal = true;
    }

    public function createFromCustomerOrder($customerOrderId)
    {
        $customerOrder = CustomerOrder::with('customer')->findOrFail($customerOrderId);
        
        $this->customer_order_id = $customerOrder->id;
        $this->item_desc = $customerOrder->item_desc;
        $this->size_mm = $customerOrder->size_mm;
        $this->ply = $customerOrder->ply;
        $this->qty_to_make = $customerOrder->qty_ordered;
        $this->order_qty = $customerOrder->qty_ordered;
        $this->selling_price = $customerOrder->unit_price ?? 0;
        $this->customer_po_no = $customerOrder->po_no ?? '';
        $this->jo_no = app(OrderService::class)->generateJobOrderNumber();
        $this->order_date = date('Y-m-d');
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
        $this->order_date = $jobOrder->order_date ? date('Y-m-d', strtotime($jobOrder->order_date)) : '';
        $this->supplier_id = $jobOrder->supplier_id;
        $this->supplier_po_ref = $jobOrder->supplier_po_ref;
        $this->customer_po_no = $jobOrder->customer_po_no;
        $this->item_desc = $jobOrder->item_desc;
        $this->size_mm = $jobOrder->size_mm;
        $this->ply = $jobOrder->ply;
        $this->qty_to_make = $jobOrder->qty_to_make;
        $this->order_qty = $jobOrder->order_qty;
        $this->selling_price = $jobOrder->selling_price;
        $this->activity = $jobOrder->activity;
        $this->finishing_type = $jobOrder->finishing_type;
        $this->box_length_cm = $jobOrder->box_length_cm;
        $this->box_width_cm = $jobOrder->box_width_cm;
        $this->box_height_cm = $jobOrder->box_height_cm;
        $this->top_liner = $jobOrder->top_liner;
        $this->combination = $jobOrder->combination;
        $this->flute = $jobOrder->flute;
        $this->sheet_width = $jobOrder->sheet_width;
        $this->sheet_length = $jobOrder->sheet_length;
        $this->no_of_ups = $jobOrder->no_of_ups;
        $this->board_qty = $jobOrder->board_qty;
        $this->printing_instruction = $jobOrder->printing_instruction;
        $this->no_of_colours = $jobOrder->no_of_colours;
        $this->sample_available = $jobOrder->sample_available;
        $this->fsc_claim = $jobOrder->fsc_claim;
        $this->notes = $jobOrder->notes;
        $this->status = $jobOrder->status;
        $this->editing = true;
        $this->showModal = true;
    }

    public function save()
    {
        if ($this->editing) {
            $this->rules['jo_no'] = 'required|string|max:255|unique:job_orders,jo_no,' . $this->jobOrderId;
        }

        $this->validate();

        // Calculate board qty before saving
        $this->calculateBoardQty();

        $data = [
            'customer_order_id' => $this->customer_order_id ?: null,
            'jo_no' => $this->jo_no,
            'order_date' => $this->order_date,
            'supplier_id' => $this->supplier_id ?: null,
            'supplier_po_ref' => $this->supplier_po_ref,
            'customer_po_no' => $this->customer_po_no,
            'item_desc' => $this->item_desc,
            'size_mm' => $this->size_mm,
            'ply' => $this->ply,
            'qty_to_make' => $this->qty_to_make,
            'order_qty' => $this->order_qty,
            'selling_price' => $this->selling_price,
            'activity' => $this->activity,
            'finishing_type' => $this->finishing_type,
            'box_length_cm' => $this->box_length_cm,
            'box_width_cm' => $this->box_width_cm,
            'box_height_cm' => $this->box_height_cm,
            'top_liner' => $this->top_liner,
            'combination' => $this->combination,
            'flute' => $this->flute,
            'sheet_width' => $this->sheet_width,
            'sheet_length' => $this->sheet_length,
            'no_of_ups' => $this->no_of_ups,
            'board_qty' => $this->board_qty,
            'printing_instruction' => $this->printing_instruction,
            'no_of_colours' => $this->no_of_colours,
            'sample_available' => $this->sample_available,
            'fsc_claim' => $this->fsc_claim,
            'notes' => $this->notes,
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
        $jobOrders = JobOrder::with(['customerOrder.customer', 'supplier'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $customerOrders = CustomerOrder::with('customer')
            ->where('status', '!=', 'delivered')
            ->orderBy('created_at', 'desc')
            ->get();

        $suppliers = Supplier::orderBy('name')->get();

        return view('livewire.job-orders-crud', [
            'jobOrders' => $jobOrders,
            'customerOrders' => $customerOrders,
            'suppliers' => $suppliers,
        ]);
    }
}
