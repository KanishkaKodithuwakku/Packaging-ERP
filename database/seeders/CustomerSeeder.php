<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'code' => 'CUST001',
                'name' => 'ABC Electronics Ltd',
                'address' => '123 Main Street, Colombo 03',
                'contact_person' => 'David Wilson',
                'phone' => '+94 11 123 4567',
                'email' => 'orders@abc.lk',
                'currency' => 'LKR',
                'is_active' => true,
            ],
            [
                'code' => 'CUST002',
                'name' => 'XYZ Trading Company',
                'address' => '456 Commercial Road, Gampaha',
                'contact_person' => 'Lisa Anderson',
                'phone' => '+94 33 234 5678',
                'email' => 'purchasing@xyz.lk',
                'currency' => 'USD',
                'is_active' => true,
            ],
            [
                'code' => 'CUST003',
                'name' => 'Global Industries',
                'address' => '789 Industrial Zone, Kandy',
                'contact_person' => 'Robert Taylor',
                'phone' => '+94 81 345 6789',
                'email' => 'procurement@global.lk',
                'currency' => 'EUR',
                'is_active' => true,
            ],
            [
                'code' => 'CUST004',
                'name' => 'Premium Brands',
                'address' => '321 Business Park, Negombo',
                'contact_person' => 'Maria Garcia',
                'phone' => '+94 31 456 7890',
                'email' => 'orders@premium.lk',
                'currency' => 'LKR',
                'is_active' => true,
            ],
            [
                'code' => 'CUST005',
                'name' => 'Export Solutions',
                'address' => '654 Export Zone, Wattala',
                'contact_person' => 'James Lee',
                'phone' => '+94 11 567 8901',
                'email' => 'buying@export.lk',
                'currency' => 'USD',
                'is_active' => true,
            ],
        ];

        foreach ($customers as $customerData) {
            Customer::firstOrCreate(
                ['code' => $customerData['code']],
                $customerData
            );
        }
    }
}