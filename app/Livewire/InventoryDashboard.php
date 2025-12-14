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
    
    // Cache expensive calculations
    private $cachedBalanceRawMaterials = null;
    private $cachedTotalReceived = null;
    private $cachedTotalConsumed = null;
    private $cachedWIP = null;
    private $cachedInventoryByCategory = null;

    public function mount()
    {
        // Don't load inventory summary on mount to prevent memory issues
        // $this->loadInventorySummary();
        
        // Clear cache on mount to ensure fresh data
        $this->clearCache();
    }
    
    /**
     * Clear all cached calculations
     */
    public function clearCache()
    {
        $this->cachedBalanceRawMaterials = null;
        $this->cachedTotalReceived = null;
        $this->cachedTotalConsumed = null;
        $this->cachedWIP = null;
        $this->cachedInventoryByCategory = null;
    }
    
    /**
     * Refresh the dashboard data
     */
    public function refresh()
    {
        $this->clearCache();
        $this->dispatch('$refresh');
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
        // Return cached result if available and no filters applied
        if ($this->cachedInventoryByCategory !== null && !$this->selectedWarehouse) {
            return $this->cachedInventoryByCategory;
        }
        
        // Optimize: Use direct query without loading full models
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
        
        // Cache if no filters
        if (!$this->selectedWarehouse) {
            $this->cachedInventoryByCategory = $results;
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
        // Limit to prevent memory issues
        // Note: inventory table uses lot_code as primary key, not id
        return Inventory::where('qty_available', '<', 10)
            ->orderBy('qty_available')
            ->limit(20) // Limit to 20 items
            ->get(['lot_code', 'item_code', 'qty_available', 'uom']);
    }

    public function getWorkInProgressQuantity()
    {
        // Return cached result if available
        if ($this->cachedWIP !== null) {
            return $this->cachedWIP;
        }
        
        // Optimize: Use database aggregation instead of loading all records
        $result = (float)(\App\Models\ProductionOrderItem::whereHas('productionOrder', function($query) {
                $query->whereIn('status', ['pending', 'in_production', 'ready_for_production']);
            })
            ->selectRaw('SUM(quantity - COALESCE(completed_quantity, 0)) as wip_quantity')
            ->whereRaw('quantity > COALESCE(completed_quantity, 0)')
            ->value('wip_quantity') ?? 0);
        
        $this->cachedWIP = $result;
        return $result;
    }

    /**
     * Get balance raw materials quantity (available raw materials after consumption)
     * This calculates: Total Received - Total Consumed = Balance
     */
    public function getBalanceRawMaterialsQuantity()
    {
        // Return cached result if available
        if ($this->cachedBalanceRawMaterials !== null) {
            return $this->cachedBalanceRawMaterials;
        }
        
        // Calculate from transactions to get accurate balance
        $totalReceived = $this->getTotalRawMaterialsReceived();
        
        // Get consumed quantity (this method handles missing transactions by estimating from WIP+FG)
        $totalConsumed = $this->getTotalRawMaterialsConsumed();
        
        // Balance = Received - Consumed
        $balance = $totalReceived - $totalConsumed;
        
        // Ensure balance is not negative
        $result = max(0, $balance);
        $this->cachedBalanceRawMaterials = $result;
        return $result;
    }

    /**
     * Get total raw materials received (all time receipts)
     */
    public function getTotalRawMaterialsReceived()
    {
        // Return cached result if available
        if ($this->cachedTotalReceived !== null) {
            return $this->cachedTotalReceived;
        }
        
        $result = \App\Models\InventoryTransaction::where('category', 'RAW')
            ->where('txn_type', 'receipt')
            ->where(function($query) {
                $query->where('material_type', 'raw_material')
                      ->orWhereNull('material_type'); // Backward compatibility
            })
            ->sum('qty');
        
        // Ensure result is numeric (sum may return string)
        $result = (float) ($result ?? 0);
        
        $this->cachedTotalReceived = $result;
        return $result;
    }

    /**
     * Get consumables quantity (available consumables)
     */
    public function getConsumablesQuantity()
    {
        return (float)(\DB::table('inventory')
            ->where('category', 'RAW')
            ->where('material_type', 'consumable')
            ->sum('qty_available') ?? 0);
    }

    /**
     * Get consumables by type (grouped by item_code)
     */
    public function getConsumablesByType()
    {
        return \DB::table('inventory')
            ->where('category', 'RAW')
            ->where('material_type', 'consumable')
            ->selectRaw('item_code, SUM(qty_available) as total_qty, uom')
            ->groupBy('item_code', 'uom')
            ->orderBy('item_code')
            ->get();
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
        // Cache result to avoid recalculating multiple times
        if ($this->cachedTotalConsumed !== null) {
            return $this->cachedTotalConsumed;
        }
        // Check both generic 'RAW' item_code and specific material codes from explicit consume transactions
        // Exclude consumables - only count raw_material type
        $consumedGeneric = abs((float)(\App\Models\InventoryTransaction::where('category', 'RAW')
            ->where('txn_type', 'consume')
            ->where('item_code', 'RAW')
            ->where(function($query) {
                $query->where('material_type', 'raw_material')
                      ->orWhereNull('material_type'); // Backward compatibility
            })
            ->sum('qty') ?? 0));
        
        $consumedSpecific = abs((float)(\App\Models\InventoryTransaction::where('category', 'RAW')
            ->where('txn_type', 'consume')
            ->where('item_code', '!=', 'RAW')
            ->whereNotNull('item_code')
            ->where(function($query) {
                $query->where('material_type', 'raw_material')
                      ->orWhereNull('material_type'); // Backward compatibility
            })
            ->sum('qty') ?? 0));
        
        $consumedFromTransactions = $consumedGeneric + $consumedSpecific;
        
        // Optimize: Use database aggregation instead of loading all records
        // Sum all completed quantities from production order items directly
        // This is the most accurate source: completed production = consumed raw materials (1:1 conversion)
        $allCompletedProductionQty = (float)(\App\Models\ProductionOrderItem::sum('completed_quantity') ?? 0);
        
        // For production orders with transaction IDs, we can use a simpler approach
        // Since we're already summing all completed quantities above, we don't need to loop
        $consumedFromCompletedProduction = 0; // Simplified - using allCompletedProductionQty instead
        
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
            
            // FG quantity - use direct DB query to avoid circular dependency with getInventoryByCategory()
            // Note: This assumes 1:1 conversion. Adjust if needed based on your conversion ratios
            $fgQty = (float)(\DB::table('inventory')
                ->where('category', 'FG')
                ->sum('qty_available') ?? 0);
            
            // Total consumed = WIP + FG (if no transactions exist)
            $result = $wipQty + $fgQty;
            $this->cachedTotalConsumed = $result;
            return $result;
        }
        
        $this->cachedTotalConsumed = $totalConsumed;
        return $totalConsumed;
    }

    public function getRecentTransactions()
    {
        // Optimize query to only load necessary data and limit relationships
        $transactions = \App\Models\InventoryTransaction::select([
                'id', 'lot_code', 'item_code', 'category', 'txn_type', 
                'qty', 'uom', 'warehouse', 'related_doc_type', 
                'related_doc_id', 'txn_date', 'created_at'
            ])
            ->with([
                'grn:id,related_doc_id,related_doc_type',
                'grn.purchaseOrder:id,job_order_id',
                'grn.purchaseOrder.jobOrder:id,job_number,supplier_id,customer_id,status',
                'grn.purchaseOrder.jobOrder.supplier:id,name',
                'grn.purchaseOrder.jobOrder.customer:id,name'
            ])
            ->orderBy('txn_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10) // Reduced from 20 to 10 to save memory
            ->get();
        
        // Pre-load production orders to avoid N+1 queries in the view
        $jobOrderIds = $transactions->filter(function($t) {
            return $t->getJobOrder() !== null;
        })->map(function($t) {
            return $t->getJobOrder()->id;
        })->unique()->values();
        
        if ($jobOrderIds->isNotEmpty()) {
            $productionOrders = \App\Models\ProductionOrder::whereIn('job_order_id', $jobOrderIds)
                ->get()
                ->groupBy('job_order_id');
            
            // Attach production order info to transactions
            foreach ($transactions as $transaction) {
                $jobOrder = $transaction->getJobOrder();
                if ($jobOrder && isset($productionOrders[$jobOrder->id])) {
                    $matchingPO = $productionOrders[$jobOrder->id]
                        ->first(function($po) use ($transaction) {
                            return str_contains($po->notes ?? '', "Transaction ID: {$transaction->id}");
                        });
                    $transaction->hasProductionOrder = $matchingPO !== null;
                    $transaction->productionOrderId = $matchingPO ? $matchingPO->id : null;
                } else {
                    $transaction->hasProductionOrder = false;
                    $transaction->productionOrderId = null;
                }
            }
        }
        
        return $transactions;
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
        try {
            // Force clear cache at the start of render to ensure fresh calculations
            $this->clearCache();
            
            // Calculate all values once and cache them
            $balanceRawMaterialsQuantity = $this->getBalanceRawMaterialsQuantity();
            $totalRawMaterialsReceived = $this->getTotalRawMaterialsReceived();
            $totalRawMaterialsConsumed = $this->getTotalRawMaterialsConsumed();
            $workInProgressQuantity = $this->getWorkInProgressQuantity();
            $consumablesQuantity = $this->getConsumablesQuantity();
            $consumablesByType = $this->getConsumablesByType();
            
            // Use DB facade for low stock items - limit to 5 items
            // Note: inventory table uses lot_code as primary key, not id
            $lowStockItems = \DB::table('inventory')
                ->where('qty_available', '<', 10)
                ->orderBy('qty_available')
                ->limit(5)
                ->get(['lot_code', 'item_code', 'qty_available', 'uom']);
            
            // Get available raw materials for production (recent receipt transactions)
            $availableRawMaterials = \App\Models\InventoryTransaction::where('category', 'RAW')
                ->where('txn_type', 'receipt')
                ->with(['grn.purchaseOrder.jobOrder'])
                ->orderBy('txn_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
            
            // Use DB facade for inventory queries - calculate once
            $inventoryByCategoryRaw = \DB::table('inventory')
                ->selectRaw('category, SUM(qty_available) as total_qty')
                ->groupBy('category')
                ->get();
            
            // Convert to collection and update RAW category
            $inventoryByCategory = collect($inventoryByCategoryRaw)->map(function($item) use ($balanceRawMaterialsQuantity) {
                if ($item->category === 'RAW') {
                    $item->total_qty = $balanceRawMaterialsQuantity;
                }
                return $item;
            });
            
            $inventoryByWarehouse = \DB::table('inventory')
                ->selectRaw('warehouse, SUM(qty_available) as total_qty')
                ->groupBy('warehouse')
                ->get();
            
            $viewData = [
                'inventoryByCategory' => $inventoryByCategory,
                'inventoryByWarehouse' => $inventoryByWarehouse,
                'lowStockItems' => $lowStockItems,
                'recentTransactions' => collect([]),
                'availableRawMaterials' => $availableRawMaterials,
                'workInProgressQuantity' => $workInProgressQuantity,
                'balanceRawMaterialsQuantity' => $balanceRawMaterialsQuantity,
                'totalRawMaterialsReceived' => $totalRawMaterialsReceived,
                'totalRawMaterialsConsumed' => $totalRawMaterialsConsumed,
                'consumablesQuantity' => $consumablesQuantity,
                'consumablesByType' => $consumablesByType,
            ];
            
            return view('livewire.inventory-dashboard', $viewData);
        } catch (\Exception $e) {
            \Log::error('Inventory Dashboard Render Error: ' . $e->getMessage() . ' | Trace: ' . $e->getTraceAsString());
            return view('livewire.inventory-dashboard', [
                'inventoryByCategory' => collect([]),
                'inventoryByWarehouse' => collect([]),
                'lowStockItems' => collect([]),
                'recentTransactions' => collect([]),
                'availableRawMaterials' => collect([]),
                'workInProgressQuantity' => 0,
                'balanceRawMaterialsQuantity' => 0,
                'totalRawMaterialsReceived' => 0,
                'totalRawMaterialsConsumed' => 0,
                'consumablesQuantity' => 0,
                'consumablesByType' => collect([]),
            ]);
        }
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
