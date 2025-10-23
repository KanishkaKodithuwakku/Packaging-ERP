<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            [
                'name' => 'Sri Lankan Rupee',
                'code' => 'LKR',
                'symbol' => 'Rs',
                'symbol_position' => 'before',
                'decimal_places' => 2,
                'is_base_currency' => true,
                'is_active' => true,
            ],
            [
                'name' => 'United States Dollar',
                'code' => 'USD',
                'symbol' => '$',
                'symbol_position' => 'before',
                'decimal_places' => 2,
                'is_base_currency' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Euro',
                'code' => 'EUR',
                'symbol' => '€',
                'symbol_position' => 'before',
                'decimal_places' => 2,
                'is_base_currency' => false,
                'is_active' => true,
            ],
            [
                'name' => 'British Pound',
                'code' => 'GBP',
                'symbol' => '£',
                'symbol_position' => 'before',
                'decimal_places' => 2,
                'is_base_currency' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Indian Rupee',
                'code' => 'INR',
                'symbol' => '₹',
                'symbol_position' => 'before',
                'decimal_places' => 2,
                'is_base_currency' => false,
                'is_active' => true,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::create($currency);
        }
    }
}
