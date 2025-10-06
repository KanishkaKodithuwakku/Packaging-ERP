<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UomConversionProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get the conversion items for this profile
     */
    public function conversionItems(): HasMany
    {
        return $this->hasMany(UomConversionProfileItem::class, 'profile_id');
    }

    /**
     * Get inventory items that use this profile
     */
    public function inventoryItems(): HasMany
    {
        return $this->hasMany(Inventory::class, 'conversion_profile_id');
    }

    /**
     * Scope for active profiles
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get all possible conversions from a specific UOM in this profile
     */
    public function getConversionsFrom($fromUomId)
    {
        return $this->conversionItems()
            ->where('from_uom_id', $fromUomId)
            ->with(['fromUom', 'toUom'])
            ->get();
    }

    /**
     * Get conversion factor between two UOMs in this profile
     */
    public function getConversionFactor($fromUomId, $toUomId)
    {
        $conversion = $this->conversionItems()
            ->where('from_uom_id', $fromUomId)
            ->where('to_uom_id', $toUomId)
            ->first();

        return $conversion ? $conversion->factor : null;
    }
}