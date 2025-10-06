<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    ];

    protected $casts = [
        'qty_available' => 'decimal:2',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'lot_code', 'lot_code');
    }
}
