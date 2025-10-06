<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\CustomerOrder;
use App\Models\JobOrder;
use App\Models\SupplierOrder;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Services\InventoryService;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample customers
        $customers = [
            ['name' => 'ABC Packaging Ltd', 'email' => 'orders@abcpkg.com', 'phone' => '+94-11-1234567', 'address' => '123 Main St, Colombo'],
            ['name' => 'XYZ Industries', 'email' => 'purchase@xyzind.com', 'phone' => '+94-11-2345678', 'address' => '456 Industrial Zone, Gampaha'],
            ['name' => 'DEF Manufacturing', 'email' => 'sales@defmfg.com', 'phone' => '+94-11-3456789', 'address' => '789 Export Zone, Negombo'],
        ];

        foreach ($customers as $customerData) {
            Customer::create($customerData);
        }

        // Create sample suppliers
        $suppliers = [
            ['name' => 'Paper Suppliers Co', 'email' => 'sales@papersuppliers.com', 'phone' => '+94-11-4567890', 'address' => '321 Paper Mill Rd, Kurunegala'],
            ['name' => 'Raw Materials Ltd', 'email' => 'orders@rawmaterials.com', 'phone' => '+94-11-5678901', 'address' => '654 Material St, Kandy'],
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::create($supplierData);
        }

        // Create sample customer orders
        $customerOrders = [
            [
                'customer_id' => 1,
                'order_no' => 'CO-001',
                'item_desc' => 'Corrugated Box 500x400x300',
                'size_mm' => 500,
                'ply' => 3,
                'qty_ordered' => 1000,
                'status' => 'confirmed',
            ],
            [
                'customer_id' => 2,
                'order_no' => 'CO-002',
                'item_desc' => 'Shipping Box 600x500x400',
                'size_mm' => 600,
                'ply' => 5,
                'qty_ordered' => 500,
                'status' => 'in_production',
            ],
        ];

        foreach ($customerOrders as $orderData) {
            CustomerOrder::create($orderData);
        }

        // Create sample job orders
        $jobOrders = [
            [
                'customer_order_id' => 1,
                'jo_no' => 'JO-001',
                'item_desc' => 'Corrugated Box 500x400x300',
                'size_mm' => 500,
                'ply' => 3,
                'qty_to_make' => 1000,
                'status' => 'in_progress',
            ],
            [
                'customer_order_id' => 2,
                'jo_no' => 'JO-002',
                'item_desc' => 'Shipping Box 600x500x400',
                'size_mm' => 600,
                'ply' => 5,
                'qty_to_make' => 500,
                'status' => 'pending',
            ],
        ];

        foreach ($jobOrders as $jobData) {
            JobOrder::create($jobData);
        }

        // Create sample supplier orders
        $supplierOrders = [
            [
                'supplier_id' => 1,
                'po_no' => 'PO-001',
                'material_code' => 'PAPER-001',
                'gsm' => 150,
                'width_mm' => 1000,
                'qty_kg' => 500,
                'status' => 'received',
            ],
            [
                'supplier_id' => 2,
                'po_no' => 'PO-002',
                'material_code' => 'PAPER-002',
                'gsm' => 200,
                'width_mm' => 1200,
                'qty_kg' => 300,
                'status' => 'ordered',
            ],
        ];

        foreach ($supplierOrders as $supplierOrderData) {
            SupplierOrder::create($supplierOrderData);
        }

        // Create sample inventory
        $inventoryService = app(InventoryService::class);
        
        $inventoryItems = [
            [
                'lot_code' => 'GRN001-20241003-01',
                'item_code' => 'PAPER-001',
                'category' => 'RAW',
                'qty_available' => 500,
                'uom' => 'KG',
                'warehouse' => 'MAIN',
                'source' => 'GoodsReceipt',
                'ref_doc' => '1',
            ],
            [
                'lot_code' => 'WIP-001',
                'item_code' => 'WIP-001',
                'category' => 'WIP',
                'qty_available' => 100,
                'uom' => 'PCS',
                'warehouse' => 'PRODUCTION',
                'source' => 'JobOrder',
                'ref_doc' => '1',
            ],
            [
                'lot_code' => 'FG001-20241003-01',
                'item_code' => 'FG-001',
                'category' => 'FG',
                'qty_available' => 50,
                'uom' => 'PCS',
                'warehouse' => 'FINISHED_GOODS',
                'source' => 'JobOrder',
                'ref_doc' => '1',
            ],
        ];

        foreach ($inventoryItems as $itemData) {
            Inventory::create($itemData);
        }

        // Create sample inventory transactions
        $transactions = [
            [
                'lot_code' => 'GRN001-20241003-01',
                'item_code' => 'PAPER-001',
                'category' => 'RAW',
                'txn_type' => 'receipt',
                'qty' => 500,
                'uom' => 'KG',
                'warehouse' => 'MAIN',
                'related_doc_type' => 'GoodsReceipt',
                'related_doc_id' => 1,
                'txn_date' => now()->subDays(5)->toDateString(),
                'remarks' => 'Raw materials received from supplier',
            ],
            [
                'lot_code' => 'WIP-001',
                'item_code' => 'WIP-001',
                'category' => 'WIP',
                'txn_type' => 'produce',
                'qty' => 100,
                'uom' => 'PCS',
                'warehouse' => 'PRODUCTION',
                'related_doc_type' => 'JobOrder',
                'related_doc_id' => 1,
                'txn_date' => now()->subDays(3)->toDateString(),
                'remarks' => 'WIP produced from RAW materials',
            ],
            [
                'lot_code' => 'FG001-20241003-01',
                'item_code' => 'FG-001',
                'category' => 'FG',
                'txn_type' => 'produce',
                'qty' => 50,
                'uom' => 'PCS',
                'warehouse' => 'FINISHED_GOODS',
                'related_doc_type' => 'JobOrder',
                'related_doc_id' => 1,
                'txn_date' => now()->subDays(1)->toDateString(),
                'remarks' => 'FG produced from WIP',
            ],
        ];

        foreach ($transactions as $transactionData) {
            InventoryTransaction::create($transactionData);
        }
    }
}
