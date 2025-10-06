<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Quotation;
use App\Models\Customer;
use App\Services\QuotationService;

class QuotationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $quotationService = app(QuotationService::class);

        // Create sample quotations
        $quotations = [
            [
                'customer_id' => Customer::first()->id,
                'qt_no' => 'QT-001',
                'item_desc' => 'Corrugated Box 500x400x300',
                'size_mm' => 500,
                'ply' => 3,
                'flute_type' => 'B',
                'gsm_layers' => [150, 120, 150],
                'qty_requested' => 1000,
                'profit_margin' => 25,
                'status' => 'accepted',
                'valid_until' => now()->addDays(30),
                'notes' => 'Standard corrugated box for electronics packaging',
            ],
            [
                'customer_id' => Customer::skip(1)->first()->id,
                'qt_no' => 'QT-002',
                'item_desc' => 'Heavy Duty Box 600x500x400',
                'size_mm' => 600,
                'ply' => 5,
                'flute_type' => 'C',
                'gsm_layers' => [180, 150, 120, 150, 180],
                'qty_requested' => 500,
                'profit_margin' => 30,
                'status' => 'rejected',
                'valid_until' => now()->subDays(5), // Expired
                'notes' => 'Heavy duty box for industrial packaging',
            ],
            [
                'customer_id' => Customer::first()->id,
                'qt_no' => 'QT-003',
                'item_desc' => 'Lightweight Box 300x200x150',
                'size_mm' => 300,
                'ply' => 3,
                'flute_type' => 'E',
                'gsm_layers' => [120, 100, 120],
                'qty_requested' => 2000,
                'profit_margin' => 20,
                'status' => 'sent',
                'valid_until' => now()->addDays(15),
                'notes' => 'Lightweight box for consumer goods',
            ],
        ];

        foreach ($quotations as $quotationData) {
            $quotation = $quotationService->createQuotation($quotationData);
            
            // If quotation is accepted, create a customer order
            if ($quotation->status === 'accepted') {
                $quotationService->acceptQuotation($quotation->id);
            }
        }
    }
}
