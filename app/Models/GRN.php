<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GRN extends Model
{
    protected $table = 'grns';
    
    protected $fillable = [
        'supplier_po_id',
        'production_order_id',
        'grn_no',
        'lot_code',
        'received_date',
        'notes',
        'status',
        'processed_at',
        'total_value',
    ];

    protected $casts = [
        'received_date' => 'date',
        'processed_at' => 'datetime',
        'total_value' => 'decimal:2',
    ];

    public function supplierOrder(): BelongsTo
    {
        return $this->belongsTo(SupplierOrder::class, 'supplier_po_id');
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'lot_code', 'lot_code');
    }

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    /**
     * Check if this GRN is from production order
     */
    public function isFromProductionOrder(): bool
    {
        return !is_null($this->production_order_id);
    }

    /**
     * Get the GRN items for this GRN
     */
    public function items(): HasMany
    {
        return $this->hasMany(GRNItem::class, 'grn_id');
    }

    /**
     * Get total quantity from items
     */
    public function getTotalQuantity(): float
    {
        return $this->items()->sum('qty_received');
    }

    /**
     * Get total items count
     */
    public function getItemsCount(): int
    {
        return $this->items()->count();
    }
}
