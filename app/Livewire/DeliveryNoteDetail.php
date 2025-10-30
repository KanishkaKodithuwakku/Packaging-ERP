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
        $this->deliveryNote = DeliveryNote::with(['jobOrder.customer', 'items'])->findOrFail($id);
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
            $deliveryService = app(DeliveryService::class);
            $result = $deliveryService->dispatchFg($this->deliveryNote, $item, $actualQty);

            // Update item
            $item->dispatched_qty += $actualQty;
            $item->remaining_qty -= $actualQty;
            
            if ($item->remaining_qty <= 0) {
                $item->status = 'dispatched';
            } elseif ($item->dispatched_qty > 0) {
                $item->status = 'partial';
            }
            
            $item->save();

            // Update delivery note status
            $this->updateDeliveryNoteStatus();

            // Reload the delivery note to show updated data
            $this->deliveryNote->refresh();
            $this->deliveryNote->load('items');

            $this->dispatchQuantities[$itemId] = 0;
            session()->flash('success', "Dispatched {$actualQty} units successfully.");

        } catch (\Exception $e) {
            Log::error('Error dispatching FG: ' . $e->getMessage());
            session()->flash('error', 'Error dispatching: ' . $e->getMessage());
        }
    }

    public function updateDeliveryNoteStatus()
    {
        $this->deliveryNote->refresh();
        $allDispatched = $this->deliveryNote->items->every(fn($item) => $item->status === 'dispatched');
        $anyPartial = $this->deliveryNote->items->contains(fn($item) => $item->status === 'partial');

        if ($allDispatched) {
            $this->deliveryNote->update(['status' => 'dispatched']);
        } elseif ($anyPartial) {
            $this->deliveryNote->update(['status' => 'partial']);
        }
    }

    public function printDeliveryNote()
    {
        // Simply trigger JavaScript print function
        $this->dispatch('openPrintDialog');
    }

    public function render()
    {
        return view('livewire.delivery-note-detail');
    }
}
