<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'lot_code',
        'item_code',
        'category',
        'txn_type',
        'qty',
        'uom',
        'warehouse',
        'related_doc_type',
        'related_doc_id',
        'txn_date',
        'remarks',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'txn_date' => 'date',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'lot_code', 'lot_code');
    }
}
