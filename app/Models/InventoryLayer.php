<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLayer extends Model
{
    protected $fillable = [
        'lot_code',
        'item_code',
        'category',
        'material_type',
        'qty_available',
        'unit_cost',
        'total_cost',
        'receipt_date',
        'warehouse',
        'source_doc_type',
        'source_doc_id',
    ];

    protected $casts = [
        'qty_available' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
        'receipt_date' => 'date',
    ];

    /**
     * Get the inventory that owns this layer
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'lot_code', 'lot_code');
    }

    /**
     * Check if layer is fully consumed
     */
    public function isFullyConsumed(): bool
    {
        return $this->qty_available <= 0;
    }

    /**
     * Get age of this layer in days
     */
    public function getAgeInDays(): int
    {
        return $this->receipt_date->diffInDays(now());
    }

    /**
     * Get aging bucket for this layer
     */
    public function getAgingBucket(): string
    {
        $days = $this->getAgeInDays();
        
        return match (true) {
            $days <= 30 => '0-30 Days',
            $days <= 60 => '31-60 Days',
            $days <= 90 => '61-90 Days',
            $days <= 180 => '91-180 Days',
            default => '180+ Days'
        };
    }
}
