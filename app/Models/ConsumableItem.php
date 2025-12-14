<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsumableItem extends Model
{
    protected $fillable = [
        'item_code',
        'item_name',
        'description',
        'material_type',
        'default_uom',
        'default_unit_cost',
        'default_warehouse',
        'min_stock_level',
        'max_stock_level',
        'reorder_point',
        'preferred_supplier_id',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'default_unit_cost' => 'decimal:4',
        'min_stock_level' => 'decimal:2',
        'max_stock_level' => 'decimal:2',
        'reorder_point' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the preferred supplier for this consumable item
     */
    public function preferredSupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'preferred_supplier_id');
    }

    /**
     * Get inventory records for this consumable item
     */
    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class, 'item_code', 'item_code');
    }

    /**
     * Get inventory transactions for this consumable item
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'item_code', 'item_code');
    }

    /**
     * Get current stock quantity
     */
    public function getCurrentStockAttribute(): float
    {
        return $this->inventory()->sum('qty_available') ?? 0;
    }

    /**
     * Check if stock is below reorder point
     */
    public function isBelowReorderPoint(): bool
    {
        return $this->current_stock <= $this->reorder_point;
    }

    /**
     * Check if stock is below minimum level
     */
    public function isBelowMinimumLevel(): bool
    {
        return $this->current_stock <= $this->min_stock_level;
    }

    /**
     * Scope to get only active items
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
