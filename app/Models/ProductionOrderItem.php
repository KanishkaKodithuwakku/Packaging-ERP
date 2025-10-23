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
}