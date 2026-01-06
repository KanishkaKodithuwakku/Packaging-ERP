<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class ProductionOrderItem extends Model
{
    protected $fillable = [
        'production_order_id',
        'item_type',
        'item_id',
        'quantity',
        'completed_quantity',
        'status',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'completed_quantity' => 'integer',
    ];

    /**
     * Get the production order that owns the item.
     */
    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    /**
     * Get the related box or divider item
     */
    public function getItem()
    {
        if (!$this->item_id) {
            return null;
        }

        try {
            if ($this->item_type === 'box') {
                return JobOrderBox::find($this->item_id);
            } elseif ($this->item_type === 'divider') {
                return JobOrderDivider::find($this->item_id);
            }
        } catch (\Exception $e) {
            Log::warning('Error getting item details', [
                'production_order_item_id' => $this->id,
                'item_type' => $this->item_type,
                'item_id' => $this->item_id,
                'error' => $e->getMessage()
            ]);
        }
        
        return null;
    }

    /**
     * Check if item is complete
     */
    public function isComplete(): bool
    {
        return $this->quantity === $this->completed_quantity;
    }

    /**
     * Get remaining quantity
     */
    public function getRemainingQuantity(): int
    {
        return $this->quantity - $this->completed_quantity;
    }

    /**
     * Get the material quantity (board_qty) from the job order
     */
    public function getMaterialQuantity(): float
    {
        $item = $this->getItem();
        if (!$item) {
            return 0;
        }

        if ($this->item_type === 'box' && $item instanceof JobOrderBox) {
            return (float) ($item->board_qty ?? 0);
        } elseif ($this->item_type === 'divider' && $item instanceof JobOrderDivider) {
            // For dividers, material quantity is the same as quantity
            return (float) $item->quantity;
        }

        return 0;
    }

    /**
     * Get the number of UPS (units per sheet) from the job order
     */
    public function getNoOfUps(): int
    {
        $item = $this->getItem();
        if (!$item) {
            return 1; // Default to 1 if not found
        }

        if ($this->item_type === 'box' && $item instanceof JobOrderBox) {
            return (int) ($item->no_of_ups ?? 1);
        }

        return 1; // Dividers typically don't have no_of_ups, default to 1
    }

    /**
     * Calculate expected finished goods quantity from material quantity
     * Formula: material quantity * no_of_ups
     */
    public function getExpectedFinishedGoodsFromMaterial(): float
    {
        $materialQty = $this->getMaterialQuantity();
        $noOfUps = $this->getNoOfUps();
        return $materialQty * $noOfUps;
    }

    /**
     * Get the job order's order quantity (expected finished goods quantity)
     * This is the maximum that can be completed
     */
    public function getJobOrderOrderQuantity(): int
    {
        $item = $this->getItem();
        if (!$item) {
            return $this->quantity; // Fallback to production order quantity
        }

        if ($this->item_type === 'box' && $item instanceof JobOrderBox) {
            return (int) $item->order_qty;
        } elseif ($this->item_type === 'divider' && $item instanceof JobOrderDivider) {
            return (int) $item->quantity;
        }

        return $this->quantity; // Fallback
    }

    /**
     * Get the effective maximum quantity for production
     * This is the minimum of:
     * 1. Expected finished goods from material (material_qty * no_of_ups)
     * 2. Production order item quantity (what user specified)
     * 3. Job order's order quantity (absolute maximum)
     */
    public function getEffectiveMaxQuantity(): int
    {
        $expectedFromMaterial = $this->getExpectedFinishedGoodsFromMaterial();
        $jobOrderOrderQty = $this->getJobOrderOrderQuantity();
        
        // Use the production order item quantity as the primary cap
        // This is what the user specified when creating the production order
        $productionOrderQty = $this->quantity;
        
        // Return the minimum of: material capacity, production order qty, and job order qty
        return (int) min($expectedFromMaterial, $productionOrderQty, $jobOrderOrderQty);
    }

    /**
     * Check if the item has reached the effective maximum quantity limit
     */
    public function hasReachedJobOrderLimit(): bool
    {
        $effectiveMaxQty = $this->getEffectiveMaxQuantity();
        return $this->completed_quantity >= $effectiveMaxQty;
    }

    /**
     * Get a stable material/item code used for FG stock lookups.
     */
    public function getItemCode(): string
    {
        $item = $this->getItem();
        if ($this->item_type === 'box' && $item) {
            $ply = $item->ply ?? 'N/A';
            return "BOX-{$item->id}-{$ply}PLY";
        }
        if ($this->item_type === 'divider' && $item) {
            $ply = $item->ply ?? 'N/A';
            return "DIV-{$item->id}-{$ply}PLY";
        }
        return "ITEM-{$this->item_id}";
    }
}