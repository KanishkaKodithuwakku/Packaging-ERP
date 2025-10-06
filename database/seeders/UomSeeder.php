<?php

namespace Database\Seeders;

use App\Models\Uom;
use App\Models\UomConversion;
use App\Models\UomConversionProfile;
use App\Models\UomConversionProfileItem;
use Illuminate\Database\Seeder;

class UomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create basic UOMs
        $uoms = [
            // Weight UOMs
            ['code' => 'G', 'name' => 'Gram', 'type' => 'weight', 'status' => 'active'],
            ['code' => 'KG', 'name' => 'Kilogram', 'type' => 'weight', 'status' => 'active'],
            ['code' => 'LB', 'name' => 'Pound', 'type' => 'weight', 'status' => 'active'],
            ['code' => 'TON', 'name' => 'Metric Ton', 'type' => 'weight', 'status' => 'active'],
            
            // Volume UOMs
            ['code' => 'ML', 'name' => 'Milliliter', 'type' => 'volume', 'status' => 'active'],
            ['code' => 'L', 'name' => 'Liter', 'type' => 'volume', 'status' => 'active'],
            ['code' => 'BRL', 'name' => 'Barrel', 'type' => 'volume', 'status' => 'active'],
            ['code' => 'GAL', 'name' => 'Gallon', 'type' => 'volume', 'status' => 'active'],
            
            // Count UOMs
            ['code' => 'PCS', 'name' => 'Pieces', 'type' => 'count', 'status' => 'active'],
            ['code' => 'BOX', 'name' => 'Box', 'type' => 'count', 'status' => 'active'],
            ['code' => 'PKG', 'name' => 'Package', 'type' => 'count', 'status' => 'active'],
            ['code' => 'SET', 'name' => 'Set', 'type' => 'count', 'status' => 'active'],
            
            // Length UOMs
            ['code' => 'MM', 'name' => 'Millimeter', 'type' => 'length', 'status' => 'active'],
            ['code' => 'CM', 'name' => 'Centimeter', 'type' => 'length', 'status' => 'active'],
            ['code' => 'M', 'name' => 'Meter', 'type' => 'length', 'status' => 'active'],
            ['code' => 'FT', 'name' => 'Foot', 'type' => 'length', 'status' => 'active'],
            
            // Area UOMs
            ['code' => 'M2', 'name' => 'Square Meter', 'type' => 'area', 'status' => 'active'],
            ['code' => 'FT2', 'name' => 'Square Foot', 'type' => 'area', 'status' => 'active'],
        ];

        foreach ($uoms as $uomData) {
            Uom::create($uomData);
        }

        // Get UOM IDs for conversions
        $gram = Uom::where('code', 'G')->first();
        $kilogram = Uom::where('code', 'KG')->first();
        $pound = Uom::where('code', 'LB')->first();
        $milliliter = Uom::where('code', 'ML')->first();
        $liter = Uom::where('code', 'L')->first();
        $barrel = Uom::where('code', 'BRL')->first();
        $gallon = Uom::where('code', 'GAL')->first();
        $pieces = Uom::where('code', 'PCS')->first();
        $box = Uom::where('code', 'BOX')->first();
        $millimeter = Uom::where('code', 'MM')->first();
        $centimeter = Uom::where('code', 'CM')->first();
        $meter = Uom::where('code', 'M')->first();
        $foot = Uom::where('code', 'FT')->first();

        // Create global conversions
        $globalConversions = [
            // Weight conversions
            ['from_uom_id' => $gram->id, 'to_uom_id' => $kilogram->id, 'factor' => 0.001, 'is_bidirectional' => true, 'notes' => '1 gram = 0.001 kilogram'],
            ['from_uom_id' => $pound->id, 'to_uom_id' => $kilogram->id, 'factor' => 0.453592, 'is_bidirectional' => true, 'notes' => '1 pound = 0.453592 kilogram'],
            
            // Volume conversions
            ['from_uom_id' => $milliliter->id, 'to_uom_id' => $liter->id, 'factor' => 0.001, 'is_bidirectional' => true, 'notes' => '1 milliliter = 0.001 liter'],
            ['from_uom_id' => $barrel->id, 'to_uom_id' => $liter->id, 'factor' => 200, 'is_bidirectional' => true, 'notes' => '1 barrel = 200 liters'],
            ['from_uom_id' => $gallon->id, 'to_uom_id' => $liter->id, 'factor' => 3.78541, 'is_bidirectional' => true, 'notes' => '1 gallon = 3.78541 liters'],
            
            // Length conversions
            ['from_uom_id' => $millimeter->id, 'to_uom_id' => $centimeter->id, 'factor' => 0.1, 'is_bidirectional' => true, 'notes' => '1 millimeter = 0.1 centimeter'],
            ['from_uom_id' => $centimeter->id, 'to_uom_id' => $meter->id, 'factor' => 0.01, 'is_bidirectional' => true, 'notes' => '1 centimeter = 0.01 meter'],
            ['from_uom_id' => $foot->id, 'to_uom_id' => $meter->id, 'factor' => 0.3048, 'is_bidirectional' => true, 'notes' => '1 foot = 0.3048 meter'],
        ];

        foreach ($globalConversions as $conversionData) {
            UomConversion::create($conversionData);
        }

        // Create conversion profiles
        $glueProfile = UomConversionProfile::create([
            'name' => 'Glue Profile',
            'description' => 'Conversion profile for glue products with specific density conversions',
            'status' => 'active',
        ]);

        $paintProfile = UomConversionProfile::create([
            'name' => 'Paint Profile',
            'description' => 'Conversion profile for paint products with volume to weight conversions',
            'status' => 'active',
        ]);

        // Create profile-specific conversions
        $profileConversions = [
            // Glue Profile conversions
            ['profile_id' => $glueProfile->id, 'from_uom_id' => $liter->id, 'to_uom_id' => $kilogram->id, 'factor' => 0.8, 'notes' => 'Glue density: 1L = 0.8KG'],
            ['profile_id' => $glueProfile->id, 'from_uom_id' => $barrel->id, 'to_uom_id' => $kilogram->id, 'factor' => 160, 'notes' => '1 barrel = 200L = 160KG (glue)'],
            
            // Paint Profile conversions
            ['profile_id' => $paintProfile->id, 'from_uom_id' => $liter->id, 'to_uom_id' => $kilogram->id, 'factor' => 1.2, 'notes' => 'Paint density: 1L = 1.2KG'],
            ['profile_id' => $paintProfile->id, 'from_uom_id' => $gallon->id, 'to_uom_id' => $kilogram->id, 'factor' => 4.54, 'notes' => '1 gallon = 3.785L = 4.54KG (paint)'],
        ];

        foreach ($profileConversions as $conversionData) {
            UomConversionProfileItem::create($conversionData);
        }
    }
}