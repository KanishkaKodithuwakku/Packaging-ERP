<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionOrder extends Model
{
    protected $fillable = [
        'production_order_number',
        'date',
        'job_order_id',
        'supplier_id',
        'status',
        'notes',
        'archived_at',
    ];

    protected $casts = [
        'date' => 'date',
        'archived_at' => 'datetime',
    ];

    /**
     * Get the job order that owns the production order.
     */
    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    /**
     * Get the supplier that owns the production order.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the items for the production order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(ProductionOrderItem::class);
    }

    /**
     * Get the GRNs for the production order.
     */
    public function grns(): HasMany
    {
        return $this->hasMany(GRN::class);
    }

    /**
     * Calculate total quantity
     */
    public function getTotalQuantity(): int
    {
        return $this->items()->sum('quantity');
    }

    /**
     * Calculate completed quantity
     */
    public function getCompletedQuantity(): int
    {
        return $this->items()->sum('completed_quantity');
    }

    /**
     * Generate next production order number
     */
    public static function generateProductionOrderNumber(): string
    {
        $lastPO = static::orderBy('id', 'desc')->first();
        $nextNumber = $lastPO ? $lastPO->id + 1 : 1;
        
        return 'PROD-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Check if production order is complete
     */
    public function isComplete(): bool
    {
        return $this->getTotalQuantity() === $this->getCompletedQuantity();
    }
}