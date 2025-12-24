<?php

namespace App\Livewire\Quotations;

use App\Models\Quotation;
use App\Models\Customer;
use App\Services\QuotationService;
use App\Repositories\QuotationRepository;
use Livewire\Component;
use Livewire\WithPagination;

class QuotationsCrud extends Component
{
    use WithPagination;

    protected $layout = 'components.layouts.app';

    public $showModal = false;
    public $editing = false;
    public $quotationId;
    
    // Form fields
    public $customer_id;
    public $qt_no;
    public $item_desc;
    public $size_mm;
    public $ply;
    public $flute_type;
    public $gsm_layers = [];
    public $qty_requested;
    public $raw_material_cost;
    public $production_cost;
    public $total_cost;
    public $selling_price;
    public $profit_margin = 20;
    public $status = 'draft';
    public $valid_until;
    public $notes;

    protected $rules = [
        'customer_id' => 'required|exists:customers,id',
        'qt_no' => 'required|string|max:255|unique:quotations,qt_no',
        'item_desc' => 'required|string|max:255',
        'size_mm' => 'required|integer|min:1',
        'ply' => 'required|integer|min:1',
        'flute_type' => 'nullable|string|max:50',
        'gsm_layers' => 'required|array|min:1',
        'gsm_layers.*' => 'required|numeric|min:1',
        'qty_requested' => 'required|numeric|min:0.01',
        'profit_margin' => 'required|numeric|min:0|max:100',
        'valid_until' => 'nullable|date|after:today',
        'notes' => 'nullable|string',
    ];

    protected $messages = [
        'customer_id.required' => 'Please select a customer.',
        'customer_id.exists' => 'Selected customer does not exist.',
        'qt_no.required' => 'Quotation number is required.',
        'qt_no.unique' => 'This quotation number already exists.',
        'item_desc.required' => 'Item description is required.',
        'size_mm.required' => 'Size is required.',
        'size_mm.integer' => 'Size must be a number.',
        'size_mm.min' => 'Size must be greater than 0.',
        'ply.required' => 'Ply is required.',
        'ply.integer' => 'Ply must be a number.',
        'ply.min' => 'Ply must be greater than 0.',
        'gsm_layers.required' => 'At least one GSM layer is required.',
        'gsm_layers.array' => 'GSM layers must be an array.',
        'gsm_layers.min' => 'At least one GSM layer is required.',
        'gsm_layers.*.required' => 'Each GSM layer is required.',
        'gsm_layers.*.numeric' => 'Each GSM layer must be a number.',
        'gsm_layers.*.min' => 'Each GSM layer must be greater than 0.',
        'qty_requested.required' => 'Quantity requested is required.',
        'qty_requested.numeric' => 'Quantity must be a number.',
        'qty_requested.min' => 'Quantity must be greater than 0.',
        'profit_margin.required' => 'Profit margin is required.',
        'profit_margin.numeric' => 'Profit margin must be a number.',
        'profit_margin.min' => 'Profit margin must be 0 or greater.',
        'profit_margin.max' => 'Profit margin cannot exceed 100%.',
        'valid_until.date' => 'Valid until must be a valid date.',
        'valid_until.after' => 'Valid until must be after today.',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->customer_id = '';
        $this->qt_no = '';
        $this->item_desc = '';
        $this->size_mm = '';
        $this->ply = '';
        $this->flute_type = '';
        $this->gsm_layers = [150]; // Default GSM
        $this->qty_requested = '';
        $this->raw_material_cost = '';
        $this->production_cost = '';
        $this->total_cost = '';
        $this->selling_price = '';
        $this->profit_margin = 20;
        $this->status = 'draft';
        $this->valid_until = now()->addDays(30)->format('Y-m-d');
        $this->notes = '';
        $this->editing = false;
        $this->quotationId = null;
    }

    public function create()
    {
        $this->resetForm();
        $this->qt_no = app(\App\Repositories\QuotationRepository::class)->generateQuotationNumber();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $quotation = Quotation::findOrFail($id);
        
        $this->quotationId = $id;
        $this->customer_id = $quotation->customer_id;
        $this->qt_no = $quotation->qt_no;
        $this->item_desc = $quotation->item_desc;
        $this->size_mm = $quotation->size_mm;
        $this->ply = $quotation->ply;
        $this->flute_type = $quotation->flute_type;
        $this->gsm_layers = $quotation->gsm_layers;
        $this->qty_requested = $quotation->qty_requested;
        $this->raw_material_cost = $quotation->raw_material_cost;
        $this->production_cost = $quotation->production_cost;
        $this->total_cost = $quotation->total_cost;
        $this->selling_price = $quotation->selling_price;
        $this->profit_margin = $quotation->profit_margin;
        $this->status = $quotation->status;
        $this->valid_until = $quotation->valid_until ? $quotation->valid_until->format('Y-m-d') : '';
        $this->notes = $quotation->notes;
        $this->editing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editing) {
            $this->rules['qt_no'] = 'required|string|max:255|unique:quotations,qt_no,' . $this->quotationId;
        }

        $data = [
            'customer_id' => $this->customer_id,
            'qt_no' => $this->qt_no,
            'item_desc' => $this->item_desc,
            'size_mm' => $this->size_mm,
            'ply' => $this->ply,
            'flute_type' => $this->flute_type,
            'gsm_layers' => $this->gsm_layers,
            'qty_requested' => $this->qty_requested,
            'profit_margin' => $this->profit_margin,
            'status' => $this->status,
            'valid_until' => $this->valid_until,
            'notes' => $this->notes,
        ];

        if ($this->editing) {
            app(QuotationService::class)->updateQuotation($this->quotationId, $data);
            session()->flash('message', 'Quotation updated successfully!');
        } else {
            app(QuotationService::class)->createQuotation($data);
            session()->flash('message', 'Quotation created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete($id)
    {
        Quotation::findOrFail($id)->delete();
        session()->flash('message', 'Quotation deleted successfully!');
    }

    public function send($id)
    {
        $quotation = Quotation::findOrFail($id);
        if ($quotation->status === 'draft') {
            app(QuotationService::class)->sendQuotation($id);
            session()->flash('message', 'Quotation sent successfully!');
        }
    }

    public function accept($id)
    {
        $customerOrder = app(QuotationService::class)->acceptQuotation($id);
        if ($customerOrder) {
            session()->flash('message', 'Quotation accepted and customer order created!');
        } else {
            session()->flash('error', 'Unable to accept quotation. Please check if it\'s still valid.');
        }
    }

    public function reject($id)
    {
        app(QuotationService::class)->rejectQuotation($id);
        session()->flash('message', 'Quotation rejected!');
    }

    public function addGsmLayer()
    {
        $this->gsm_layers[] = 150;
    }

    public function removeGsmLayer($index)
    {
        unset($this->gsm_layers[$index]);
        $this->gsm_layers = array_values($this->gsm_layers);
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function render()
    {
        $quotations = Quotation::with('customer')
            ->whereHas('customer')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $customers = Customer::orderBy('name')->get();

        return view('livewire.quotations.quotations-crud', [
            'quotations' => $quotations,
            'customers' => $customers,
        ]);
    }
}
