<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerOrder extends Model
{
    protected $fillable = [
        'customer_id',
        'quotation_id',
        'order_no',
        'status',
        'notes',
    ];

    protected $casts = [
        // No direct casts needed for the new structure
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function jobOrders(): HasMany
    {
        return $this->hasMany(JobOrder::class);
    }

    public function deliveryNotes(): HasMany
    {
        return $this->hasMany(DeliveryNote::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(CustomerOrderItem::class);
    }

    /**
     * Get the total quantity of all items in this order.
     */
    public function getTotalQuantityAttribute(): float
    {
        return $this->orderItems->sum('qty_ordered');
    }

    /**
     * Get the total value of all items in this order.
     */
    public function getTotalValueAttribute(): float
    {
        return $this->orderItems->sum('total_price');
    }
}
