<?php

namespace App\Repositories;

use App\Models\Uom;
use App\Models\UomConversion;
use App\Models\UomConversionProfile;
use App\Models\UomConversionProfileItem;
use Illuminate\Database\Eloquent\Collection;

class UomRepository
{
    /**
     * Get all active UOMs
     */
    public function getActiveUoms(): Collection
    {
        return Uom::active()->orderBy('name')->get();
    }

    /**
     * Get UOMs by type
     */
    public function getUomsByType(string $type): Collection
    {
        return Uom::active()->ofType($type)->orderBy('name')->get();
    }

    /**
     * Get all UOM types
     */
    public function getUomTypes(): array
    {
        return ['weight', 'volume', 'count', 'length', 'area'];
    }

    /**
     * Create a new UOM
     */
    public function createUom(array $data): Uom
    {
        return Uom::create($data);
    }

    /**
     * Update UOM
     */
    public function updateUom(Uom $uom, array $data): bool
    {
        return $uom->update($data);
    }

    /**
     * Delete UOM (soft delete if needed)
     */
    public function deleteUom(Uom $uom): bool
    {
        // Check if UOM is being used in any conversions or inventory
        if ($this->isUomInUse($uom)) {
            // Instead of deleting, mark as inactive
            return $uom->update(['status' => 'inactive']);
        }
        
        return $uom->delete();
    }

    /**
     * Check if UOM is being used
     */
    public function isUomInUse(Uom $uom): bool
    {
        return $uom->fromConversions()->exists() ||
               $uom->toConversions()->exists() ||
               $uom->fromProfileItems()->exists() ||
               $uom->toProfileItems()->exists() ||
               $uom->inventoryItems()->exists();
    }

    /**
     * Get all global conversions
     */
    public function getGlobalConversions(): Collection
    {
        return UomConversion::with(['fromUom', 'toUom'])->get();
    }

    /**
     * Create global conversion
     */
    public function createGlobalConversion(array $data): UomConversion
    {
        $conversion = UomConversion::create($data);
        
        // Create reverse conversion if bidirectional
        if ($conversion->is_bidirectional) {
            $conversion->createReverseIfBidirectional();
        }
        
        return $conversion;
    }

    /**
     * Get all conversion profiles
     */
    public function getConversionProfiles(): Collection
    {
        return UomConversionProfile::with('conversionItems.fromUom', 'conversionItems.toUom')->get();
    }

    /**
     * Create conversion profile
     */
    public function createConversionProfile(array $data): UomConversionProfile
    {
        return UomConversionProfile::create($data);
    }

    /**
     * Add conversion item to profile
     */
    public function addConversionItemToProfile(int $profileId, array $data): UomConversionProfileItem
    {
        $data['profile_id'] = $profileId;
        return UomConversionProfileItem::create($data);
    }

    /**
     * Get conversion profile with items
     */
    public function getConversionProfileWithItems(int $profileId): ?UomConversionProfile
    {
        return UomConversionProfile::with(['conversionItems.fromUom', 'conversionItems.toUom'])
            ->find($profileId);
    }
}
