<?php

namespace App\Livewire;

use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
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

