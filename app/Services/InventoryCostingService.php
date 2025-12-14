<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\InventoryLayer;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryCostingService
{
    /**
     * Process inventory receipt with FIFO/LIFO costing
     */
    public function processReceipt(array $data, string $costingMethod = 'FIFO'): InventoryTransaction
    {
        return DB::transaction(function () use ($data, $costingMethod) {
            // Create transaction record
            $transaction = InventoryTransaction::create([
                ...$data,
                'costing_method' => $costingMethod,
                'unit_cost' => $data['unit_cost'] ?? 0,
                'total_cost' => ($data['qty'] ?? 0) * ($data['unit_cost'] ?? 0),
                'remaining_qty' => $data['qty'] ?? 0,
                'remaining_cost' => ($data['qty'] ?? 0) * ($data['unit_cost'] ?? 0),
            ]);

            // Create inventory layer
            $this->createInventoryLayer($data, $costingMethod);

            // Update inventory balance
            $this->updateInventoryBalance($data);

            return $transaction;
        });
    }

    /**
     * Process inventory consumption with FIFO/LIFO costing
     */
    public function processConsumption(string $itemCode, string $category, string $warehouse, float $qty, string $costingMethod = 'FIFO'): array
    {
        return DB::transaction(function () use ($itemCode, $category, $warehouse, $qty, $costingMethod) {
            $layers = $this->getAvailableLayers($itemCode, $category, $warehouse, $costingMethod);
            $remainingQty = $qty;
            $totalCost = 0;
            $consumedLayers = [];
            $lotCodes = [];

            foreach ($layers as $layer) {
                if ($remainingQty <= 0) break;

                $consumeQty = min($remainingQty, $layer->qty_available);
                $layerCost = $consumeQty * $layer->unit_cost;
                
                // Update layer
                $layer->qty_available -= $consumeQty;
                $layer->total_cost -= $layerCost;
                $layer->save();

                $consumedLayers[] = [
                    'layer_id' => $layer->id,
                    'qty' => $consumeQty,
                    'unit_cost' => $layer->unit_cost,
                    'total_cost' => $layerCost,
                ];

                // Track lot codes for inventory table updates
                if (!in_array($layer->lot_code, $lotCodes)) {
                    $lotCodes[] = $layer->lot_code;
                }

                $remainingQty -= $consumeQty;
                $totalCost += $layerCost;

                // Delete layer if fully consumed
                if ($layer->qty_available <= 0) {
                    $layer->delete();
                }
            }

            if ($remainingQty > 0) {
                throw new \Exception("Insufficient inventory. Required: {$qty}, Available: " . ($qty - $remainingQty));
            }

            // Update inventory table for consumed lot codes
            // Need to recalculate from all remaining layers for this item/category/warehouse combination
            $this->syncInventoryBalanceForItem($itemCode, $category, $warehouse);

            return [
                'total_cost' => $totalCost,
                'average_cost' => $qty > 0 ? $totalCost / $qty : 0,
                'consumed_layers' => $consumedLayers,
            ];
        });
    }

    /**
     * Get available inventory layers for consumption
     */
    private function getAvailableLayers(string $itemCode, string $category, string $warehouse, string $costingMethod): \Illuminate\Database\Eloquent\Collection
    {
        $query = InventoryLayer::where('item_code', $itemCode)
            ->where('category', $category)
            ->where('warehouse', $warehouse)
            ->where('qty_available', '>', 0);

        return $costingMethod === 'FIFO' 
            ? $query->orderBy('receipt_date', 'asc')->orderBy('created_at', 'asc')->get()
            : $query->orderBy('receipt_date', 'desc')->orderBy('created_at', 'desc')->get();
    }

    /**
     * Create inventory layer for receipt
     */
    private function createInventoryLayer(array $data, string $costingMethod): InventoryLayer
    {
        return InventoryLayer::create([
            'lot_code' => $data['lot_code'],
            'item_code' => $data['item_code'],
            'category' => $data['category'],
            'material_type' => $data['material_type'] ?? 'raw_material',
            'qty_available' => $data['qty'],
            'unit_cost' => $data['unit_cost'] ?? 0,
            'total_cost' => ($data['qty'] ?? 0) * ($data['unit_cost'] ?? 0),
            'receipt_date' => $data['txn_date'] ?? now()->toDateString(),
            'warehouse' => $data['warehouse'],
            'source_doc_type' => $data['related_doc_type'],
            'source_doc_id' => $data['related_doc_id'],
        ]);
    }

    /**
     * Sync inventory balance after consumption for all lot codes of an item
     */
    private function syncInventoryBalanceForItem(string $itemCode, string $category, string $warehouse): void
    {
        // Get all remaining layers for this item/category/warehouse
        $remainingLayers = InventoryLayer::where('item_code', $itemCode)
            ->where('category', $category)
            ->where('warehouse', $warehouse)
            ->where('qty_available', '>', 0)
            ->get();
        
        // Group by lot_code
        $layersByLotCode = $remainingLayers->groupBy('lot_code');
        
        foreach ($layersByLotCode as $lotCode => $layers) {
            $totalQty = $layers->sum('qty_available');
            $totalCost = $layers->sum('total_cost');
            $firstLayer = $layers->first();
            
            $inventory = Inventory::where('lot_code', $lotCode)->first();
            
            if ($inventory) {
                $inventory->update([
                    'qty_available' => $totalQty,
                    'total_value' => $totalCost,
                    'unit_cost' => $totalQty > 0 ? $totalCost / $totalQty : 0,
                    'last_movement_date' => now()->toDateString(),
                ]);
                
                // Delete inventory record if fully consumed
                if ($totalQty <= 0) {
                    $inventory->delete();
                }
            } else if ($totalQty > 0) {
                // Create inventory record if it doesn't exist but we have remaining layers
                Inventory::create([
                    'lot_code' => $lotCode,
                    'item_code' => $itemCode,
                    'category' => $category,
                    'material_type' => $firstLayer->material_type ?? 'raw_material',
                    'qty_available' => $totalQty,
                    'unit_cost' => $totalQty > 0 ? $totalCost / $totalQty : 0,
                    'total_value' => $totalCost,
                    'uom' => 'PCS', // Default
                    'warehouse' => $warehouse,
                    'source' => $firstLayer->source_doc_type ?? '',
                    'ref_doc' => $firstLayer->source_doc_id ?? null,
                    'first_receipt_date' => $firstLayer->receipt_date ?? now()->toDateString(),
                    'last_movement_date' => now()->toDateString(),
                    'costing_method' => 'FIFO',
                ]);
            }
        }
        
        // Delete inventory records for lot codes that no longer have any layers
        $existingInventory = Inventory::where('item_code', $itemCode)
            ->where('category', $category)
            ->where('warehouse', $warehouse)
            ->get();
        
        foreach ($existingInventory as $inv) {
            $hasLayers = InventoryLayer::where('lot_code', $inv->lot_code)
                ->where('qty_available', '>', 0)
                ->exists();
            
            if (!$hasLayers) {
                $inv->delete();
            }
        }
    }

    /**
     * Update inventory balance with weighted average
     */
    private function updateInventoryBalance(array $data): void
    {
        $lotCode = $data['lot_code'];
        $qty = $data['qty'];
        $unitCost = $data['unit_cost'] ?? 0;

        $inventory = Inventory::where('lot_code', $lotCode)->first();
        
        if ($inventory) {
            // Calculate weighted average cost
            $currentValue = $inventory->total_value;
            $newValue = $qty * $unitCost;
            $totalQty = $inventory->qty_available + $qty;
            
            $newUnitCost = $totalQty > 0 ? ($currentValue + $newValue) / $totalQty : $unitCost;
            
            $inventory->update([
                'qty_available' => $totalQty,
                'unit_cost' => $newUnitCost,
                'total_value' => $currentValue + $newValue,
                'last_movement_date' => now()->toDateString(),
            ]);
        } else {
            // Create new inventory record
            Inventory::create([
                'lot_code' => $lotCode,
                'item_code' => $data['item_code'] ?? '',
                'category' => $data['category'] ?? 'RAW',
                'material_type' => $data['material_type'] ?? 'raw_material',
                'qty_available' => $qty,
                'unit_cost' => $unitCost,
                'total_value' => $qty * $unitCost,
                'uom' => $data['uom'] ?? 'PCS',
                'warehouse' => $data['warehouse'] ?? 'MAIN',
                'source' => $data['related_doc_type'] ?? '',
                'ref_doc' => $data['related_doc_id'] ?? '',
                'first_receipt_date' => now()->toDateString(),
                'last_movement_date' => now()->toDateString(),
                'costing_method' => 'FIFO',
            ]);
        }
    }

    /**
     * Get inventory valuation by costing method
     */
    public function getInventoryValuation(string $itemCode = null, string $category = null, string $warehouse = null): array
    {
        $query = InventoryLayer::query();
        
        if ($itemCode) $query->where('item_code', $itemCode);
        if ($category) $query->where('category', $category);
        if ($warehouse) $query->where('warehouse', $warehouse);
        
        return $query->selectRaw('
            item_code,
            category,
            warehouse,
            SUM(qty_available) as total_qty,
            SUM(total_cost) as total_value,
            AVG(unit_cost) as avg_unit_cost
        ')->groupBy('item_code', 'category', 'warehouse')->get()->toArray();
    }

    /**
     * Get FIFO/LIFO cost for specific quantity
     */
    public function getCostForQuantity(string $itemCode, string $category, string $warehouse, float $qty, string $costingMethod = 'FIFO'): float
    {
        $layers = $this->getAvailableLayers($itemCode, $category, $warehouse, $costingMethod);
        $remainingQty = $qty;
        $totalCost = 0;

        foreach ($layers as $layer) {
            if ($remainingQty <= 0) break;
            
            $consumeQty = min($remainingQty, $layer->qty_available);
            $totalCost += $consumeQty * $layer->unit_cost;
            $remainingQty -= $consumeQty;
        }

        return $totalCost;
    }

    /**
     * Recalculate inventory costs (useful for cost method changes)
     */
    public function recalculateInventoryCosts(string $lotCode): void
    {
        DB::transaction(function () use ($lotCode) {
            $layers = InventoryLayer::where('lot_code', $lotCode)->get();
            $totalQty = $layers->sum('qty_available');
            $totalCost = $layers->sum('total_cost');
            
            $inventory = Inventory::where('lot_code', $lotCode)->first();
            if ($inventory) {
                $inventory->update([
                    'qty_available' => $totalQty,
                    'total_value' => $totalCost,
                    'unit_cost' => $totalQty > 0 ? $totalCost / $totalQty : 0,
                ]);
            }
        });
    }
}
