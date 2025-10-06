<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_order_id',
        'item_description',
        'size_mm',
        'length_mm',
        'width_mm',
        'height_mm',
        'ply',
        'flute_type',
        'gsm_layers',
        'qty_ordered',
        'unit_price',
        'total_price',
        'notes',
    ];

    protected $casts = [
        'gsm_layers' => 'array',
        'qty_ordered' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Get the customer order that owns this item.
     */
    public function customerOrder(): BelongsTo
    {
        return $this->belongsTo(CustomerOrder::class);
    }

    /**
     * Calculate total price based on quantity and unit price.
     */
    public function calculateTotalPrice(): void
    {
        if ($this->unit_price && $this->qty_ordered) {
            $this->total_price = $this->unit_price * $this->qty_ordered;
        }
    }

    /**
     * Boot method to automatically calculate total price.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->calculateTotalPrice();
        });
    }
}