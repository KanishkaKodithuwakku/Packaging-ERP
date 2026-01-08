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

    public function mount($id)
    {
        $this->deliveryNote = DeliveryNote::with([
            'jobOrder.customer', 
            'items',
            'invoice'
        ])->findOrFail($id);
        
        $this->checkExistingInvoice();
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

            // Allow invoice creation for any status (dispatch process removed)

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

            // Calculate invoice total and items based on dispatched quantities and selling prices
            $invoiceItems = [];
            $subtotal = 0;
            $sortOrder = 0;

            foreach ($this->deliveryNote->items as $item) {
                // Use quantity instead of dispatched_qty since dispatch process is removed
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

                if ($unitPrice > 0) {
                    $lineTotal = $unitPrice * (float) $item->quantity;
                    $subtotal += $lineTotal;
                    
                    $invoiceItems[] = [
                        'delivery_note_item_id' => $item->id,
                        'item_type' => $item->item_type,
                        'item_id' => $item->item_id,
                        'description' => $item->description,
                        'material_code' => $item->material_code,
                        'quantity' => $item->quantity,
                        'unit_price' => $unitPrice,
                        'line_total' => $lineTotal,
                        'sort_order' => $sortOrder++,
                    ];
                }
            }

            if ($subtotal <= 0 || empty($invoiceItems)) {
                Log::warning('Invoice total is zero or negative or no items');
                session()->flash('error', 'Cannot create invoice: No items with valid prices were found.');
                return;
            }

            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'delivery_note_id' => $this->deliveryNote->id,
                'customer_id' => $customer->id,
                'job_order_id' => $this->deliveryNote->job_order_id,
                'invoice_date' => $this->deliveryNote->dispatch_date?->format('Y-m-d') ?? now()->format('Y-m-d'),
                'due_date' => null, // Can be calculated based on payment terms
                'subtotal' => $subtotal,
                'tax_amount' => 0, // Can be calculated if tax is configured
                'discount_amount' => 0, // Can be applied if discounts are configured
                'total_amount' => $subtotal,
                'status' => 'draft',
                'notes' => "Invoice for Delivery Note {$this->deliveryNote->dn_number}",
                'terms' => null,
            ]);

            // Create invoice items
            foreach ($invoiceItems as $itemData) {
                InvoiceItem::create(array_merge($itemData, ['invoice_id' => $invoice->id]));
            }

            // Update delivery note status to invoiced
            $this->deliveryNote->update(['status' => 'invoiced']);

            // Update all delivery note items status to invoiced
            foreach ($this->deliveryNote->items as $item) {
                if ($item->quantity > 0) {
                    $item->update(['status' => 'invoiced']);
                }
            }

            Log::info('Invoice created successfully', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'delivery_note_id' => $this->deliveryNote->id,
            ]);
            
            // Update existing invoice reference
            $this->existingInvoice = $invoice;
            
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

