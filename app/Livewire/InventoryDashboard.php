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

        $results = $query->get();
        
        // For RAW category, show the balance (available after consumption), not just inventory table qty
        // This ensures consistency with the Raw Materials card
        foreach ($results as $result) {
            if ($result->category === 'RAW') {
                $result->total_qty = $this->getBalanceRawMaterialsQuantity();
                break;
            }
        }
        
        return $results;
    }

    public function getInventoryByWarehouse()
    {
        $query = Inventory::selectRaw('warehouse, SUM(qty_available) as total_qty')
            ->groupBy('warehouse');

        if ($this->selectedCategory) {
            $query->where('category', $this->selectedCategory);
        }

        $results = $query->get();
        
        // For warehouses, if showing all categories or RAW category, adjust RAW qty to show balance
        // This ensures consistency with the Raw Materials card
        if (!$this->selectedCategory || $this->selectedCategory === 'RAW') {
            $rawBalance = $this->getBalanceRawMaterialsQuantity();
            
            foreach ($results as $result) {
                // Check if this warehouse has RAW materials in inventory table
                $rawQtyInInventory = Inventory::where('warehouse', $result->warehouse)
                    ->where('category', 'RAW')
                    ->sum('qty_available');
                
                if ($rawQtyInInventory > 0 || $rawBalance > 0) {
                    // Adjust: subtract inventory table RAW qty, add actual balance
                    // This way, if RAW balance is 0, the warehouse total will also reflect 0 for RAW
                    $result->total_qty = $result->total_qty - $rawQtyInInventory + $rawBalance;
                }
            }
        }
        
        return $results;
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

    /**
     * Get balance raw materials quantity (available raw materials after consumption)
     * This calculates: Total Received - Total Consumed = Balance
     */
    public function getBalanceRawMaterialsQuantity()
    {
        // Calculate from transactions to get accurate balance
        $totalReceived = \App\Models\InventoryTransaction::where('category', 'RAW')
            ->where('txn_type', 'receipt')
            ->sum('qty');
        
        // Get consumed quantity (this method handles missing transactions by estimating from WIP+FG)
        $totalConsumed = $this->getTotalRawMaterialsConsumed();
        
        // Balance = Received - Consumed
        $balance = $totalReceived - $totalConsumed;
        
        // Ensure balance is not negative
        return max(0, $balance);
    }

    /**
     * Get total raw materials received (all time receipts)
     */
    public function getTotalRawMaterialsReceived()
    {
        return \App\Models\InventoryTransaction::where('category', 'RAW')
            ->where('txn_type', 'receipt')
            ->sum('qty');
    }

    /**
     * Get total raw materials consumed (for production)
     * Includes:
     * 1. Explicit 'consume' transactions
     * 2. Raw materials linked to completed production orders (via Transaction ID in notes)
     * 3. If neither exists, estimates from WIP + FG
     */
    public function getTotalRawMaterialsConsumed()
    {
        // Check both generic 'RAW' item_code and specific material codes from explicit consume transactions
        $consumedGeneric = abs(\App\Models\InventoryTransaction::where('category', 'RAW')
            ->where('txn_type', 'consume')
            ->where('item_code', 'RAW')
            ->sum('qty'));
        
        $consumedSpecific = abs(\App\Models\InventoryTransaction::where('category', 'RAW')
            ->where('txn_type', 'consume')
            ->where('item_code', '!=', 'RAW')
            ->whereNotNull('item_code')
            ->sum('qty'));
        
        $consumedFromTransactions = $consumedGeneric + $consumedSpecific;
        
        // Also count raw materials that were used in production orders (based on completed quantities)
        // Production orders store Transaction ID in their notes: "Transaction ID: {id}"
        // We need to count completed_quantity from production order items, not just fully completed orders
        $productionOrdersWithTransactions = \App\Models\ProductionOrder::whereNotNull('notes')
            ->where('notes', 'like', '%Transaction ID:%')
            ->get();
        
        $consumedFromCompletedProduction = 0;
        foreach ($productionOrdersWithTransactions as $productionOrder) {
            // Extract transaction IDs from notes
            if (preg_match('/Transaction ID: (\d+)/', $productionOrder->notes, $matches)) {
                $transactionId = (int)$matches[1];
                
                // Find the raw material transaction that was linked to this production order
                $rawMaterialTransaction = \App\Models\InventoryTransaction::where('id', $transactionId)
                    ->where('category', 'RAW')
                    ->where('txn_type', 'receipt')
                    ->first();
                
                if ($rawMaterialTransaction && $rawMaterialTransaction->qty) {
                    // Get the completed quantity from all production order items
                    // This represents how much raw material has been consumed (produced)
                    $completedQuantity = (float)$productionOrder->items()->sum('completed_quantity');
                    
                    if ($completedQuantity > 0) {
                        // Any completed quantity means raw materials were consumed
                        // This is a 1:1 mapping: completed quantity in production = consumed raw materials
                        $consumedFromCompletedProduction += $completedQuantity;
                    }
                }
            }
        }
        
        // Sum all completed quantities from production order items
        // This is the most accurate source: completed production = consumed raw materials (1:1 conversion)
        $allCompletedProductionQty = (float)\App\Models\ProductionOrderItem::sum('completed_quantity');
        
        // Use the higher of: 
        // 1. Explicit consume transactions + linked production orders
        // 2. Sum of ALL completed production quantities (catches cases where Transaction ID tracking is missing)
        $calculatedFromLinked = $consumedFromTransactions + $consumedFromCompletedProduction;
        $calculatedFromAllProduction = $consumedFromTransactions + $allCompletedProductionQty;
        
        // Use the maximum to ensure we capture all consumption
        // This handles both tracked (via Transaction ID) and untracked production orders
        $totalConsumed = max($calculatedFromLinked, $calculatedFromAllProduction);
        
        // Final fallback: If still no consumption found, estimate from WIP + FG
        if ($totalConsumed == 0) {
            // WIP quantity represents raw materials in production
            $wipQty = $this->getWorkInProgressQuantity();
            
            // FG quantity represents raw materials that became finished goods
            // Note: This assumes 1:1 conversion. Adjust if needed based on your conversion ratios
            $fgQty = $this->getInventoryByCategory()
                ->where('category', 'FG')
                ->sum('total_qty');
            
            // Total consumed = WIP + FG (if no transactions exist)
            return $wipQty + $fgQty;
        }
        
        return $totalConsumed;
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

            // Check if a production order already exists for THIS SPECIFIC transaction (by transaction ID in notes)
            // Each transaction should be tracked separately, even if they share the same item code
            $transactionIdMarker = "Transaction ID: {$this->selectedTransaction->id}";
            $existingProductionOrder = \App\Models\ProductionOrder::where('job_order_id', $jobOrder->id)
                ->where('notes', 'like', '%' . $transactionIdMarker . '%')
                ->first();
            
            if ($existingProductionOrder) {
                session()->flash('error', 'A production order already exists for this inventory transaction. Cannot create duplicate production orders.');
                return;
            }

            // Create production order with transaction ID in notes for tracking
            $notesWithTransactionId = $this->productionOrderForm['notes'] . " | {$transactionIdMarker}";
            $productionOrder = \App\Models\ProductionOrder::create([
                'production_order_number' => $this->productionOrderForm['production_order_number'],
                'job_order_id' => $jobOrder->id,
                'supplier_id' => $jobOrder->supplier_id,
                'date' => $this->productionOrderForm['date'],
                'status' => 'pending',
                'notes' => $notesWithTransactionId,
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
            'balanceRawMaterialsQuantity' => $this->getBalanceRawMaterialsQuantity(),
            'totalRawMaterialsReceived' => $this->getTotalRawMaterialsReceived(),
            'totalRawMaterialsConsumed' => $this->getTotalRawMaterialsConsumed(),
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
