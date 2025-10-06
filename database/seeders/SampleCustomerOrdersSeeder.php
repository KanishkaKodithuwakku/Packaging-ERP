<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderItem;
use App\Services\OrderService;

class SampleCustomerOrdersSeeder extends Seeder
{
    public function run(): void
    {
        $orderService = app(OrderService::class);

        // Sample Customer Orders with Multiple Items
        $sampleOrders = [
            [
                'customer_name' => 'TechCorp Electronics',
                'order_no' => 'CO-001',
                'status' => 'confirmed',
                'notes' => 'Urgent delivery required for product launch',
                'items' => [
                    [
                        'item_description' => 'Electronics Shipping Box - Large',
                        'length_mm' => 600,
                        'width_mm' => 400,
                        'height_mm' => 300,
                        'ply' => 3,
                        'flute_type' => 'C',
                        'gsm_layers' => [300, 150, 250],
                        'qty_ordered' => 500,
                        'unit_price' => 2.50,
                        'notes' => 'Heavy-duty protection for electronics'
                    ],
                    [
                        'item_description' => 'Electronics Shipping Box - Medium',
                        'length_mm' => 400,
                        'width_mm' => 300,
                        'height_mm' => 200,
                        'ply' => 3,
                        'flute_type' => 'C',
                        'gsm_layers' => [280, 140, 220],
                        'qty_ordered' => 800,
                        'unit_price' => 1.80,
                        'notes' => 'Standard electronics packaging'
                    ]
                ]
            ],
            [
                'customer_name' => 'Fashion Forward Ltd',
                'order_no' => 'CO-002',
                'status' => 'in_production',
                'notes' => 'Premium retail packaging for luxury items',
                'items' => [
                    [
                        'item_description' => 'Luxury Handbag Display Box',
                        'length_mm' => 350,
                        'width_mm' => 250,
                        'height_mm' => 150,
                        'ply' => 3,
                        'flute_type' => 'E',
                        'gsm_layers' => [250, 120, 200],
                        'qty_ordered' => 200,
                        'unit_price' => 4.20,
                        'notes' => 'High-quality printing surface for luxury branding'
                    ],
                    [
                        'item_description' => 'Shoe Box - Premium',
                        'length_mm' => 320,
                        'width_mm' => 200,
                        'height_mm' => 100,
                        'ply' => 3,
                        'flute_type' => 'E',
                        'gsm_layers' => [220, 100, 180],
                        'qty_ordered' => 1000,
                        'unit_price' => 1.50,
                        'notes' => 'Smooth surface for premium shoe packaging'
                    ],
                    [
                        'item_description' => 'Jewelry Gift Box',
                        'length_mm' => 150,
                        'width_mm' => 100,
                        'height_mm' => 50,
                        'ply' => 3,
                        'flute_type' => 'F',
                        'gsm_layers' => [200, 80, 150],
                        'qty_ordered' => 500,
                        'unit_price' => 0.80,
                        'notes' => 'Micro flute for delicate jewelry items'
                    ]
                ]
            ],
            [
                'customer_name' => 'Fresh Foods Co',
                'order_no' => 'CO-003',
                'status' => 'pending',
                'notes' => 'Food-grade packaging with moisture barrier',
                'items' => [
                    [
                        'item_description' => 'Produce Shipping Box - Large',
                        'length_mm' => 500,
                        'width_mm' => 400,
                        'height_mm' => 300,
                        'ply' => 3,
                        'flute_type' => 'B',
                        'gsm_layers' => [200, 100, 180],
                        'qty_ordered' => 2000,
                        'unit_price' => 1.20,
                        'notes' => 'Food-safe packaging for fresh produce'
                    ],
                    [
                        'item_description' => 'Frozen Food Box',
                        'length_mm' => 400,
                        'width_mm' => 300,
                        'height_mm' => 200,
                        'ply' => 3,
                        'flute_type' => 'A',
                        'gsm_layers' => [250, 120, 200],
                        'qty_ordered' => 1500,
                        'unit_price' => 1.80,
                        'notes' => 'Insulated packaging for frozen goods'
                    ]
                ]
            ],
            [
                'customer_name' => 'Auto Parts Warehouse',
                'order_no' => 'CO-004',
                'status' => 'completed',
                'notes' => 'Heavy-duty packaging for automotive parts',
                'items' => [
                    [
                        'item_description' => 'Engine Parts Box - Extra Large',
                        'length_mm' => 800,
                        'width_mm' => 600,
                        'height_mm' => 400,
                        'ply' => 5,
                        'flute_type' => 'A',
                        'gsm_layers' => [350, 200, 300, 150, 250],
                        'qty_ordered' => 100,
                        'unit_price' => 8.50,
                        'notes' => 'Maximum strength for heavy engine components'
                    ],
                    [
                        'item_description' => 'Brake Pad Box',
                        'length_mm' => 200,
                        'width_mm' => 150,
                        'height_mm' => 100,
                        'ply' => 3,
                        'flute_type' => 'C',
                        'gsm_layers' => [280, 140, 220],
                        'qty_ordered' => 500,
                        'unit_price' => 0.95,
                        'notes' => 'Standard automotive parts packaging'
                    ],
                    [
                        'item_description' => 'Oil Filter Box',
                        'length_mm' => 120,
                        'width_mm' => 80,
                        'height_mm' => 80,
                        'ply' => 3,
                        'flute_type' => 'B',
                        'gsm_layers' => [200, 100, 180],
                        'qty_ordered' => 2000,
                        'unit_price' => 0.45,
                        'notes' => 'Small parts packaging with oil resistance'
                    ]
                ]
            ],
            [
                'customer_name' => 'Pharma Solutions',
                'order_no' => 'CO-005',
                'status' => 'delivered',
                'notes' => 'Pharmaceutical packaging with regulatory compliance',
                'items' => [
                    [
                        'item_description' => 'Medicine Bottle Box',
                        'length_mm' => 100,
                        'width_mm' => 80,
                        'height_mm' => 60,
                        'ply' => 3,
                        'flute_type' => 'F',
                        'gsm_layers' => [180, 80, 150],
                        'qty_ordered' => 10000,
                        'unit_price' => 0.25,
                        'notes' => 'Micro flute for small medicine bottles'
                    ],
                    [
                        'item_description' => 'Vaccine Shipping Box',
                        'length_mm' => 300,
                        'width_mm' => 200,
                        'height_mm' => 150,
                        'ply' => 3,
                        'flute_type' => 'C',
                        'gsm_layers' => [300, 150, 250],
                        'qty_ordered' => 500,
                        'unit_price' => 3.20,
                        'notes' => 'Temperature-controlled packaging for vaccines'
                    ],
                    [
                        'item_description' => 'Medical Device Box',
                        'length_mm' => 400,
                        'width_mm' => 300,
                        'height_mm' => 200,
                        'ply' => 3,
                        'flute_type' => 'B',
                        'gsm_layers' => [250, 120, 200],
                        'qty_ordered' => 300,
                        'unit_price' => 2.10,
                        'notes' => 'Sterile packaging for medical devices'
                    ]
                ]
            ]
        ];

        foreach ($sampleOrders as $orderData) {
            // Create or find customer
            $customer = Customer::firstOrCreate(
                ['name' => $orderData['customer_name']],
                [
                    'email' => strtolower(str_replace(' ', '.', $orderData['customer_name'])) . '@example.com',
                    'phone' => '+1-555-' . rand(100, 999) . '-' . rand(1000, 9999),
                    'address' => rand(100, 9999) . ' Business Street, City, State ' . rand(10000, 99999)
                ]
            );

            // Create customer order
            $customerOrder = CustomerOrder::create([
                'customer_id' => $customer->id,
                'order_no' => $orderData['order_no'],
                'status' => $orderData['status'],
                'notes' => $orderData['notes'],
            ]);

            // Create order items
            foreach ($orderData['items'] as $itemData) {
                CustomerOrderItem::create([
                    'customer_order_id' => $customerOrder->id,
                    'item_description' => $itemData['item_description'],
                    'length_mm' => $itemData['length_mm'],
                    'width_mm' => $itemData['width_mm'],
                    'height_mm' => $itemData['height_mm'],
                    'ply' => $itemData['ply'],
                    'flute_type' => $itemData['flute_type'],
                    'gsm_layers' => $itemData['gsm_layers'],
                    'qty_ordered' => $itemData['qty_ordered'],
                    'unit_price' => $itemData['unit_price'],
                    'notes' => $itemData['notes'],
                ]);
            }
        }
    }
}