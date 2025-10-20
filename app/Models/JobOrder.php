<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class JobOrder extends Model
{
    protected $table = 'job_orders';
    
    const STATUS_PENDING = 'pending';
    const STATUS_DRAFT = 'draft';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_IN_PRODUCTION = 'in_production';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    
    protected $fillable = [
        'job_number',
        'date',
        'supplier_id',
        'supplier_po_number',
        'customer_id',
        'customer_address',
        'purchase_order_no',
        'po_date',
        'notes',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'po_date' => 'date',
    ];

    /**
     * Get the supplier that owns the job order.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the customer that owns the job order.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the boxes for the job order.
     */
    public function boxes(): HasMany
    {
        return $this->hasMany(JobOrderBox::class, 'job_order_id');
    }

    /**
     * Get the dividers for the job order.
     */
    public function dividers(): HasMany
    {
        return $this->hasMany(JobOrderDivider::class, 'job_order_id');
    }

    /**
     * Get the purchase orders for the job order.
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'job_order_id');
    }

    /**
     * Get the production orders for the job order.
     */
    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class, 'job_order_id');
    }

    /**
     * Generate job number based on supplier
     */
    public static function generateJobNumber($supplierId)
    {
        $supplier = Supplier::find($supplierId);
        if (!$supplier) {
            return null;
        }

        $lastJob = self::where('supplier_id', $supplierId)
            ->orderBy('id', 'desc')
            ->first();

        \Log::info('Generating job number', [
            'supplier_id' => $supplierId,
            'supplier_code' => $supplier->code,
            'last_job' => $lastJob ? $lastJob->job_number : 'none'
        ]);

        $nextNumber = $lastJob ? (intval(substr($lastJob->job_number, strlen($supplier->code))) + 1) : 1;
        
        $newJobNumber = $supplier->code . $nextNumber;
        
        \Log::info('Generated job number', ['new_job_number' => $newJobNumber]);
        
        return $newJobNumber;
    }
}