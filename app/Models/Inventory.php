<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Inventory extends Model
{
    protected $table = 'inventory';
    protected $primaryKey = 'lot_code';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'lot_code',
        'item_code',
        'category',
        'qty_available',
        'uom',
        'warehouse',
        'source',
        'ref_doc',
        'base_uom_id',
        'conversion_profile_id',
    ];

    protected $casts = [
        'qty_available' => 'decimal:2',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'lot_code', 'lot_code');
    }

    /**
     * Get the base UOM for this inventory item
     */
    public function baseUom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'base_uom_id');
    }

    /**
     * Get the conversion profile for this inventory item
     */
    public function conversionProfile(): BelongsTo
    {
        return $this->belongsTo(UomConversionProfile::class, 'conversion_profile_id');
    }
}
