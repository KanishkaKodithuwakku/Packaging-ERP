<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\SupplierReelSize;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // HJC Supplier
        $hjc = Supplier::create([
            'code' => 'HJC',
            'name' => 'HJC Packaging Ltd',
            'address' => '123 Industrial Zone, Colombo',
            'contact_person' => 'John Smith',
            'phone' => '+94 11 234 5678',
            'email' => 'orders@hjc.lk',
            'currency' => 'LKR',
            'display_format' => 'reel',
            'is_active' => true,
        ]);

        // HJC Reel Sizes
        $hjcReelSizes = [31, 33, 35, 37, 39, 41, 43, 45, 47, 49, 51, 53, 55, 57];
        foreach ($hjcReelSizes as $size) {
            SupplierReelSize::create([
                'supplier_id' => $hjc->id,
                'reel_size' => $size,
            ]);
        }

        // EXP Supplier
        $exp = Supplier::create([
            'code' => 'EXP',
            'name' => 'Export Packaging Solutions',
            'address' => '456 Export Zone, Gampaha',
            'contact_person' => 'Sarah Johnson',
            'phone' => '+94 33 345 6789',
            'email' => 'sales@exp.lk',
            'currency' => 'USD',
            'display_format' => 'dimensions',
            'is_active' => true,
        ]);

        // EXP Reel Sizes
        $expReelSizes = [35, 37, 39, 41, 43, 45, 47, 49, 51, 53, 55, 57, 59, 61, 63, 65, 67, 69, 71, 73];
        foreach ($expReelSizes as $size) {
            SupplierReelSize::create([
                'supplier_id' => $exp->id,
                'reel_size' => $size,
            ]);
        }

        // LCI Supplier
        $lci = Supplier::create([
            'code' => 'LCI',
            'name' => 'LCI Industries',
            'address' => '789 Manufacturing District, Kandy',
            'contact_person' => 'Michael Brown',
            'phone' => '+94 81 456 7890',
            'email' => 'info@lci.lk',
            'currency' => 'LKR',
            'display_format' => 'reel',
            'is_active' => true,
        ]);

        // LCI Reel Sizes (same as HJC)
        foreach ($hjcReelSizes as $size) {
            SupplierReelSize::create([
                'supplier_id' => $lci->id,
                'reel_size' => $size,
            ]);
        }

        // Additional suppliers
        $additionalSuppliers = [
            [
                'code' => 'PRE',
                'name' => 'Premium Packaging',
                'currency' => 'EUR',
                'display_format' => 'dimensions',
            ],
            [
                'code' => 'STD',
                'name' => 'Standard Supplies',
                'currency' => 'USD',
                'display_format' => 'reel',
            ],
        ];

        foreach ($additionalSuppliers as $supplierData) {
            $supplier = Supplier::create([
                'code' => $supplierData['code'],
                'name' => $supplierData['name'],
                'address' => 'Sample Address',
                'contact_person' => 'Contact Person',
                'phone' => '+94 11 000 0000',
                'email' => 'info@' . strtolower($supplierData['code']) . '.lk',
                'currency' => $supplierData['currency'],
                'display_format' => $supplierData['display_format'],
                'is_active' => true,
            ]);

            // Add default reel sizes
            $defaultReelSizes = [35, 37, 39, 41, 43, 45, 47, 49, 51, 53, 55, 57];
            foreach ($defaultReelSizes as $size) {
                SupplierReelSize::create([
                    'supplier_id' => $supplier->id,
                    'reel_size' => $size,
                ]);
            }
        }
    }
}