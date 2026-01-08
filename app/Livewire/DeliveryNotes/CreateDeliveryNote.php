<?php

namespace App\Livewire\DeliveryNotes;

use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\JobOrder;
use App\Services\DeliveryService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class CreateDeliveryNote extends Component
{
    protected $layout = 'components.layouts.app';

    public $form = [
        'job_order_id' => '',
        'dispatch_date' => '',
        'delivery_address' => '',
        'notes' => '',
    ];

    public $selectedJobOrder = null;
    public $availableFg = [];
    public $dispatchQuantities = [];

    protected $rules = [
        'form.job_order_id' => 'required|exists:job_orders,id',
        'form.dispatch_date' => 'required|date',
        'form.delivery_address' => 'nullable|string',
        'form.notes' => 'nullable|string',
        'dispatchQuantities.*' => 'nullable|numeric|min:0',
    ];

    protected $messages = [
        'form.job_order_id.required' => 'Please select a job order.',
    ];

    public function mount()
    {
        $this->form['dispatch_date'] = now()->format('Y-m-d');
    }

    public function updatedFormJobOrderId()
    {
        if ($this->form['job_order_id']) {
            $this->selectedJobOrder = JobOrder::with(['boxes', 'dividers', 'customer'])->find($this->form['job_order_id']);
            $this->loadAvailableFg();
            
            // Auto-populate delivery address from customer
            if ($this->selectedJobOrder && $this->selectedJobOrder->customer) {
                $this->form['delivery_address'] = $this->selectedJobOrder->customer_address ?? '';
            }
        } else {
            $this->selectedJobOrder = null;
            $this->availableFg = [];
            $this->dispatchQuantities = [];
            $this->form['delivery_address'] = '';
        }
    }

    public function loadAvailableFg()
    {
        if (!$this->form['job_order_id']) {
            $this->availableFg = [];
            $this->dispatchQuantities = [];
            return;
        }

        $deliveryService = app(DeliveryService::class);
        $this->availableFg = $deliveryService->getAvailableFgForJobOrder($this->form['job_order_id']);
        
        // Initialize dispatch quantities as empty (0) to allow users to enter any amount
        $this->dispatchQuantities = [];
        foreach ($this->availableFg as $index => $fg) {
            $this->dispatchQuantities[$index] = 0;
        }
    }

    public function save()
    {
        $this->validate();

        if (empty($this->availableFg)) {
            session()->flash('error', 'No Finished Goods available for this job order.');
            return;
        }

        // Validate dispatch quantities against current available FG
        $deliveryService = app(DeliveryService::class);
        $validationErrors = [];
        
        foreach ($this->availableFg as $index => $fg) {
            $qty = (float)($this->dispatchQuantities[$index] ?? 0);
            
            // Skip validation if quantity is 0 (allow partial dispatch)
            if ($qty <= 0) {
                continue;
            }
            
            // Get current available quantity (may have changed since page load)
            $currentAvailableQty = $deliveryService->getAvailableFgQuantity($fg['material_code']);
            
            if ($qty > $currentAvailableQty) {
                $validationErrors[] = "Quantity ({$qty}) exceeds available quantity ({$currentAvailableQty}) for {$fg['description']}";
            }
        }
        
        if (!empty($validationErrors)) {
            session()->flash('error', implode('. ', $validationErrors));
            return;
        }
        
        // At least one item must have quantity > 0
        $hasAnyQuantity = false;
        foreach ($this->dispatchQuantities as $qty) {
            if ((float)$qty > 0) {
                $hasAnyQuantity = true;
                break;
            }
        }
        
        if (!$hasAnyQuantity) {
            session()->flash('error', 'Please enter quantities for at least one item.');
            return;
        }

        try {
            // Create delivery note
            $dn = DeliveryNote::create([
                'dn_number' => DeliveryNote::generateDnNumber(),
                'job_order_id' => $this->form['job_order_id'],
                'dispatch_date' => $this->form['dispatch_date'],
                'status' => 'draft',
                'delivery_address' => $this->form['delivery_address'],
                'notes' => $this->form['notes'],
            ]);

            // Create delivery note items and inventory transactions for items with quantity > 0
            $deliveryService = app(DeliveryService::class);
            $inventoryService = app(\App\Services\InventoryService::class);
            $itemsCreated = 0;
            
            foreach ($this->availableFg as $index => $fg) {
                $qty = (float)($this->dispatchQuantities[$index] ?? 0);
                
                // Skip items with zero or negative quantity
                if ($qty <= 0) {
                    continue;
                }
                
                // Verify available quantity again (may have changed)
                $currentAvailableQty = $deliveryService->getAvailableFgQuantity($fg['material_code']);
                if ($qty > $currentAvailableQty) {
                    throw new \Exception("Quantity ({$qty}) exceeds available quantity ({$currentAvailableQty}) for {$fg['description']}. Please refresh and try again.");
                }
                
                // Create delivery note item
                $dnItem = DeliveryNoteItem::create([
                    'delivery_note_id' => $dn->id,
                    'item_type' => $fg['item_type'],
                    'item_id' => $fg['item_id'],
                    'description' => $fg['description'],
                    'material_code' => $fg['material_code'],
                    'quantity' => $qty,
                    'dispatched_qty' => 0,
                    'remaining_qty' => $qty,
                    'status' => 'pending',
                ]);
                
                // Create inventory transaction to consume FG immediately using FIFO
                // Get the lot code from the first available inventory layer (before consumption)
                // This will be used as a reference, but actual consumption uses FIFO across layers
                $firstLayer = \App\Models\InventoryLayer::where('item_code', $fg['material_code'])
                    ->where('category', 'FG')
                    ->where('qty_available', '>', 0)
                    ->orderBy('receipt_date', 'asc')
                    ->first();
                
                $lotCode = $firstLayer ? $firstLayer->lot_code : 'DISPATCH-' . now()->format('YmdHis');
                
                // This will call processConsumption which handles FIFO and reduces layers automatically
                $inventoryService->recordTransaction([
                    'lot_code' => $lotCode, // Reference lot code (actual consumption uses FIFO across layers)
                    'item_code' => $fg['material_code'],
                    'category' => 'FG',
                    'txn_type' => 'consume',
                    'qty' => $qty,
                    'uom' => 'PCS',
                    'warehouse' => 'FINISHED_GOODS',
                    'related_doc_type' => 'DeliveryNote',
                    'related_doc_id' => $dn->id,
                    'delivery_note_item_id' => $dnItem->id,
                    'txn_date' => $this->form['dispatch_date'],
                    'remarks' => "FG consumed for delivery note {$dn->dn_number} - {$fg['description']}",
                ], 'FIFO');
                
                $itemsCreated++;
            }
            
            if ($itemsCreated === 0) {
                // No items created, delete the delivery note
                $dn->delete();
                session()->flash('error', 'No items were added to the delivery note.');
                return;
            }

            session()->flash('success', 'Delivery note created successfully!');
            return $this->redirect(route('delivery-notes-management'), navigate: true);

        } catch (\Exception $e) {
            Log::error('Error creating delivery note: ' . $e->getMessage());
            session()->flash('error', 'Error creating delivery note: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $deliveryService = app(DeliveryService::class);
        
        return view('livewire.delivery-notes.create-delivery-note', [
            'jobOrders' => JobOrder::with('customer')
                ->where('status', 'confirmed')
                ->whereHas('productionOrders', function($q) {
                    $q->whereHas('grns', function($grnQuery) {
                        $grnQuery->where('status', 'processed');
                    });
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->filter(function($jobOrder) use ($deliveryService) {
                    // Only include job orders that have remaining FG quantity
                    $availableFg = $deliveryService->getAvailableFgForJobOrder($jobOrder->id);
                    return count($availableFg) > 0; // Has at least one item with available FG > 0
                })
                ->values(),
        ]);
    }
}
