<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UomConversionProfileItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'from_uom_id',
        'to_uom_id',
        'factor',
        'notes',
    ];

    protected $casts = [
        'factor' => 'decimal:6',
    ];

    /**
     * Get the conversion profile
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(UomConversionProfile::class, 'profile_id');
    }

    /**
     * Get the source UOM
     */
    public function fromUom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'from_uom_id');
    }

    /**
     * Get the target UOM
     */
    public function toUom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'to_uom_id');
    }

    /**
     * Get the reverse conversion factor
     */
    public function getReverseFactorAttribute(): float
    {
        return 1 / $this->factor;
    }
}