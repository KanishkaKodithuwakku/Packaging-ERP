<?php

namespace App\Services;

use App\Models\GRN;
use App\Models\GRNItem;
use App\Models\Inventory;
use App\Models\InventoryLayer;
use App\Models\InventoryTransaction;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use App\Models\ConsumableItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GRNProcessingService
{
    protected $inventoryService;
    protected $costingService;

    public function __construct(InventoryService $inventoryService, InventoryCostingService $costingService)
    {
        $this->inventoryService = $inventoryService;
        $this->costingService = $costingService;
    }

    /**
     * Process GRN to stock with partial quantities
     */
    public function processGRNToStockPartial(GRN $grn, array $partialQuantities, string $costingMethod = 'FIFO'): array
    {
        return DB::transaction(function () use ($grn, $partialQuantities, $costingMethod) {
            Log::info('Processing GRN to stock with partial quantities', [
                'grn_id' => $grn->id,
                'grn_no' => $grn->grn_no,
                'partial_quantities' => $partialQuantities,
                'costing_method' => $costingMethod
            ]);

            $processedItems = [];
            $totalValue = 0;

            foreach ($grn->items as $grnItem) {
                $quantityToProcess = $partialQuantities[$grnItem->id] ?? 0;
                
                if ($quantityToProcess <= 0) {
                    continue; // Skip items with zero quantity
                }

                $result = $this->processGRNItemToStockPartial($grnItem, $quantityToProcess, $costingMethod);
                $processedItems[] = $result;
                $totalValue += $result['total_cost'];
            }

            // Update GRN status based on processing result
            $allItemsFullyProcessed = $this->areAllItemsFullyProcessed($grn);
            $hasPending = $grn->getTotalPendingQuantity() > 0;
            
            if ($allItemsFullyProcessed && !$hasPending) {
                // All received items processed and no pending quantities
                $grn->update([
                    'status' => 'processed',
                    'processed_at' => now(),
                    'total_value' => $totalValue
                ]);
            } elseif (!$allItemsFullyProcessed || $hasPending) {
                // Some items processed but balance remains
                $grn->update([
                    'status' => 'partially_processed',
                    'total_value' => $totalValue
                ]);
            }

            Log::info('GRN partial processing completed', [
                'grn_id' => $grn->id,
                'items_processed' => count($processedItems),
                'total_value' => $totalValue,
                'all_fully_processed' => $allItemsFullyProcessed
            ]);

            return [
                'grn' => $grn,
                'processed_items' => $processedItems,
                'total_value' => $totalValue,
                'success' => true,
                'all_fully_processed' => $allItemsFullyProcessed
            ];
        });
    }

    /**
     * Process GRN to stock - Main entry point
     */
    public function processGRNToStock(GRN $grn, string $costingMethod = 'FIFO'): array
    {
        return DB::transaction(function () use ($grn, $costingMethod) {
            Log::info('Processing GRN to stock', [
                'grn_id' => $grn->id,
                'grn_no' => $grn->grn_no,
                'costing_method' => $costingMethod
            ]);

            $processedItems = [];
            $totalValue = 0;

            foreach ($grn->items as $grnItem) {
                $result = $this->processGRNItemToStock($grnItem, $costingMethod);
                $processedItems[] = $result;
                $totalValue += $result['total_cost'];
            }

            // Update GRN status
            $grn->update([
                'status' => 'processed',
                'processed_at' => now(),
                'total_value' => $totalValue
            ]);

            Log::info('GRN processed successfully', [
                'grn_id' => $grn->id,
                'items_processed' => count($processedItems),
                'total_value' => $totalValue
            ]);

            return [
                'grn' => $grn,
                'processed_items' => $processedItems,
                'total_value' => $totalValue,
                'success' => true
            ];
        });
    }

    /**
     * Process individual GRN item to stock with partial quantity
     */
    public function processGRNItemToStockPartial(GRNItem $grnItem, float $quantityToProcess, string $costingMethod = 'FIFO'): array
    {
        try {
            // Check if item is already fully processed
            $qtyReceived = $grnItem->qty_received_partial ?? $grnItem->qty_received ?? 0;
            $qtyProcessed = $grnItem->qty_processed ?? 0;
            $remaining = $qtyReceived - $qtyProcessed;
            
            if ($remaining <= 0) {
                throw new \Exception("This item has already been fully processed. Received: {$qtyReceived}, Processed: {$qtyProcessed}");
            }
            
            // Ensure we don't process more than available
            if ($quantityToProcess > $remaining) {
                throw new \Exception("Cannot process {$quantityToProcess} units. Only {$remaining} units remaining for processing.");
            }

            // Get required variables first
            $category = $this->determineItemCategory($grnItem);
            $materialType = $this->determineMaterialType($grnItem);
            $lotCode = $this->generateInventoryLotCode($grnItem);
            
            // If consumable, try to get defaults from item master
            $unitCost = $this->calculateUnitCost($grnItem);
            if ($materialType === 'consumable') {
                $consumableDefaults = $this->getConsumableDefaults($grnItem->material_code);
                if (!empty($consumableDefaults)) {
                    // Use consumable defaults if available, but allow override
                    $unitCost = $consumableDefaults['unit_cost'] ?? $unitCost;
                }
            }
            
            // For FG GRNs from production orders, quantityToProcess is already FG quantity (boxes)
            // We should NOT multiply by No of UPS again since qty_received is already in FG units
            $fgQuantityToProcess = $quantityToProcess;
            
            // Always create a new transaction for each processing batch
            // This ensures we have a complete audit trail
            $transaction = $this->inventoryService->recordTransaction([
                'lot_code' => $lotCode,
                'item_code' => $grnItem->material_code,
                'category' => $category,
                'material_type' => $materialType,
                'txn_type' => 'receipt',
                'qty' => $fgQuantityToProcess, // Use FG quantity for production GRNs
                'unit_cost' => $unitCost,
                'uom' => $grnItem->uom,
                'warehouse' => $this->getWarehouseForCategory($category),
                'related_doc_type' => 'GRN',
                'related_doc_id' => $grnItem->grn_id,
                'txn_date' => now()->toDateString(),
                'remarks' => "GRN Item: {$grnItem->description} (Batch: {$fgQuantityToProcess}, Total Processed: " . ($qtyProcessed + $fgQuantityToProcess) . ")",
            ], $costingMethod);

            // Update GRN item with partial processing details
            // For FG GRNs, qty_processed should be in FG quantity (boxes)
            $currentProcessed = $grnItem->qty_processed ?? 0;
            $newProcessed = $currentProcessed + $fgQuantityToProcess;
            $remaining = $grnItem->qty_received_partial - $newProcessed;
            
            $grnItem->update([
                'qty_processed' => $newProcessed, // Store FG quantity (boxes)
                'qty_remaining' => $remaining,
                'inventory_lot_code' => $lotCode,
                'unit_cost' => $unitCost,
                'total_cost' => $fgQuantityToProcess * $unitCost, // Use FG quantity for cost
                'processed_at' => now(),
            ]);

            return [
                'grn_item_id' => $grnItem->id,
                'material_code' => $grnItem->material_code,
                'qty_processed' => $fgQuantityToProcess, // Return FG quantity for display
                'qty_remaining' => $remaining,
                'unit_cost' => $unitCost,
                'total_cost' => $fgQuantityToProcess * $unitCost,
                'lot_code' => $lotCode,
                'category' => $category,
                'success' => true
            ];

        } catch (\Exception $e) {
            Log::error('Error processing GRN item to stock (partial)', [
                'grn_item_id' => $grnItem->id,
                'quantity_to_process' => $quantityToProcess,
                'error' => $e->getMessage()
            ]);
            
            return [
                'grn_item_id' => $grnItem->id,
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Process individual GRN item to stock
     */
    public function processGRNItemToStock(GRNItem $grnItem, string $costingMethod = 'FIFO'): array
    {
        try {
            // Check if item is already fully processed
            $qtyReceived = $grnItem->qty_received_partial ?? $grnItem->qty_received ?? 0;
            $qtyProcessed = $grnItem->qty_processed ?? 0;
            $remaining = $qtyReceived - $qtyProcessed;
            
            if ($remaining <= 0) {
                throw new \Exception("This item has already been fully processed. Received: {$qtyReceived}, Processed: {$qtyProcessed}");
            }
            
            // Check if transaction already exists for this GRN item (prevent duplicates)
            $existingTransaction = InventoryTransaction::where('related_doc_type', 'GRN')
                ->where('related_doc_id', $grnItem->grn_id)
                ->where('item_code', $grnItem->material_code)
                ->where('qty', $qtyReceived)
                ->first();
            
            if ($existingTransaction) {
                throw new \Exception("A transaction already exists for this item. This GRN item has already been processed.");
            }
            
            // Determine item category based on production order item
            $category = $this->determineItemCategory($grnItem);
            $materialType = $this->determineMaterialType($grnItem);
            
            // Generate inventory lot code
            $lotCode = $this->generateInventoryLotCode($grnItem);
            
            // Calculate unit cost (you may need to implement cost calculation logic)
            $unitCost = $this->calculateUnitCost($grnItem);
            
            // If consumable, try to get defaults from item master
            if ($materialType === 'consumable') {
                $consumableDefaults = $this->getConsumableDefaults($grnItem->material_code);
                if (!empty($consumableDefaults)) {
                    // Use consumable defaults if available, but allow override
                    $unitCost = $consumableDefaults['unit_cost'] ?? $unitCost;
                }
            }
            
            // Get the quantity to process (use partial received if available, otherwise use received)
            // For FG GRNs from production orders, qty_received already stores FG quantity (boxes), not board quantity
            // So we should NOT multiply by No of UPS again
            $fgQuantity = $grnItem->qty_received_partial ?? $grnItem->qty_received ?? 0;
            
            // Note: For FG GRNs, qty_received is already in finished goods (boxes) units
            // We no longer need to multiply by No of UPS since we fixed GRN creation to store FG quantity
            
            // Create inventory transaction - use FG quantity for production GRNs
            $transaction = $this->inventoryService->recordTransaction([
                'lot_code' => $lotCode,
                'item_code' => $grnItem->material_code,
                'category' => $category,
                'material_type' => $materialType,
                'txn_type' => 'receipt',
                'qty' => $fgQuantity, // Use FG quantity for production GRNs
                'unit_cost' => $unitCost,
                'uom' => $grnItem->uom,
                'warehouse' => $this->getWarehouseForCategory($category),
                'related_doc_type' => 'GRN',
                'related_doc_id' => $grnItem->grn_id,
                'txn_date' => now()->toDateString(),
                'remarks' => "GRN Item: {$grnItem->description}",
            ], $costingMethod);

            // Update GRN item with inventory details
            // For FG GRNs, qty_received is already FG quantity (boxes), so qty_processed should also be FG quantity
            $grnItem->update([
                'inventory_lot_code' => $lotCode,
                'unit_cost' => $unitCost,
                'total_cost' => $fgQuantity * $unitCost, // Use FG quantity for cost calculation
                'qty_processed' => $fgQuantity, // Store FG quantity (boxes) in qty_processed
                'qty_remaining' => 0,
                'processed_at' => now(),
            ]);

            return [
                'grn_item_id' => $grnItem->id,
                'material_code' => $grnItem->material_code,
                'qty_received' => $fgQuantity, // FG quantity (boxes)
                'qty_processed' => $fgQuantity, // FG quantity for display
                'unit_cost' => $unitCost,
                'total_cost' => $fgQuantity * $unitCost,
                'lot_code' => $lotCode,
                'category' => $category,
                'success' => true
            ];

        } catch (\Exception $e) {
            Log::error('Error processing GRN item to stock', [
                'grn_item_id' => $grnItem->id,
                'error' => $e->getMessage()
            ]);
            
            return [
                'grn_item_id' => $grnItem->id,
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Determine item category based on production order item
     */
    private function determineItemCategory(GRNItem $grnItem): string
    {
        // For production orders, items are typically finished goods
        if ($grnItem->grn->isFromProductionOrder()) {
            return 'FG'; // Finished Goods
        }
        
        // For supplier orders, items are typically raw materials
        return 'RAW'; // Raw Materials
    }

    /**
     * Determine material type (raw_material, consumable, component)
     */
    private function determineMaterialType(GRNItem $grnItem): string
    {
        // First check if it's in consumable_items table (preferred method)
        $consumableItem = ConsumableItem::where('item_code', $grnItem->material_code)
            ->where('is_active', true)
            ->first();
        
        if ($consumableItem) {
            return 'consumable';
        }
        
        // Fallback: Check if item is a consumable based on material code prefix
        if ($this->isConsumable($grnItem->material_code)) {
            return 'consumable';
        }
        
        // Default to raw_material
        return 'raw_material';
    }

    /**
     * Check if material code indicates a consumable item (fallback method)
     */
    private function isConsumable(string $materialCode): bool
    {
        if (empty($materialCode)) {
            return false;
        }

        $consumablePrefixes = [
            'GLUE', 'INK', 'PAPER', 'TAPE', 'LABEL', 
            'ADHESIVE', 'STAPLE', 'STRING', 'TWINE',
            'WRAP', 'FILM', 'SHRINK', 'BUBBLE'
        ];

        $upperCode = strtoupper($materialCode);
        
        foreach ($consumablePrefixes as $prefix) {
            if (str_starts_with($upperCode, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get consumable defaults from item master
     */
    private function getConsumableDefaults(string $itemCode): array
    {
        $consumable = ConsumableItem::where('item_code', $itemCode)
            ->where('is_active', true)
            ->first();
        
        if ($consumable) {
            return [
                'uom' => $consumable->default_uom,
                'unit_cost' => $consumable->default_unit_cost,
                'warehouse' => $consumable->default_warehouse,
            ];
        }
        
        return [];
    }

    /**
     * Generate inventory lot code for GRN item
     */
    private function generateInventoryLotCode(GRNItem $grnItem): string
    {
        $date = Carbon::now()->format('Ymd');
        $sequence = $grnItem->id;
        
        return "INV-{$grnItem->material_code}-{$date}-{$sequence}";
    }

    /**
     * Calculate unit cost for GRN item
     */
    private function calculateUnitCost(GRNItem $grnItem): float
    {
        // For now, use a default cost calculation
        // You can implement more sophisticated cost calculation logic here
        $baseCost = 10.00; // Base cost per unit
        
        // Adjust cost based on item type
        $multiplier = match($grnItem->item_type) {
            'box' => 1.0,
            'divider' => 0.8,
            'multi' => 1.2,
            default => 1.0
        };
        
        return $baseCost * $multiplier;
    }

    /**
     * Get warehouse for category
     */
    private function getWarehouseForCategory(string $category): string
    {
        return match($category) {
            'RAW' => 'MAIN',
            'WIP' => 'PRODUCTION',
            'FG' => 'FINISHED_GOODS',
            default => 'MAIN'
        };
    }


    /**
     * Get GRN processing status
     */
    public function getGRNProcessingStatus(GRN $grn): array
    {
        $totalItems = $grn->items->count();
        $processedItems = $grn->items->where('processed_at', '!=', null)->count();
        
        return [
            'total_items' => $totalItems,
            'processed_items' => $processedItems,
            'pending_items' => $totalItems - $processedItems,
            'is_fully_processed' => $processedItems === $totalItems,
            'processing_percentage' => $totalItems > 0 ? ($processedItems / $totalItems) * 100 : 0
        ];
    }

    /**
     * Check if all items are fully processed
     */
    private function areAllItemsFullyProcessed(GRN $grn): bool
    {
        foreach ($grn->items as $item) {
            $remaining = $item->qty_remaining ?? $item->qty_received;
            if ($remaining > 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get stock status for production order
     */
    public function getProductionOrderStockStatus(ProductionOrder $productionOrder): array
    {
        $stockStatus = [];
        
        foreach ($productionOrder->items as $item) {
            // Check inventory layers for the specific item
            $availableQty = InventoryLayer::where('item_code', $item->getItemCode())
                ->where('category', 'RAW')
                ->sum('qty_available');
            
            $requiredQty = $item->quantity;
            
            $stockStatus[] = [
                'item_id' => $item->id,
                'item_type' => $item->item_type,
                'required_qty' => $requiredQty,
                'available_qty' => $availableQty,
                'shortage' => max(0, $requiredQty - $availableQty),
                'is_available' => $availableQty >= $requiredQty
            ];
        }
        
        $allAvailable = collect($stockStatus)->every('is_available');
        
        return [
            'items' => $stockStatus,
            'all_items_available' => $allAvailable,
            'can_start_production' => $allAvailable
        ];
    }
}
