<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Uom extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'status',
        'description',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get the global conversions where this UOM is the source
     */
    public function fromConversions(): HasMany
    {
        return $this->hasMany(UomConversion::class, 'from_uom_id');
    }

    /**
     * Get the global conversions where this UOM is the target
     */
    public function toConversions(): HasMany
    {
        return $this->hasMany(UomConversion::class, 'to_uom_id');
    }

    /**
     * Get the profile items where this UOM is the source
     */
    public function fromProfileItems(): HasMany
    {
        return $this->hasMany(UomConversionProfileItem::class, 'from_uom_id');
    }

    /**
     * Get the profile items where this UOM is the target
     */
    public function toProfileItems(): HasMany
    {
        return $this->hasMany(UomConversionProfileItem::class, 'to_uom_id');
    }

    /**
     * Get inventory items that use this UOM as base UOM
     */
    public function inventoryItems(): HasMany
    {
        return $this->hasMany(Inventory::class, 'base_uom_id');
    }

    /**
     * Scope for active UOMs
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get all possible conversions from this UOM
     */
    public function getAllConversions()
    {
        $conversions = collect();
        
        // Add global conversions
        $conversions = $conversions->merge($this->fromConversions()->with('toUom')->get());
        
        // Add bidirectional global conversions
        $bidirectional = $this->toConversions()->where('is_bidirectional', true)->with('fromUom')->get();
        $conversions = $conversions->merge($bidirectional);
        
        return $conversions;
    }
}