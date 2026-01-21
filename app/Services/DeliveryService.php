<?php

namespace App\Services;

use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\Inventory;
use App\Models\InventoryLayer;
use App\Models\InventoryTransaction;
use App\Models\JobOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeliveryService
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Dispatch FG from inventory using FIFO
     */
    public function dispatchFg(DeliveryNote $deliveryNote, DeliveryNoteItem $item, float $quantity): array
    {
        return DB::transaction(function () use ($deliveryNote, $item, $quantity) {
            // Get available FG inventory for this material code
            $availableLayers = InventoryLayer::where('item_code', $item->material_code)
                ->where('category', 'FG')
                ->where('qty_available', '>', 0)
                ->orderBy('receipt_date', 'asc') // FIFO
                ->get();

            if ($availableLayers->sum('qty_available') < $quantity) {
                throw new \Exception("Insufficient FG inventory. Available: " . $availableLayers->sum('qty_available'));
            }

            $remainingQty = $quantity;
            $totalCost = 0;
            $consumedLayers = [];
            $lastLayer = null;

            // Consume from layers using FIFO
            foreach ($availableLayers as $layer) {
                if ($remainingQty <= 0) break;

                $consumeQty = min($remainingQty, $layer->qty_available);
                $layerCost = $consumeQty * $layer->unit_cost;

                // Reduce layer quantity
                $layer->qty_available -= $consumeQty;
                $layer->total_cost -= $layerCost;
                $layer->save();

                $consumedLayers[] = [
                    'layer_id' => $layer->id,
                    'qty' => $consumeQty,
                    'unit_cost' => $layer->unit_cost,
                    'total_cost' => $layerCost,
                ];

                $remainingQty -= $consumeQty;
                $totalCost += $layerCost;
                $lastLayer = $layer;

                // Delete empty layers
                if ($layer->qty_available <= 0) {
                    $layer->delete();
                }
            }

            // Create delivery transaction with delivery note item reference
            $transaction = InventoryTransaction::create([
                'lot_code' => $lastLayer ? $lastLayer->lot_code : 'DISPATCH-' . now()->format('YmdHis'),
                'item_code' => $item->material_code,
                'category' => 'FG',
                'txn_type' => 'delivery',
                'qty' => -$quantity,
                'unit_cost' => $quantity > 0 ? $totalCost / $quantity : 0,
                'total_cost' => -$totalCost,
                'uom' => 'PCS',
                'warehouse' => 'FINISHED_GOODS',
                'related_doc_type' => 'DeliveryNote',
                'related_doc_id' => $deliveryNote->id,
                'delivery_note_item_id' => $item->id, // Link to specific delivery note item
                'txn_date' => now()->toDateString(),
                'remarks' => "Delivery: {$item->description} (DN Item ID: {$item->id})",
                'costing_method' => 'FIFO',
            ]);

            // Update inventory balance
            $this->updateInventoryBalance($item->material_code, $quantity);

            return [
                'success' => true,
                'quantity' => $quantity,
                'total_cost' => $totalCost,
                'consumed_layers' => $consumedLayers,
                'transaction' => $transaction,
            ];
        });
    }

    /**
     * Update inventory balance after delivery
     */
    private function updateInventoryBalance(string $itemCode, float $qty): void
    {
        $inventory = Inventory::where('item_code', $itemCode)
            ->where('category', 'FG')
            ->first();

        if ($inventory) {
            $inventory->qty_available -= $qty;
            $inventory->last_movement_date = now()->toDateString();
            $inventory->save();
        }
    }

    /**
     * Get available FG for a job order
     */
    public function getAvailableFgForJobOrder(int $jobOrderId): array
    {
        // Get job order boxes and dividers
        $jobOrder = JobOrder::with(['boxes', 'dividers'])->findOrFail($jobOrderId);
        
        $availableFg = [];

        // Check FG for boxes
        foreach ($jobOrder->boxes as $box) {
            $materialCode = 'BOX-' . $box->id . '-' . $box->ply . 'PLY';
            $availableQty = $this->getAvailableFgQuantity($materialCode);
            
            if ($availableQty > 0) {
                $availableFg[] = [
                    'item_type' => 'box',
                    'item_id' => $box->id,
                    'material_code' => $materialCode,
                    'description' => "Box - {$box->length}x{$box->width}x{$box->height}cm",
                    'available_qty' => $availableQty,
                    'pending_qty' => $box['order_qty'],
                ];
            }
        }

        // Check FG for dividers - use correct material code pattern (DIV-, not DIVIDER-)
        foreach ($jobOrder->dividers as $divider) {
            $materialCode = 'DIV-' . $divider->id . '-' . $divider->ply . 'PLY';
            $availableQty = $this->getAvailableFgQuantity($materialCode);
            
            if ($availableQty > 0) {
                $availableFg[] = [
                    'item_type' => 'divider',
                    'item_id' => $divider->id,
                    'material_code' => $materialCode,
                    'description' => "Divider - {$divider->ply} PLY",
                    'available_qty' => $availableQty,
                    'pending_qty' => $divider['quantity'],
                ];
            }
        }

        return $availableFg;
    }

    /**
     * Get available FG quantity for a material code
     * This uses inventory layers as the source of truth.
     * Note: Inventory layers already reflect consumed quantities from delivery notes
     * because consume transactions are created immediately when delivery notes are created.
     */
    public function getAvailableFgQuantity(string $materialCode): float
    {
        // Get available quantity from inventory layers (already accounts for consumed quantities)
        $availableQty = InventoryLayer::where('item_code', $materialCode)
            ->where('category', 'FG')
            ->where('qty_available', '>', 0)
            ->sum('qty_available');
        
        return (float) max(0, $availableQty);
    }
}

