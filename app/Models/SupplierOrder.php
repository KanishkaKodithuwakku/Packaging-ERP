<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierOrder extends Model
{
    protected $fillable = [
        'supplier_id',
        'po_no',
        'material_code',
        'gsm',
        'width_mm',
        'qty_kg',
        'status',
    ];

    protected $casts = [
        'qty_kg' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function grns(): HasMany
    {
        return $this->hasMany(GRN::class, 'supplier_po_id');
    }
}
