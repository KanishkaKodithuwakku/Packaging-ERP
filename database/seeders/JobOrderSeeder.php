<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JobOrder;
use App\Models\JobOrderBox;
use App\Models\JobOrderDivider;
use App\Models\Supplier;
use App\Models\Customer;
use Carbon\Carbon;

class JobOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create suppliers
        $suppliers = Supplier::all();
        if ($suppliers->isEmpty()) {
            $this->command->warn('No suppliers found. Please run SupplierSeeder first.');
            return;
        }

        // Get or create customers
        $customers = Customer::all();
        if ($customers->isEmpty()) {
            $this->command->warn('No customers found. Please run CustomerSeeder first.');
            return;
        }

        // Sample job orders data
        $jobOrdersData = [
            [
                'supplier' => $suppliers->first(), // Use first supplier
                'customer' => $customers->first(), // Use first customer
                'job_number' => 'JO-001',
                'date' => Carbon::now()->subDays(10),
                'supplier_po_number' => 'PO-SUP-001',
                'customer_address' => '123 Main Street, Colombo',
                'purchase_order_no' => 'PO-001',
                'po_date' => Carbon::now()->subDays(8),
                'status' => 'confirmed',
                'notes' => 'Sample job order 1 - Standard packaging',
                'boxes' => [
                    [
                        'order_qty' => 5000,
                        'length' => 150.00,
                        'width' => 160.00,
                        'height' => 155.00,
                        'unit' => 'INCHES',
                        'dimension_type' => 'INTERNAL',
                        'ply' => '3',
                        'no_of_ups' => 2,
                        'reel_size' => 316.00,
                        'cut_size' => 622.50,
                        'board_qty' => 2500,
                        'supplier_price' => 230.00,
                        'selling_price' => 280.00,
                    ],
                ],
                'dividers' => [],
            ],
            [
                'supplier' => $suppliers->count() > 1 ? $suppliers->skip(1)->first() : $suppliers->first(),
                'customer' => $customers->count() > 1 ? $customers->skip(1)->first() : $customers->first(),
                'job_number' => 'JO-002',
                'date' => Carbon::now()->subDays(8),
                'supplier_po_number' => 'PO-SUP-002',
                'customer_address' => '456 Industrial Zone, Gampaha',
                'purchase_order_no' => 'PO-002',
                'po_date' => Carbon::now()->subDays(6),
                'status' => 'confirmed',
                'notes' => 'Sample job order 2 - Large boxes',
                'boxes' => [
                    [
                        'order_qty' => 3000,
                        'length' => 200.00,
                        'width' => 180.00,
                        'height' => 170.00,
                        'unit' => 'INCHES',
                        'dimension_type' => 'INTERNAL',
                        'ply' => '5',
                        'no_of_ups' => 1,
                        'reel_size' => 356.00,
                        'cut_size' => 760.00,
                        'board_qty' => 3000,
                        'supplier_price' => 350.00,
                        'selling_price' => 420.00,
                    ],
                ],
                'dividers' => [
                    [
                        'ply' => '3',
                        'quantity' => 1000,
                        'unit' => 'PCS',
                        'supplier_price' => 50.00,
                    ],
                ],
            ],
            [
                'supplier' => $suppliers->count() > 2 ? $suppliers->skip(2)->first() : $suppliers->first(),
                'customer' => $customers->count() > 2 ? $customers->skip(2)->first() : $customers->first(),
                'job_number' => 'JO-003',
                'date' => Carbon::now()->subDays(5),
                'supplier_po_number' => 'PO-SUP-003',
                'customer_address' => '789 Export Zone, Negombo',
                'purchase_order_no' => 'PO-003',
                'po_date' => Carbon::now()->subDays(3),
                'status' => 'draft',
                'notes' => 'Sample job order 3 - Medium boxes',
                'boxes' => [
                    [
                        'order_qty' => 8000,
                        'length' => 120.00,
                        'width' => 130.00,
                        'height' => 140.00,
                        'unit' => 'INCHES',
                        'dimension_type' => 'INTERNAL',
                        'ply' => '3',
                        'no_of_ups' => 2,
                        'reel_size' => 270.00,
                        'cut_size' => 500.00,
                        'board_qty' => 4000,
                        'supplier_price' => 180.00,
                        'selling_price' => 220.00,
                    ],
                ],
                'dividers' => [],
            ],
            [
                'supplier' => $suppliers->count() > 3 ? $suppliers->skip(3)->first() : $suppliers->first(),
                'customer' => $customers->count() > 3 ? $customers->skip(3)->first() : $customers->first(),
                'job_number' => 'JO-004',
                'date' => Carbon::now()->subDays(3),
                'supplier_po_number' => 'PO-SUP-004',
                'customer_address' => '321 Business Park, Kandy',
                'purchase_order_no' => 'PO-004',
                'po_date' => Carbon::now()->subDays(1),
                'status' => 'pending',
                'notes' => 'Sample job order 4 - Small boxes with dividers',
                'boxes' => [
                    [
                        'order_qty' => 10000,
                        'length' => 100.00,
                        'width' => 110.00,
                        'height' => 120.00,
                        'unit' => 'INCHES',
                        'dimension_type' => 'INTERNAL',
                        'ply' => '3',
                        'no_of_ups' => 3,
                        'reel_size' => 230.00,
                        'cut_size' => 420.00,
                        'board_qty' => 3334,
                        'supplier_price' => 150.00,
                        'selling_price' => 190.00,
                    ],
                ],
                'dividers' => [
                    [
                        'ply' => '5',
                        'quantity' => 2000,
                        'unit' => 'PCS',
                        'supplier_price' => 75.00,
                    ],
                ],
            ],
            [
                'supplier' => $suppliers->last(),
                'customer' => $customers->last(),
                'job_number' => 'JO-005',
                'date' => Carbon::now()->subDays(1),
                'supplier_po_number' => 'PO-SUP-005',
                'customer_address' => '555 Commercial Street, Kurunegala',
                'purchase_order_no' => 'PO-005',
                'po_date' => Carbon::now(),
                'status' => 'confirmed',
                'notes' => 'Sample job order 5 - Mixed order',
                'boxes' => [
                    [
                        'order_qty' => 6000,
                        'length' => 140.00,
                        'width' => 150.00,
                        'height' => 160.00,
                        'unit' => 'INCHES',
                        'dimension_type' => 'EXTERNAL',
                        'ply' => '5',
                        'no_of_ups' => 2,
                        'reel_size' => 310.00,
                        'cut_size' => 580.00,
                        'board_qty' => 3000,
                        'supplier_price' => 280.00,
                        'selling_price' => 340.00,
                    ],
                    [
                        'order_qty' => 4000,
                        'length' => 110.00,
                        'width' => 120.00,
                        'height' => 130.00,
                        'unit' => 'INCHES',
                        'dimension_type' => 'INTERNAL',
                        'ply' => '3',
                        'no_of_ups' => 2,
                        'reel_size' => 250.00,
                        'cut_size' => 460.00,
                        'board_qty' => 2000,
                        'supplier_price' => 200.00,
                        'selling_price' => 250.00,
                    ],
                ],
                'dividers' => [
                    [
                        'ply' => '3',
                        'quantity' => 1500,
                        'unit' => 'PCS',
                        'supplier_price' => 60.00,
                    ],
                ],
            ],
        ];

        foreach ($jobOrdersData as $jobOrderData) {
            // Create job order
            $jobOrder = JobOrder::create([
                'job_number' => $jobOrderData['job_number'],
                'date' => $jobOrderData['date'],
                'supplier_id' => $jobOrderData['supplier']->id,
                'supplier_po_number' => $jobOrderData['supplier_po_number'],
                'customer_id' => $jobOrderData['customer']->id,
                'customer_address' => $jobOrderData['customer_address'],
                'purchase_order_no' => $jobOrderData['purchase_order_no'],
                'po_date' => $jobOrderData['po_date'],
                'status' => $jobOrderData['status'],
                'notes' => $jobOrderData['notes'],
            ]);

            // Create boxes for this job order
            foreach ($jobOrderData['boxes'] as $boxData) {
                $boxData['job_order_id'] = $jobOrder->id;
                $boxData['supplier_id'] = $jobOrderData['supplier']->id;
                JobOrderBox::create($boxData);
            }

            // Create dividers for this job order
            foreach ($jobOrderData['dividers'] as $dividerData) {
                $dividerData['job_order_id'] = $jobOrder->id;
                $dividerData['supplier_id'] = $jobOrderData['supplier']->id;
                JobOrderDivider::create($dividerData);
            }

            $this->command->info("Created Job Order: {$jobOrder->job_number} with " . 
                count($jobOrderData['boxes']) . " box(es) and " . 
                count($jobOrderData['dividers']) . " divider(s)");
        }

        $this->command->info('Successfully created 5 job orders with boxes and dividers!');
    }
}




