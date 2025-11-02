<?php

namespace App\Livewire;

use App\Models\Inventory;
use App\Services\InventoryService;
use Livewire\Component;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\DB;

class InventoryDashboard extends Component
{
    protected $layout = 'components.layouts.app';

    public $inventorySummary = [];
    public $selectedCategory = '';
    public $selectedWarehouse = '';
    
    // Production Order Modal
    public $showProductionOrderModal = false;
    public $selectedTransaction = null;
    public $productionOrderForm = [
        'production_order_number' => '',
        'date' => '',
        'quantity' => '',
        'notes' => ''
    ];

    public function mount()
    {
        $this->loadInventorySummary();
    }

    public function loadInventorySummary()
    {
        $inventoryService = app(InventoryService::class);
        $this->inventorySummary = $inventoryService->getInventorySummary();
    }

    public function filterByCategory($category)
    {
        $this->selectedCategory = $category;
        $this->loadInventorySummary();
    }

    public function filterByWarehouse($warehouse)
    {
        $this->selectedWarehouse = $warehouse;
        $this->loadInventorySummary();
    }

    public function getInventoryByCategory()
    {
        $query = Inventory::selectRaw('category, SUM(qty_available) as total_qty')
            ->groupBy('category');

        if ($this->selectedWarehouse) {
            $query->where('warehouse', $this->selectedWarehouse);
        }

        return $query->get();
    }

    public function getInventoryByWarehouse()
    {
        $query = Inventory::selectRaw('warehouse, SUM(qty_available) as total_qty')
            ->groupBy('warehouse');

        if ($this->selectedCategory) {
            $query->where('category', $this->selectedCategory);
        }

        return $query->get();
    }

    public function getLowStockItems()
    {
        return Inventory::where('qty_available', '<', 10)
            ->orderBy('qty_available')
            ->get();
    }

    public function getWorkInProgressQuantity()
    {
        // Calculate WIP as sum of (quantity - completed_quantity) from all in-progress production order items
        $inProgressItems = \App\Models\ProductionOrderItem::whereHas('productionOrder', function($query) {
                $query->whereIn('status', ['pending', 'in_production', 'ready_for_production']);
            })
            ->get();
        
        $wipQuantity = 0;
        foreach ($inProgressItems as $item) {
            $remaining = $item->quantity - ($item->completed_quantity ?? 0);
            if ($remaining > 0) {
                $wipQuantity += $remaining;
            }
        }
        
        return $wipQuantity;
    }

