<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryNote extends Model
{
    protected $fillable = [
        'customer_order_id',
        'dn_no',
        'fg_code',
        'qty_delivered',
        'delivery_date',
    ];

    protected $casts = [
        'qty_delivered' => 'decimal:2',
        'delivery_date' => 'date',
    ];

    public function customerOrder(): BelongsTo
    {
        return $this->belongsTo(CustomerOrder::class);
    }
}
