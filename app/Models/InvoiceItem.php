<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'delivery_note_item_id',
        'item_type',
        'item_id',
        'description',
        'material_code',
        'quantity',
        'unit_price',
        'line_total',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function deliveryNoteItem(): BelongsTo
    {
        return $this->belongsTo(DeliveryNoteItem::class);
    }
}
