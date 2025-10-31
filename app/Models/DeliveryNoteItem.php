<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryNoteItem extends Model
{
    protected $fillable = [
        'delivery_note_id',
        'item_type',
        'item_id',
        'description',
        'material_code',
        'quantity',
        'dispatched_qty',
        'remaining_qty',
        'status',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'dispatched_qty' => 'decimal:4',
        'remaining_qty' => 'decimal:4',
    ];

    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class);
    }
}

