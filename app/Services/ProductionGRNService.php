<?php

namespace App\Services;

use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use App\Models\GRN;
use App\Models\JobOrderBox;
use App\Models\JobOrderDivider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductionGRNService
{
    /**
     * Generate GRN from production order item
     */
    public function generateGRNFromProductionItem(ProductionOrderItem $productionItem, int $quantity, string $lotCode = null): GRN
    {
        $productionOrder = $productionItem->productionOrder;
        $item = $productionItem->getItem();
        
        if (!$item) {
            throw new \Exception('Item not found for production order item');
        }

        // Generate GRN number
        $grnNumber = $this->generateGRNNumber();
        
        // Generate lot code if not provided
        if (!$lotCode) {
            $lotCode = $this->generateLotCode($productionOrder, $productionItem);
        }

        // Generate description
        $description = $this->generateDescription($productionItem, $item);

        // Create GRN
        $grn = GRN::create([
            'production_order_id' => $productionOrder->id,
            'grn_no' => $grnNumber,
            'lot_code' => $lotCode,
            'received_date' => now()->format('Y-m-d'),
            'notes' => $description,
        ]);

        // Create GRN item
        \App\Models\GRNItem::create([
            'grn_id' => $grn->id,
            'production_order_item_id' => $productionItem->id,
            'item_type' => $productionItem->item_type,
            'item_id' => $productionItem->item_id,
            'description' => $description,
            'material_code' => $this->generateMaterialCode($productionItem, $item),
            'qty_received' => $quantity,
            'uom' => 'PCS',
        ]);

        // Update production order item completed quantity
        $productionItem->increment('completed_quantity', $quantity);
        
            // Update production order item status
            if ($productionItem->getRemainingQuantity() <= 0) {
                $productionItem->update(['status' => 'completed']);
            } else {
                $productionItem->update(['status' => 'in_production']);
            }

        // Check if production order is complete
        $this->checkProductionOrderCompletion($productionOrder);

        Log::info('GRN generated from production order', [
            'grn_id' => $grn->id,
            'grn_no' => $grnNumber,
            'production_order_id' => $productionOrder->id,
            'production_item_id' => $productionItem->id,
            'quantity' => $quantity,
            'lot_code' => $lotCode
        ]);

        return $grn;
    }

    /**
     * Generate multiple GRNs for a production order item
     */
    public function generateMultipleGRNs(ProductionOrderItem $productionItem, array $quantities, array $lotCodes = []): array
    {
        $grns = [];
        $remainingQuantity = $productionItem->getRemainingQuantity();
        
        foreach ($quantities as $index => $quantity) {
            if ($remainingQuantity <= 0) {
                break;
            }
            
            $actualQuantity = min($quantity, $remainingQuantity);
            $lotCode = $lotCodes[$index] ?? null;
            
            $grn = $this->generateGRNFromProductionItem($productionItem, $actualQuantity, $lotCode);
            $grns[] = $grn;
            
            $remainingQuantity -= $actualQuantity;
        }

        return $grns;
    }

    /**
     * Generate a single GRN for multiple production items
     */
    public function generateMultiItemGRN(ProductionOrder $productionOrder, array $itemQuantities, string $lotCode = null): GRN
    {
        // Generate GRN number
        $grnNumber = $this->generateGRNNumber();
        
        // Generate lot code if not provided
        if (!$lotCode) {
            $lotCode = $this->generateMultiItemLotCode($productionOrder);
        }

        // Create the main GRN record
        $grn = GRN::create([
            'production_order_id' => $productionOrder->id,
            'grn_no' => $grnNumber,
            'lot_code' => $lotCode,
            'received_date' => now()->format('Y-m-d'),
            'notes' => $this->generateMultiItemDescription($itemQuantities),
        ]);

        Log::info('Main multi-item GRN created', [
            'grn_id' => $grn->id,
            'grn_no' => $grnNumber,
            'total_quantity' => array_sum($itemQuantities)
        ]);

        // Create GRN items for each production item
        Log::info('Starting GRN items creation', [
            'item_quantities' => $itemQuantities,
            'grn_id' => $grn->id
        ]);
        
        foreach ($itemQuantities as $itemId => $quantity) {
            Log::info('Processing item', [
                'item_id' => $itemId,
                'quantity' => $quantity
            ]);
            
            if ($quantity <= 0) {
                Log::info('Skipping item - quantity is 0 or negative', [
                    'item_id' => $itemId,
                    'quantity' => $quantity
                ]);
                continue;
            }
            
            try {
                $productionItem = $productionOrder->items()->findOrFail($itemId);
                Log::info('Found production item', [
                    'production_item_id' => $productionItem->id,
                    'item_type' => $productionItem->item_type,
                    'item_id' => $productionItem->item_id
                ]);
                
                // Check if quantity is available
                if ($quantity > $productionItem->getRemainingQuantity()) {
                    throw new \Exception("Quantity {$quantity} exceeds remaining quantity for item {$itemId}");
                }

                // Get item details safely
                $itemDetails = $productionItem->getItem();
                
                // Update production order item completed quantity
                $productionItem->increment('completed_quantity', $quantity);
                
                // Update production order item status
                if ($productionItem->getRemainingQuantity() <= 0) {
                    $productionItem->update(['status' => 'completed']);
                } else {
                    $productionItem->update(['status' => 'in_production']);
                }

                // Create GRN item record
                $grnItemData = [
                    'grn_id' => $grn->id,
                    'production_order_item_id' => $productionItem->id,
                    'item_type' => $productionItem->item_type,
                    'item_id' => $productionItem->item_id,
                    'description' => $this->generateDescription($productionItem, $itemDetails),
                    'material_code' => $this->generateMaterialCode($productionItem, $itemDetails),
                    'qty_received' => $quantity,
                    'uom' => 'PCS',
                ];
                
                Log::info('Creating GRN item with data', $grnItemData);
                
                $grnItem = \App\Models\GRNItem::create($grnItemData);
                
                Log::info('GRN item created successfully', [
                    'grn_item_id' => $grnItem->id,
                    'grn_id' => $grn->id
                ]);

                Log::info('GRN item created', [
                    'grn_id' => $grn->id,
                    'production_item_id' => $productionItem->id,
                    'item_type' => $productionItem->item_type,
                    'quantity' => $quantity,
                    'item_details_found' => !is_null($itemDetails)
                ]);

            } catch (\Exception $e) {
                Log::error('Error creating GRN item', [
                    'grn_id' => $grn->id,
                    'item_id' => $itemId,
                    'quantity' => $quantity,
                    'error' => $e->getMessage()
                ]);
                
                // Continue with other items even if one fails
                continue;
            }
        }

        // Check if production order is complete
        $this->checkProductionOrderCompletion($productionOrder);

        Log::info('Multi-item GRN generated', [
            'grn_id' => $grn->id,
            'grn_no' => $grnNumber,
            'production_order_id' => $productionOrder->id,
            'item_quantities' => $itemQuantities,
            'total_quantity' => array_sum($itemQuantities),
            'lot_code' => $lotCode
        ]);

        return $grn;
    }

    /**
     * Generate GRN number
     */
    private function generateGRNNumber(): string
    {
        $lastGRN = GRN::orderBy('id', 'desc')->first();
        $nextNumber = $lastGRN ? $lastGRN->id + 1 : 1;
        
        return 'GRN-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate lot code
     */
    private function generateLotCode(ProductionOrder $productionOrder, ProductionOrderItem $productionItem): string
    {
        $date = now()->format('Ymd');
        $productionNumber = str_replace('PROD-', '', $productionOrder->production_order_number);
        $itemType = strtoupper(substr($productionItem->item_type, 0, 1));
        
        return "LOT-{$date}-{$productionNumber}-{$itemType}";
    }

    /**
     * Generate material code
     */
    private function generateMaterialCode(ProductionOrderItem $productionItem, $item): string
    {
        if (!$item) {
            return "ITEM-{$productionItem->item_id}";
        }

        if ($productionItem->item_type === 'box') {
            $ply = $item->ply ?? 'N/A';
            return "BOX-{$item->id}-{$ply}PLY";
        } elseif ($productionItem->item_type === 'divider') {
            $ply = $item->ply ?? 'N/A';
            return "DIV-{$item->id}-{$ply}PLY";
        }
        
        return "ITEM-{$productionItem->item_id}";
    }

    /**
     * Generate description
     */
    private function generateDescription(ProductionOrderItem $productionItem, $item): string
    {
        if (!$item) {
            return ucfirst($productionItem->item_type) . " - Item ID: {$productionItem->item_id}";
        }

        if ($productionItem->item_type === 'box') {
            $dimensions = "{$item->length}x{$item->width}x{$item->height}";
            $unit = $item->unit ?? '';
            $dimensionType = $item->dimension_type ?? '';
            $ply = $item->ply ?? 'N/A';
            return "Box - {$dimensions} {$unit} {$dimensionType} - {$ply} PLY";
        } elseif ($productionItem->item_type === 'divider') {
            $ply = $item->ply ?? 'N/A';
            $quantity = $item->quantity ?? 'N/A';
            return "Divider - {$ply} PLY - Qty: {$quantity}";
        }
        
        return ucfirst($productionItem->item_type) . " - Item ID: {$productionItem->item_id}";
    }

    /**
     * Check if production order is complete and update status
     */
    private function checkProductionOrderCompletion(ProductionOrder $productionOrder): void
    {
        if ($productionOrder->isComplete()) {
            $productionOrder->update(['status' => 'completed']);
            
            Log::info('Production order completed', [
                'production_order_id' => $productionOrder->id,
                'production_order_number' => $productionOrder->production_order_number
            ]);
        }
    }

    /**
     * Get available quantities for GRN generation
     */
    public function getAvailableQuantities(ProductionOrder $productionOrder): array
    {
        $availableQuantities = [];
        
        foreach ($productionOrder->items as $item) {
            $remaining = $item->getRemainingQuantity();
            if ($remaining > 0) {
                $availableQuantities[] = [
                    'item' => $item,
                    'remaining_quantity' => $remaining,
                    'item_details' => $item->getItem()
                ];
            }
        }
        
        return $availableQuantities;
    }

    /**
     * Generate multi-item lot code
     */
    private function generateMultiItemLotCode(ProductionOrder $productionOrder): string
    {
        $date = now()->format('Ymd');
        $productionNumber = str_replace('PROD-', '', $productionOrder->production_order_number);
        
        return "LOT-{$date}-{$productionNumber}-MULTI";
    }

    /**
     * Generate multi-item description
     */
    private function generateMultiItemDescription(array $itemQuantities): string
    {
        $descriptions = [];
        foreach ($itemQuantities as $itemId => $quantity) {
            if ($quantity > 0) {
                $descriptions[] = "Item {$itemId}: {$quantity} pcs";
            }
        }
        
        return "Multi-item GRN - " . implode(', ', $descriptions);
    }

    /**
     * Generate multi-item material code
     */
    private function generateMultiItemMaterialCode(ProductionOrder $productionOrder): string
    {
        $productionNumber = str_replace('PROD-', '', $productionOrder->production_order_number);
        return "MULTI-{$productionNumber}";
    }
}
