<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ProductionOrder;
use App\Models\PurchaseOrder;
use App\Models\JobOrder;
use Illuminate\Support\Facades\Log;

class ProductionOrderManagement extends Component
{
    public $productionOrders = [];
    public $showCreateModal = false;
    public $selectedPurchaseOrderId = null;
    public $selectedPurchaseOrder = null;
    public $form = [];

    protected $rules = [
        'form.purchase_order_id' => 'required',
        'form.date' => 'required|date',
        'form.notes' => 'nullable|string',
    ];

    public function mount()
    {
        $this->loadProductionOrders();
        $this->resetForm();
    }

    public function loadProductionOrders()
    {
        $this->productionOrders = ProductionOrder::with(['supplier', 'jobOrder.customer', 'items', 'grns'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function resetForm()
    {
        $this->form = [
            'purchase_order_id' => '',
            'date' => now()->format('Y-m-d'),
            'notes' => '',
        ];
        $this->selectedPurchaseOrderId = null;
        $this->selectedPurchaseOrder = null;
    }

    public function openCreateModal($purchaseOrderId = null)
    {
        $this->resetForm();
        
        if ($purchaseOrderId) {
            $this->selectedPurchaseOrderId = $purchaseOrderId;
            $this->selectedPurchaseOrder = PurchaseOrder::with(['supplier', 'jobOrder', 'items'])->find($purchaseOrderId);
            
            if ($this->selectedPurchaseOrder) {
                $this->form['purchase_order_id'] = $purchaseOrderId;
            }
        }
        
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function generateProductionOrder()
    {
        $this->validate();

        try {
            // Check if Purchase Order is confirmed
            $purchaseOrder = PurchaseOrder::find($this->form['purchase_order_id']);
            if ($purchaseOrder->status !== 'confirmed') {
                session()->flash('error', 'Only confirmed Purchase Orders can be used to create Production Orders.');
                return;
            }

            // Generate Production Order number
            $poNumber = ProductionOrder::generateProductionOrderNumber();

            // Create Production Order
            $productionOrder = ProductionOrder::create([
                'production_order_number' => $poNumber,
                'date' => $this->form['date'],
                'job_order_id' => $purchaseOrder->job_order_id,
                'supplier_id' => $purchaseOrder->supplier_id,
                'status' => 'pending',
                'notes' => $this->form['notes'],
            ]);

            // Get purchase order with items
            $purchaseOrder = PurchaseOrder::with(['items'])->find($this->form['purchase_order_id']);

            // Add items from Purchase Order to Production Order
            // Use job order's order quantity (finished goods quantity) instead of purchase order quantity (material quantity)
            foreach ($purchaseOrder->items as $item) {
                // Get the job order item to get the order quantity (finished goods quantity)
                $jobOrderItem = null;
                if ($item->item_type === 'box') {
                    $jobOrderItem = \App\Models\JobOrderBox::find($item->item_id);
                    $orderQty = $jobOrderItem ? (int) $jobOrderItem->order_qty : $item->quantity;
                } elseif ($item->item_type === 'divider') {
                    $jobOrderItem = \App\Models\JobOrderDivider::find($item->item_id);
                    $orderQty = $jobOrderItem ? (int) $jobOrderItem->quantity : $item->quantity;
                } else {
                    $orderQty = $item->quantity; // Fallback
                }
                
                $productionOrder->items()->create([
                    'item_type' => $item->item_type,
                    'item_id' => $item->item_id,
                    'quantity' => $orderQty, // Use job order's order quantity (finished goods), not material quantity
                    'completed_quantity' => 0,
                    'status' => 'pending',
                ]);
            }

            Log::info('Production Order generated successfully', [
                'po_id' => $productionOrder->id,
                'po_number' => $poNumber,
                'purchase_order_id' => $this->form['purchase_order_id'],
                'items_count' => $productionOrder->items()->count()
            ]);

            $this->closeCreateModal();
            $this->loadProductionOrders();
            
            session()->flash('success', "Production Order {$poNumber} generated successfully!");
            
        } catch (\Exception $e) {
            Log::error('Failed to generate Production Order', [
                'error' => $e->getMessage(),
                'form_data' => $this->form
            ]);
            
            session()->flash('error', 'Failed to generate Production Order: ' . $e->getMessage());
        }
    }

    public function viewProductionOrder($id)
    {
        return redirect()->route('production-order-detail', $id);
    }

    public function render()
    {
        return view('livewire.production-order-management', [
            'confirmedPurchaseOrders' => PurchaseOrder::with(['supplier', 'jobOrder'])
                ->where('status', 'confirmed')
                ->get(),
        ]);
    }
}
