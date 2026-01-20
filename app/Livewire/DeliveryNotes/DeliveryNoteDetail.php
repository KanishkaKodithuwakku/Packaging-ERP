<?php

namespace App\Livewire\DeliveryNotes;

use App\Models\DeliveryNote;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JobOrderBox;
use App\Models\JobOrderDivider;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class DeliveryNoteDetail extends Component
{
    protected $layout = 'components.layouts.app';

    public $deliveryNote;
    public $existingInvoice = null;
    
    // Invoice creation modal
    public $showInvoiceModal = false;
    public $invoiceDate = '';
    public $invoiceItems = [];
    public $extraItems = [];
    public $remarks = '';

    public function mount($id)
    {
        $this->deliveryNote = DeliveryNote::with([
            'jobOrder.customer', 
            'items',
            'invoice'
        ])->findOrFail($id);
        
        $this->checkExistingInvoice();
        $this->initializeInvoiceForm();
    }
    
    public function initializeInvoiceForm()
    {
        $this->invoiceDate = $this->deliveryNote->dispatch_date?->format('Y-m-d') ?? now()->format('Y-m-d');
        $this->remarks = "Invoice for Delivery Note {$this->deliveryNote->dn_number}";
        $this->invoiceItems = [];
        $this->extraItems = [];
        
        // Initialize invoice items from delivery note items
        foreach ($this->deliveryNote->items as $item) {
            if ($item->quantity <= 0) {
                continue;
            }
            
            $unitPrice = 0;
            if ($item->item_type === 'box') {
                $box = JobOrderBox::find($item->item_id);
                if ($box) {
                    $unitPrice = (float) ($box->selling_price ?? $box->supplier_price ?? 0);
                }
            } elseif ($item->item_type === 'divider') {
                $divider = JobOrderDivider::find($item->item_id);
                if ($divider) {
                    $unitPrice = (float) ($divider->supplier_price ?? 0);
                }
            }
            
            $this->invoiceItems[] = [
                'delivery_note_item_id' => $item->id,
                'item_type' => $item->item_type,
                'item_id' => $item->item_id,
                'description' => $item->description,
                'material_code' => $item->material_code,
                'quantity' => (float) $item->quantity,
                'unit_price' => $unitPrice,
                'line_total' => $unitPrice * (float) $item->quantity,
            ];
        }
    }
    
    public function openInvoiceModal()
    {
        $this->initializeInvoiceForm();
        $this->showInvoiceModal = true;
    }
    
    public function closeInvoiceModal()
    {
        $this->showInvoiceModal = false;
        $this->initializeInvoiceForm();
    }
    
    public function addExtraItem()
    {
        $this->extraItems[] = [
            'description' => '',
            'material_code' => '',
            'quantity' => 0,
            'unit_price' => 0,
            'line_total' => 0,
        ];
    }
    
    public function removeExtraItem($index)
    {
        unset($this->extraItems[$index]);
        $this->extraItems = array_values($this->extraItems);
    }
    
    public function updateInvoiceItemTotal($index)
    {
        if (isset($this->invoiceItems[$index])) {
            $qty = (float) ($this->invoiceItems[$index]['quantity'] ?? 0);
            $price = (float) ($this->invoiceItems[$index]['unit_price'] ?? 0);
            $this->invoiceItems[$index]['line_total'] = $qty * $price;
        }
    }
    
    public function updateExtraItemTotal($index)
    {
        if (isset($this->extraItems[$index])) {
            $qty = (float) ($this->extraItems[$index]['quantity'] ?? 0);
            $price = (float) ($this->extraItems[$index]['unit_price'] ?? 0);
            $this->extraItems[$index]['line_total'] = $qty * $price;
        }
    }
    
    public function getTotalAmountProperty()
    {
        $total = 0;
        // Calculate from invoice items
        foreach ($this->invoiceItems as $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $price = (float) ($item['unit_price'] ?? 0);
            $total += $qty * $price;
        }
        // Calculate from extra items
        foreach ($this->extraItems as $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $price = (float) ($item['unit_price'] ?? 0);
            $total += $qty * $price;
        }
        return $total;
    }
    
    public function getLineTotal($item)
    {
        $qty = (float) ($item['quantity'] ?? 0);
        $price = (float) ($item['unit_price'] ?? 0);
        return $qty * $price;
    }
    
    public function checkExistingInvoice()
    {
        $this->existingInvoice = Invoice::where('delivery_note_id', $this->deliveryNote->id)->first();
    }


    /**
     * Create sales invoice (accounting entry) for this delivery note.
     * Uses customer's Account Receivable and Sales Revenue ledgers from customer finance settings.
     */
    public function createInvoice()
    {
        Log::info('createInvoice method called', [
            'delivery_note_id' => $this->deliveryNote->id ?? null,
        ]);
        
        try {
            // Validate form data
            if (empty($this->invoiceDate)) {
                session()->flash('error', 'Invoice date is required.');
                return;
            }
            
            if (empty($this->invoiceItems) && empty($this->extraItems)) {
                session()->flash('error', 'At least one invoice item is required.');
                return;
            }
            
            // Validate items
            foreach ($this->invoiceItems as $index => $item) {
                if (empty($item['description']) || (float)($item['quantity'] ?? 0) <= 0) {
                    session()->flash('error', "Item " . ($index + 1) . ": Description and quantity are required.");
                    return;
                }
            }
            
            foreach ($this->extraItems as $index => $item) {
                if (empty($item['description']) || (float)($item['quantity'] ?? 0) <= 0) {
                    session()->flash('error', "Extra item " . ($index + 1) . ": Description and quantity are required.");
                    return;
                }
            }
            
            $this->deliveryNote->refresh();
            $this->deliveryNote->load('items', 'jobOrder.customer');
            
            Log::info('Delivery note loaded', [
                'delivery_note_id' => $this->deliveryNote->id,
                'status' => $this->deliveryNote->status,
                'has_job_order' => $this->deliveryNote->jobOrder ? true : false,
                'has_customer' => $this->deliveryNote->jobOrder && $this->deliveryNote->jobOrder->customer ? true : false,
            ]);

            if (!$this->deliveryNote->jobOrder || !$this->deliveryNote->jobOrder->customer) {
                Log::warning('Cannot create invoice: Job order or customer not found');
                session()->flash('error', 'Cannot create invoice: Job order or customer not found.');
                return;
            }

            $customer = $this->deliveryNote->jobOrder->customer;
            
            Log::info('Customer loaded', [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
            ]);

            // Prevent duplicate invoice for same DN
            $this->checkExistingInvoice();
            if ($this->existingInvoice) {
                $invoiceUrl = route('invoice-detail', ['id' => $this->existingInvoice->id]);
                session()->flash('info', 'An invoice already exists for this delivery note (Invoice #' . $this->existingInvoice->invoice_number . '). | <a href="' . $invoiceUrl . '" class="underline font-semibold">View Invoice</a>');
                return;
            }

            // Build invoice items from form data
            $invoiceItems = [];
            $subtotal = 0;
            $sortOrder = 0;

            // Add items from delivery note
            foreach ($this->invoiceItems as $item) {
                $qty = (float) ($item['quantity'] ?? 0);
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                
                if ($qty > 0 && $unitPrice >= 0) {
                    $lineTotal = $qty * $unitPrice;
                    $subtotal += $lineTotal;
                    
                    $invoiceItems[] = [
                        'delivery_note_item_id' => $item['delivery_note_item_id'] ?? null,
                        'item_type' => $item['item_type'] ?? null,
                        'item_id' => $item['item_id'] ?? null,
                        'description' => $item['description'],
                        'material_code' => $item['material_code'] ?? '',
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                        'sort_order' => $sortOrder++,
                    ];
                }
            }
            
            // Add extra items
            foreach ($this->extraItems as $item) {
                $qty = (float) ($item['quantity'] ?? 0);
                $unitPrice = (float) ($item['unit_price'] ?? 0);
                
                if ($qty > 0 && $unitPrice >= 0) {
                    $lineTotal = $qty * $unitPrice;
                    $subtotal += $lineTotal;
                    
                    $invoiceItems[] = [
                        'delivery_note_item_id' => null,
                        'item_type' => 'extra', // Use 'extra' as type for items not from delivery note
                        'item_id' => null,
                        'description' => $item['description'],
                        'material_code' => $item['material_code'] ?? '',
                        'quantity' => $qty,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                        'sort_order' => $sortOrder++,
                    ];
                }
            }

            if ($subtotal <= 0 || empty($invoiceItems)) {
                Log::warning('Invoice total is zero or negative or no items');
                session()->flash('error', 'Cannot create invoice: Invoice total must be greater than zero.');
                return;
            }

            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'delivery_note_id' => $this->deliveryNote->id,
                'customer_id' => $customer->id,
                'job_order_id' => $this->deliveryNote->job_order_id,
                'invoice_date' => $this->invoiceDate,
                'due_date' => null, // Can be calculated based on payment terms
                'subtotal' => $subtotal,
                'tax_amount' => 0, // Can be calculated if tax is configured
                'discount_amount' => 0, // Can be applied if discounts are configured
                'total_amount' => $subtotal,
                'status' => 'draft',
                'notes' => $this->remarks,
                'terms' => null,
            ]);

            // Create invoice items
            foreach ($invoiceItems as $itemData) {
                InvoiceItem::create(array_merge($itemData, ['invoice_id' => $invoice->id]));
            }

            // Update delivery note status to invoiced
            $this->deliveryNote->update(['status' => 'invoiced']);

            // Update delivery note items status to invoiced (only for items that were invoiced)
            foreach ($this->invoiceItems as $formItem) {
                if (isset($formItem['delivery_note_item_id'])) {
                    $dnItem = $this->deliveryNote->items->find($formItem['delivery_note_item_id']);
                    if ($dnItem && (float)($formItem['quantity'] ?? 0) > 0) {
                        $dnItem->update(['status' => 'invoiced']);
                    }
                }
            }

            Log::info('Invoice created successfully', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'delivery_note_id' => $this->deliveryNote->id,
            ]);
            
            // Update existing invoice reference
            $this->existingInvoice = $invoice;
            
            // Close modal
            $this->closeInvoiceModal();
            
            $invoiceUrl = route('invoice-detail', ['id' => $invoice->id]);
            session()->flash('success', 'Invoice created successfully for this delivery note. Invoice #: ' . $invoice->invoice_number . ' | <a href="' . $invoiceUrl . '" class="underline font-semibold">View Invoice</a>');
            
            // Refresh the delivery note to show updated data
            $this->deliveryNote->refresh();
            $this->deliveryNote->load('items', 'jobOrder.customer', 'invoice');
        } catch (\Exception $e) {
            Log::error('Error creating invoice from delivery note: ' . $e->getMessage(), [
                'delivery_note_id' => $this->deliveryNote->id ?? null,
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            session()->flash('error', 'Error creating invoice: ' . $e->getMessage());
        }
    }

    public function printDeliveryNote()
    {
        // Simply trigger JavaScript print function
        $this->dispatch('openPrintDialog');
    }


    public function render()
    {
        // Refresh invoice check on each render
        $this->checkExistingInvoice();
        
        return view('livewire.delivery-notes.delivery-note-detail');
    }
}

