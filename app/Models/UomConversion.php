<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UomConversion extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_uom_id',
        'to_uom_id',
        'factor',
        'is_bidirectional',
        'notes',
    ];

    protected $casts = [
        'factor' => 'decimal:6',
        'is_bidirectional' => 'boolean',
    ];

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

    /**
     * Check if conversion exists in reverse direction
     */
    public function hasReverseConversion(): bool
    {
        return self::where('from_uom_id', $this->to_uom_id)
            ->where('to_uom_id', $this->from_uom_id)
            ->exists();
    }

    /**
     * Create reverse conversion if bidirectional
     */
    public function createReverseIfBidirectional(): void
    {
        if ($this->is_bidirectional && !$this->hasReverseConversion()) {
            self::create([
                'from_uom_id' => $this->to_uom_id,
                'to_uom_id' => $this->from_uom_id,
                'factor' => $this->reverse_factor,
                'is_bidirectional' => false, // Prevent infinite loop
                'notes' => "Auto-generated reverse of conversion {$this->id}",
            ]);
        }
    }
}