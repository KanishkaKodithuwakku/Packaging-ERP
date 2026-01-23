<?php

namespace App\Livewire\DeliveryNotes;

use App\Models\DeliveryNote;
use App\Models\JobOrder;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;

class DeliveryNotesManagement extends Component
{
    use WithPagination;

    protected $layout = 'components.layouts.app';

    public $deliveryNotes = [];
    public $showDeleteModal = false;
    public $deliveryNoteToDelete = null;

    public function mount()
    {
        $this->loadDeliveryNotes();
    }

    public function loadDeliveryNotes()
    {
        $this->deliveryNotes = DeliveryNote::with(['jobOrder.customer', 'items', 'invoice'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function editDeliveryNote($id)
    {
        $deliveryNote = DeliveryNote::with('invoice')->find($id);
        
        // Check if delivery note is invoiced
        if ($deliveryNote && $deliveryNote->invoice) {
            session()->flash('error', 'Cannot edit an invoiced delivery note.');
            return;
        }
        
        return redirect()->route('delivery-note-detail', $id);
    }

    public function openDeleteModal($id)
    {
        $deliveryNote = DeliveryNote::with('invoice')->find($id);
        
        // Check if delivery note is invoiced
        if ($deliveryNote && $deliveryNote->invoice) {
            session()->flash('error', 'Cannot delete an invoiced delivery note.');
            return;
        }
        
        $this->deliveryNoteToDelete = $id;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->deliveryNoteToDelete = null;
    }

    public function deleteDeliveryNote()
    {
        try {
            if (!$this->deliveryNoteToDelete) {
                return;
            }

            $deliveryNote = DeliveryNote::with(['invoice', 'items.inventoryTransactions'])->find($this->deliveryNoteToDelete);
            
            if (!$deliveryNote) {
                session()->flash('error', 'Delivery note not found.');
                $this->closeDeleteModal();
                return;
            }

            // Double check it's not invoiced
            if ($deliveryNote->invoice) {
                session()->flash('error', 'Cannot delete an invoiced delivery note.');
                $this->closeDeleteModal();
                return;
            }

            $dnNumber = $deliveryNote->dn_number;
            
            \Illuminate\Support\Facades\DB::transaction(function () use ($deliveryNote) {
                // First, reverse inventory transactions and restore stock
                foreach ($deliveryNote->items as $item) {
                    // Get all inventory transactions for this delivery note item
                    $transactions = $item->inventoryTransactions;
                    
                    foreach ($transactions as $transaction) {
                        // Reverse the transaction - add back the consumed quantity
                        if ($transaction->lot_code) {
                            $inventoryLayer = \App\Models\InventoryLayer::where('lot_code', $transaction->lot_code)->first();
                            if ($inventoryLayer) {
                                $inventoryLayer->increment('qty_available', abs($transaction->qty));
                                
                                // Also update main inventory record
                                $inventory = \App\Models\Inventory::where('lot_code', $transaction->lot_code)->first();
                                if ($inventory) {
                                    $inventory->increment('qty_available', abs($transaction->qty));
                                }
                            }
                        }
                        
                        // Delete the transaction
                        $transaction->delete();
                    }
                }
                
                // Delete delivery note items
                $deliveryNote->items()->delete();
                
                // Delete delivery note
                $deliveryNote->delete();
            });
            
            session()->flash('success', "Delivery Note {$dnNumber} deleted successfully and stock restored.");
            
            Log::info('Delivery note deleted with stock restoration', [
                'dn_number' => $dnNumber,
                'deleted_by' => auth()->user()->name
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to delete delivery note', [
                'error' => $e->getMessage(),
                'dn_id' => $this->deliveryNoteToDelete,
                'trace' => $e->getTraceAsString()
            ]);
            
            session()->flash('error', 'Failed to delete delivery note: ' . $e->getMessage());
        }
        
        $this->closeDeleteModal();
        $this->loadDeliveryNotes();
    }

    public function render()
    {
        return view('livewire.delivery-notes.delivery-notes-management');
    }
}
