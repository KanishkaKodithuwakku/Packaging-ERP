<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

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
     * Note: This may be null if purchase order contains items from multiple job orders.
     * Use jobOrders() method to get all job orders from items.
     */
    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    /**
     * Get all unique job orders associated with this purchase order through items.
     * This method uses the job_order_id column directly from purchase_order_items.
     * 
     * @return Collection
     */
    public function jobOrders(): Collection
    {
        // Get unique job order IDs directly from purchase order items
        $jobOrderIds = $this->items()
            ->whereNotNull('job_order_id')
            ->pluck('job_order_id')
            ->unique()
            ->filter();
        
        // Return unique job orders
        return JobOrder::whereIn('id', $jobOrderIds)->get();
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