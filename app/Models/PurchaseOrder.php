<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_number',
        'date',
        'supplier_id',
        'job_order_id',
        'status',
        'notes',
        'cancellation_reason',
        'cancelled_at',
        'cancelled_by',
    ];

    protected $casts = [
        'date' => 'date',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Get the supplier that owns the purchase order.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the job order that owns the purchase order.
     */
    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    /**
     * Get the items for the purchase order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /**
     * Get the GRN for this purchase order.
     */
    public function grn(): HasMany
    {
        return $this->hasMany(GRN::class);
    }

    /**
     * Get the user who cancelled the purchase order.
     */
    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Calculate total amount
     */
    public function getTotalAmount(): float
    {
        return $this->items()->sum('total_price');
    }

    /**
     * Generate next PO number
     */
    public static function generatePONumber(): string
    {
        $lastPO = static::orderBy('id', 'desc')->first();
        $nextNumber = $lastPO ? $lastPO->id + 1 : 1;
        
        return 'PO-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}