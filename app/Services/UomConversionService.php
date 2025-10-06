<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Uom;
use App\Models\UomConversion;
use App\Models\UomConversionProfileItem;
use Exception;

class UomConversionService
{
    /**
     * Convert quantity from one UOM to another
     */
    public function convertToBaseUom(Inventory $item, float $qty, int $fromUomId): float
    {
        $baseUomId = $item->base_uom_id;

        if ($fromUomId == $baseUomId) {
            return $qty; // No conversion needed
        }

        // 1️⃣ Check item-specific conversion profile
        if ($item->conversion_profile_id) {
            $conversion = UomConversionProfileItem::where('profile_id', $item->conversion_profile_id)
                ->where('from_uom_id', $fromUomId)
                ->where('to_uom_id', $baseUomId)
                ->first();

            if ($conversion) {
                return $qty * $conversion->factor;
            }
        }

        // 2️⃣ Check global conversions
        $global = UomConversion::where('from_uom_id', $fromUomId)
            ->where('to_uom_id', $baseUomId)
            ->first();

        if ($global) {
            return $qty * $global->factor;
        }

        throw new Exception("No conversion rule found for UOM conversion from {$fromUomId} to {$baseUomId}.");
    }

    /**
     * Convert quantity from base UOM to target UOM
     */
    public function convertFromBaseUom(Inventory $item, float $qty, int $toUomId): float
    {
        $baseUomId = $item->base_uom_id;

        if ($toUomId == $baseUomId) {
            return $qty; // No conversion needed
        }

        // 1️⃣ Check item-specific conversion profile
        if ($item->conversion_profile_id) {
            $conversion = UomConversionProfileItem::where('profile_id', $item->conversion_profile_id)
                ->where('from_uom_id', $baseUomId)
                ->where('to_uom_id', $toUomId)
                ->first();

            if ($conversion) {
                return $qty * $conversion->factor;
            }
        }

        // 2️⃣ Check global conversions
        $global = UomConversion::where('from_uom_id', $baseUomId)
            ->where('to_uom_id', $toUomId)
            ->first();

        if ($global) {
            return $qty * $global->factor;
        }

        throw new Exception("No conversion rule found for UOM conversion from {$baseUomId} to {$toUomId}.");
    }

    /**
     * Get conversion factor between two UOMs
     */
    public function getConversionFactor(int $fromUomId, int $toUomId, ?int $profileId = null): float
    {
        if ($fromUomId == $toUomId) {
            return 1.0;
        }

        // 1️⃣ Check profile-specific conversions
        if ($profileId) {
            $profileConversion = UomConversionProfileItem::where('profile_id', $profileId)
                ->where('from_uom_id', $fromUomId)
                ->where('to_uom_id', $toUomId)
                ->first();

            if ($profileConversion) {
                return $profileConversion->factor;
            }
        }

        // 2️⃣ Check global conversions
        $global = UomConversion::where('from_uom_id', $fromUomId)
            ->where('to_uom_id', $toUomId)
            ->first();

        if ($global) {
            return $global->factor;
        }

        throw new Exception("No conversion rule found for UOM conversion from {$fromUomId} to {$toUomId}.");
    }

    /**
     * Get all possible conversions from a UOM
     */
    public function getPossibleConversions(int $fromUomId, ?int $profileId = null): array
    {
        $conversions = [];

        // 1️⃣ Check profile-specific conversions
        if ($profileId) {
            $profileConversions = UomConversionProfileItem::where('profile_id', $profileId)
                ->where('from_uom_id', $fromUomId)
                ->with('toUom')
                ->get();

            foreach ($profileConversions as $conversion) {
                $conversions[] = [
                    'to_uom_id' => $conversion->to_uom_id,
                    'to_uom_name' => $conversion->toUom->name,
                    'factor' => $conversion->factor,
                    'source' => 'profile'
                ];
            }
        }

        // 2️⃣ Check global conversions
        $globalConversions = UomConversion::where('from_uom_id', $fromUomId)
            ->with('toUom')
            ->get();

        foreach ($globalConversions as $conversion) {
            $conversions[] = [
                'to_uom_id' => $conversion->to_uom_id,
                'to_uom_name' => $conversion->toUom->name,
                'factor' => $conversion->factor,
                'source' => 'global'
            ];
        }

        return $conversions;
    }

    /**
     * Validate conversion chain
     */
    public function validateConversionChain(array $uomIds): bool
    {
        for ($i = 0; $i < count($uomIds) - 1; $i++) {
            try {
                $this->getConversionFactor($uomIds[$i], $uomIds[$i + 1]);
            } catch (Exception $e) {
                return false;
            }
        }
        return true;
    }

    /**
     * Convert through multiple UOMs
     */
    public function convertThroughChain(float $qty, array $uomIds): float
    {
        $currentQty = $qty;
        
        for ($i = 0; $i < count($uomIds) - 1; $i++) {
            $factor = $this->getConversionFactor($uomIds[$i], $uomIds[$i + 1]);
            $currentQty *= $factor;
        }
        
        return $currentQty;
    }
}
