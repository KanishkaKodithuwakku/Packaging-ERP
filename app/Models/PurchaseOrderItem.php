<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'item_type',
        'item_id',
        'description',
        'reel_size',
        'cut_size',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'reel_size' => 'decimal:3',
        'cut_size' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the purchase order that owns the item.
     */
    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Get the related box or divider item
     */
    public function getItem()
    {
        if ($this->item_type === 'box') {
            return JobOrderBox::find($this->item_id);
        } elseif ($this->item_type === 'divider') {
            return JobOrderDivider::find($this->item_id);
        }
        
        return null;
    }
}