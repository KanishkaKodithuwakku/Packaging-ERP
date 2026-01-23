<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tax;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $taxes = [
            [
                'description' => 'VAT',
                'abbreviation' => 'VAT',
                'percentage' => 15.0000,
                'reverse_calculation' => 1.1500,
                'tax_label' => 'VAT',
                'status' => 1,
            ],
            [
                'description' => 'SSCL',
                'abbreviation' => 'SSCL',
                'percentage' => 2.5000,
                'reverse_calculation' => 1.0250,
                'tax_label' => 'SSCL',
                'status' => 1,
            ],
        ];
        
        foreach ($taxes as $taxData) {
            Tax::updateOrCreate(
                ['abbreviation' => $taxData['abbreviation']],
                $taxData
            );
        }
    }
}
