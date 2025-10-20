<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierReelSize extends Model
{
    protected $fillable = [
        'supplier_id',
        'reel_size',
    ];

    protected $casts = [
        'reel_size' => 'decimal:2',
    ];

    /**
     * Get the supplier that owns the reel size.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}