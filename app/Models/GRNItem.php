<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class GRNItem extends Model
{
    protected $table = 'grn_items';
    
    protected $fillable = [
        'grn_id',
        'production_order_item_id',
        'item_type',
        'item_id',
        'description',
        'material_code',
        'qty_received',
        'qty_processed',
        'qty_remaining',
        'uom',
        'inventory_lot_code',
        'unit_cost',
        'total_cost',
        'processed_at',
    ];

    protected $casts = [
        'qty_received' => 'decimal:4',
        'qty_processed' => 'decimal:4',
        'qty_remaining' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the GRN that owns the item.
     */
    public function grn(): BelongsTo
    {
        return $this->belongsTo(GRN::class, 'grn_id');
    }

    /**
     * Get the production order item that owns this GRN item.
     */
    public function productionOrderItem(): BelongsTo
    {
        return $this->belongsTo(ProductionOrderItem::class);
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
            Log::warning('Error getting GRN item details', [
                'grn_item_id' => $this->id,
                'item_type' => $this->item_type,
                'item_id' => $this->item_id,
                'error' => $e->getMessage()
            ]);
        }
        
        return null;
    }
}