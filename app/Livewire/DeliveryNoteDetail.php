<?php

namespace App\Livewire;

use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\Entry;
use App\Models\EntryType;
use App\Models\Ledger;
use App\Models\JobOrderBox;
use App\Models\JobOrderDivider;
use App\Services\DeliveryService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class DeliveryNoteDetail extends Component
{
    protected $layout = 'components.layouts.app';

    public $deliveryNote;
    public $dispatchQuantities = [];

    public function mount($id)
    {
        $this->deliveryNote = DeliveryNote::with([
            'jobOrder.customer', 
            'items.inventoryTransactions'
        ])->findOrFail($id);
    }

    public function dispatchItem($itemId, $quantity)
    {
        // Get the actual quantity from the form array
        $actualQty = $this->dispatchQuantities[$itemId] ?? 0;
        
        if ($actualQty <= 0) {
            session()->flash('error', 'Please enter a quantity greater than 0.');
            return;
        }

        $this->validate([
            "dispatchQuantities.{$itemId}" => 'required|numeric|min:0.01',
        ], [
            "dispatchQuantities.{$itemId}.required" => 'Quantity is required',
            "dispatchQuantities.{$itemId}.numeric" => 'Quantity must be a number',
            "dispatchQuantities.{$itemId}.min" => 'Quantity must be greater than 0',
        ]);

        $item = DeliveryNoteItem::findOrFail($itemId);
        
        if ($actualQty > $item->remaining_qty) {
            session()->flash('error', 'Cannot dispatch more than remaining quantity.');
            return;
        }

        try {
            Log::info('Dispatching FG', [
                'delivery_note_id' => $this->deliveryNote->id,
                'delivery_note_item_id' => $item->id,
                'quantity' => $actualQty,
                'item_material_code' => $item->material_code,
                'current_dispatched_qty' => $item->dispatched_qty,
                'current_remaining_qty' => $item->remaining_qty,
            ]);

            $deliveryService = app(DeliveryService::class);
            $result = $deliveryService->dispatchFg($this->deliveryNote, $item, $actualQty);

            // Verify transaction was created
            if (!isset($result['transaction']) || !$result['transaction']) {
                Log::error('No transaction created during dispatch', [
                    'delivery_note_id' => $this->deliveryNote->id,
                    'delivery_note_item_id' => $item->id,
                    'quantity' => $actualQty,
                ]);
                throw new \Exception('Failed to create inventory transaction');
            }

            // Verify transaction has the delivery_note_item_id
            $transaction = $result['transaction'];
            if ($transaction instanceof \App\Models\InventoryTransaction) {
                if (!$transaction->delivery_note_item_id) {
                    $transaction->delivery_note_item_id = $item->id;
                    $transaction->save();
                    Log::info('Updated transaction with delivery_note_item_id', [
                        'transaction_id' => $transaction->id,
                        'delivery_note_item_id' => $item->id,
                    ]);
                }
            }

            // Update item - refresh to get latest data
            $item->refresh();
            $item->dispatched_qty += $actualQty;
            $item->remaining_qty -= $actualQty;
            
            // Ensure remaining_qty doesn't go negative (safety check)
            if ($item->remaining_qty < 0) {
                $item->remaining_qty = 0;
            }
            
            // Update item status
            if ($item->remaining_qty <= 0) {
                $item->status = 'dispatched';
            } elseif ($item->dispatched_qty > 0 && $item->remaining_qty > 0) {
                $item->status = 'partial';
            }
            
            $item->save();

            Log::info('Delivery note item updated', [
                'delivery_note_item_id' => $item->id,
                'new_dispatched_qty' => $item->dispatched_qty,
                'new_remaining_qty' => $item->remaining_qty,
                'new_status' => $item->status,
            ]);

            // Update delivery note status immediately
            $this->updateDeliveryNoteStatus();

            // Reload the delivery note to show updated data
            $this->deliveryNote->refresh();
            $this->deliveryNote->load('items');

            $this->dispatchQuantities[$itemId] = 0;
            session()->flash('success', "Dispatched {$actualQty} units successfully. Transaction ID: {$transaction->id}");

        } catch (\Exception $e) {
            Log::error('Error dispatching FG', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'delivery_note_id' => $this->deliveryNote->id,
                'delivery_note_item_id' => $itemId,
                'quantity' => $actualQty,
            ]);
            session()->flash('error', 'Error dispatching: ' . $e->getMessage());
        }
    }

    public function updateDeliveryNoteStatus()
    {
        $this->deliveryNote->refresh();
        $this->deliveryNote->load('items');
        
        // Check if all items are fully dispatched
        $allDispatched = $this->deliveryNote->items->every(function($item) {
            return $item->status === 'dispatched' || $item->remaining_qty <= 0;
        });
        
        // Check if any item has been partially dispatched (has dispatched_qty > 0 but remaining_qty > 0)
        $anyPartial = $this->deliveryNote->items->contains(function($item) {
            return ($item->dispatched_qty > 0 && $item->remaining_qty > 0) || $item->status === 'partial';
        });
        
        // Check if any item has been dispatched at all
        $anyDispatched = $this->deliveryNote->items->contains(function($item) {
            return $item->dispatched_qty > 0;
        });

        if ($allDispatched && $anyDispatched) {
            $this->deliveryNote->update(['status' => 'dispatched']);
        } elseif ($anyPartial) {
            $this->deliveryNote->update(['status' => 'partial']);
        } elseif ($anyDispatched) {
            // If some items are dispatched but none are partial, check if all are dispatched
            // This handles edge cases
            if ($allDispatched) {
                $this->deliveryNote->update(['status' => 'dispatched']);
            } else {
                $this->deliveryNote->update(['status' => 'partial']);
            }
        }
        // If nothing is dispatched, keep status as 'draft'
    }

    /**
     * Create sales invoice (accounting entry) for this delivery note.
     * Uses customer's Account Receivable and Sales Revenue ledgers from customer finance settings.
     */
    public function testButton()
    {
        session()->flash('success', 'Test button works! Livewire is functioning correctly.');
        Log::info('Test button clicked');
    }

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

            // Only allow invoice when something has been dispatched
            if ($this->deliveryNote->status !== 'dispatched' && $this->deliveryNote->status !== 'partial') {
                Log::warning('Invoice creation blocked: Status is not dispatched or partial', [
                    'status' => $this->deliveryNote->status,
                ]);
                session()->flash('error', 'Invoice can only be created after dispatch.');
                return;
            }

            $customer = $this->deliveryNote->jobOrder->customer;
            
            Log::info('Customer loaded', [
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'account_receivable' => $customer->account_receivable ?? 'empty',
                'sales_revenue' => $customer->sales_revenue ?? 'empty',
            ]);

            if (empty($customer->account_receivable) || empty($customer->sales_revenue)) {
                Log::warning('Customer missing finance settings', [
                    'customer_id' => $customer->id,
                    'account_receivable' => $customer->account_receivable ?? 'empty',
                    'sales_revenue' => $customer->sales_revenue ?? 'empty',
                ]);
                session()->flash('error', 'Customer is missing Account Receivable or Sales Revenue ledger mapping. Please configure these in customer finance settings first.');
                return;
            }

            // Find the Sales Voucher entry type
            $salesEntryType = EntryType::where('label', 'sales')->first();
            if (!$salesEntryType) {
                Log::warning('Sales Voucher entry type not found');
                session()->flash('error', 'Sales Voucher entry type not configured. Please run seeders or configure entry types.');
                return;
            }
            
            Log::info('Sales entry type found', [
                'entry_type_id' => $salesEntryType->id,
                'entry_type_name' => $salesEntryType->name,
            ]);

            // Prevent duplicate invoice for same DN
            $existingInvoice = Entry::where('entrytype_id', $salesEntryType->id)
                ->where('narration', 'like', '%' . $this->deliveryNote->dn_number . '%')
                ->first();
            if ($existingInvoice) {
                session()->flash('info', 'An invoice already exists for this delivery note (Entry #' . $existingInvoice->formatted_number . ').');
                return;
            }

            // Resolve ledgers from customer finance mapping (by ledger name)
            $receivableLedger = Ledger::where('name', $customer->account_receivable)->first();
            $salesLedger = Ledger::where('name', $customer->sales_revenue)->first();

            Log::info('Ledgers lookup', [
                'account_receivable_name' => $customer->account_receivable,
                'receivable_ledger_found' => $receivableLedger ? true : false,
                'sales_revenue_name' => $customer->sales_revenue,
                'sales_ledger_found' => $salesLedger ? true : false,
            ]);

            if (!$receivableLedger || !$salesLedger) {
                Log::warning('Ledgers not found', [
                    'account_receivable_name' => $customer->account_receivable,
                    'sales_revenue_name' => $customer->sales_revenue,
                ]);
                session()->flash('error', 'Could not find ledger(s) for Account Receivable or Sales Revenue. Please ensure ledger names in customer finance tab match Chart of Accounts.');
                return;
            }

            // Calculate invoice total based on dispatched quantities and selling prices from job order items
            $invoiceTotal = 0;
            foreach ($this->deliveryNote->items as $item) {
                if ($item->dispatched_qty <= 0) {
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

                $lineTotal = $unitPrice * (float) $item->dispatched_qty;
                $invoiceTotal += $lineTotal;
            }

            Log::info('Invoice total calculated', [
                'invoice_total' => $invoiceTotal,
                'items_count' => $this->deliveryNote->items->count(),
            ]);

            if ($invoiceTotal <= 0) {
                Log::warning('Invoice total is zero or negative');
                session()->flash('error', 'Cannot create invoice: No dispatched quantities with valid prices were found.');
                return;
            }

            // Create sales entry
            $entry = Entry::create([
                'date' => $this->deliveryNote->dispatch_date?->format('Y-m-d') ?? now()->format('Y-m-d'),
                'entrytype_id' => $salesEntryType->id,
                'number' => null, // auto-numbering by EntryType
                'tag_id' => null,
                'narration' => "Invoice for Delivery Note {$this->deliveryNote->dn_number} - {$customer->name}",
                'dr_total' => $invoiceTotal,
                'cr_total' => $invoiceTotal,
            ]);

            // DR Accounts Receivable
            $entry->entryItems()->create([
                'ledger_id' => $receivableLedger->id,
                'amount' => $invoiceTotal,
                'dc' => 'D',
                'reconciliation_date' => null,
            ]);

            // CR Sales Revenue
            $entry->entryItems()->create([
                'ledger_id' => $salesLedger->id,
                'amount' => $invoiceTotal,
                'dc' => 'C',
                'reconciliation_date' => null,
            ]);

            // Refresh entry to ensure number is set and load relationships
            $entry->refresh();
            $entry->load('entryType');
            
            Log::info('Invoice created successfully', [
                'entry_id' => $entry->id,
                'entry_number' => $entry->number,
                'formatted_number' => $entry->formatted_number,
            ]);
            
            session()->flash('success', 'Sales invoice created successfully for this delivery note. Entry #: ' . $entry->formatted_number);
            
            // Refresh the delivery note to show updated data
            $this->deliveryNote->refresh();
            $this->deliveryNote->load('items', 'jobOrder.customer');
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

    public function printDispatch($transactionId)
    {
        // Trigger JavaScript to print specific dispatch
        $this->dispatch('openDispatchPrintDialog', transactionId: $transactionId);
    }

    public function getDispatchHistory()
    {
        // Get all dispatch transactions for this delivery note
        $transactions = \App\Models\InventoryTransaction::where('related_doc_type', 'DeliveryNote')
            ->where('related_doc_id', $this->deliveryNote->id)
            ->where('txn_type', 'delivery')
            ->orderBy('created_at', 'desc')
            ->get();

        return $transactions;
    }

    public function render()
    {
        return view('livewire.delivery-note-detail');
    }
}