    public function getRecentTransactions()
    {
        return \App\Models\InventoryTransaction::with(['inventory', 'grn.productionOrder', 'grn.purchaseOrder.jobOrder'])
            ->orderBy('txn_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();
    }

    public function openProductionOrderModal($transactionId)
    {
        $this->selectedTransaction = \App\Models\InventoryTransaction::with(['grn.purchaseOrder.jobOrder.supplier', 'grn.purchaseOrder.jobOrder.customer'])
            ->find($transactionId);
        
        if ($this->selectedTransaction) {
            $this->productionOrderForm = [
                'production_order_number' => \App\Models\ProductionOrder::generateProductionOrderNumber(),
                'date' => now()->format('Y-m-d'),
                'quantity' => $this->selectedTransaction->qty,
                'notes' => "Production order created from inventory transaction: {$this->selectedTransaction->lot_code}"
            ];
            $this->showProductionOrderModal = true;
        }
    }

    public function closeProductionOrderModal()
    {
        $this->showProductionOrderModal = false;
        $this->selectedTransaction = null;
        $this->productionOrderForm = [
            'production_order_number' => '',
            'date' => '',
            'quantity' => '',
            'notes' => ''
        ];
    }

    public function createProductionOrder()
    {
        try {
            if (!$this->selectedTransaction) {
                session()->flash('error', 'No transaction selected.');
                return;
            }

            $jobOrder = $this->selectedTransaction->getJobOrder();
            if (!$jobOrder) {
                session()->flash('error', 'No job order found for this transaction.');
                return;
            }

            // Validate form
            if (empty($this->productionOrderForm['production_order_number']) || empty($this->productionOrderForm['date'])) {
                session()->flash('error', 'Please fill in all required fields.');
                return;
            }

            if ($this->productionOrderForm['quantity'] > $this->selectedTransaction->qty) {
                session()->flash('error', 'Production quantity cannot exceed available quantity.');
                return;
            }

            // Check if a production order already exists for this transaction (by lot code)
            // Check if any production order has the same lot code in its notes
            $existingProductionOrder = \App\Models\ProductionOrder::where('job_order_id', $jobOrder->id)
                ->where('notes', 'like', '%' . $this->selectedTransaction->lot_code . '%')
                ->exists();
            
            if ($existingProductionOrder) {
                session()->flash('error', 'A production order already exists for this inventory transaction (Lot: ' . $this->selectedTransaction->lot_code . '). Cannot create duplicate production orders.');
                return;
            }

            // Create production order
            $productionOrder = \App\Models\ProductionOrder::create([
                'production_order_number' => $this->productionOrderForm['production_order_number'],
                'job_order_id' => $jobOrder->id,
                'supplier_id' => $jobOrder->supplier_id,
                'date' => $this->productionOrderForm['date'],
                'status' => 'pending',
                'notes' => $this->productionOrderForm['notes'],
            ]);

            // Find the corresponding job order item for this inventory transaction
            $jobOrderItem = null;
            $itemType = 'box';
            
            // Try to find the job order item that matches this inventory transaction
            if ($this->selectedTransaction->item_code) {
                // Look for matching job order boxes or dividers
                $jobOrderBox = $jobOrder->boxes()->where('id', $this->selectedTransaction->item_code)->first();
                if ($jobOrderBox) {
                    $jobOrderItem = $jobOrderBox;
                    $itemType = 'box';
                } else {
                    $jobOrderDivider = $jobOrder->dividers()->where('id', $this->selectedTransaction->item_code)->first();
                    if ($jobOrderDivider) {
                        $jobOrderItem = $jobOrderDivider;
                        $itemType = 'divider';
                    }
                }
            }
            
            // If no specific item found, use the first available item from the job order
            if (!$jobOrderItem) {
                $jobOrderItem = $jobOrder->boxes()->first();
                if (!$jobOrderItem) {
                    $jobOrderItem = $jobOrder->dividers()->first();
                    $itemType = 'divider';
                }
            }
            
            if ($jobOrderItem) {
                // Create production order item
                \App\Models\ProductionOrderItem::create([
                    'production_order_id' => $productionOrder->id,
                    'item_type' => $itemType,
                    'item_id' => $jobOrderItem->id,
                    'quantity' => $this->productionOrderForm['quantity'],
                    'completed_quantity' => 0,
                    'status' => 'pending',
                ]);
            }

            session()->flash('success', "Production order {$productionOrder->production_order_number} created successfully!");
            $this->closeProductionOrderModal();

        } catch (\Exception $e) {
            session()->flash('error', 'Error creating production order: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.inventory-dashboard', [
            'inventoryByCategory' => $this->getInventoryByCategory(),
            'inventoryByWarehouse' => $this->getInventoryByWarehouse(),
            'lowStockItems' => $this->getLowStockItems(),
            'recentTransactions' => $this->getRecentTransactions(),
            'workInProgressQuantity' => $this->getWorkInProgressQuantity(),
        ]);
    }

    /**
     * Danger zone: Reset transactional data for testing (local env only)
     */
    public function resetTestData(): void
    {
        if (!app()->environment('local')) {
            session()->flash('error', 'Reset is only allowed in local environment.');
            return;
        }

        try {
            FacadesDB::transaction(function () {
                // Temporarily disable FK checks to avoid constraint errors
                FacadesDB::statement('SET FOREIGN_KEY_CHECKS=0');
                // Inventory
                \App\Models\InventoryTransaction::query()->delete();
                \App\Models\InventoryLayer::query()->delete();
                \App\Models\Inventory::query()->delete();

                // GRNs
                \App\Models\GRNItem::query()->delete();
                \App\Models\GRN::query()->delete();

                // Production Orders
                \App\Models\ProductionOrderItem::query()->delete();
                \App\Models\ProductionOrder::query()->delete();

                // Purchase Orders
                \App\Models\PurchaseOrderItem::query()->delete();
                \App\Models\PurchaseOrder::query()->delete();

                // Job Orders (keep masters but remove orders and their items)
                \App\Models\JobOrderBox::query()->delete();
                \App\Models\JobOrderDivider::query()->delete();
                \App\Models\JobOrder::query()->delete();

                // Re-enable FK checks
                FacadesDB::statement('SET FOREIGN_KEY_CHECKS=1');
            });

            session()->flash('success', 'All transactional data has been reset.');
            // Reload dashboard widgets
            $this->loadInventorySummary();
        } catch (\Throwable $e) {
            session()->flash('error', 'Reset failed: ' . $e->getMessage());
        }
    }
}
