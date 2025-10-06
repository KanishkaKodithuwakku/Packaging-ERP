<?php

namespace App\Services;

use App\Models\CustomerOrder;
use App\Models\JobOrder;
use App\Models\SupplierOrder;
use App\Models\GoodsReceipt;
use App\Models\DeliveryNote;
use App\Models\MaterialRequest;
use App\Services\InventoryService;
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Create customer order and generate job orders for each item
     */
    public function createCustomerOrder(array $data): CustomerOrder
    {
        return DB::transaction(function () use ($data) {
            // Create customer order (without item-specific fields)
            $orderData = [
                'customer_id' => $data['customer_id'],
                'order_no' => $data['order_no'],
                'status' => $data['status'] ?? 'pending',
                'notes' => $data['notes'] ?? null,
            ];
            
            $customerOrder = CustomerOrder::create($orderData);

            // Create job orders for each item (if items are provided)
            if (isset($data['orderItems']) && is_array($data['orderItems'])) {
                foreach ($data['orderItems'] as $item) {
                    $jobOrderData = [
                        'customer_order_id' => $customerOrder->id,
                        'jo_no' => $this->generateJobOrderNumber(),
                        'item_desc' => $item['item_description'],
                        'size_mm' => $item['size_mm'],
                        'ply' => $item['ply'],
                        'qty_to_make' => $item['qty_ordered'],
                        'status' => 'pending',
                    ];

                    JobOrder::create($jobOrderData);
                }
            }

            return $customerOrder;
        });
    }

    /**
     * Create supplier order
     */
    public function createSupplierOrder(array $data): SupplierOrder
    {
        return SupplierOrder::create($data);
    }

    /**
     * Process goods receipt and update inventory
     */
    public function processGoodsReceipt(array $data): GoodsReceipt
    {
        return DB::transaction(function () use ($data) {
            // Create goods receipt
            $goodsReceipt = GoodsReceipt::create($data);

            // Generate lot code
            $lotCode = $this->inventoryService->generateLotCode('GRN', $goodsReceipt->id);

            // Update goods receipt with lot code
            $goodsReceipt->update(['lot_code' => $lotCode]);

            // Record inventory transaction
            $this->inventoryService->recordTransaction([
                'lot_code' => $lotCode,
                'item_code' => $data['material_code'],
                'category' => 'RAW',
                'txn_type' => 'receipt',
                'qty' => $data['qty_received'],
                'uom' => $data['uom'],
                'warehouse' => 'MAIN',
                'related_doc_type' => 'GoodsReceipt',
                'related_doc_id' => $goodsReceipt->id,
                'txn_date' => $data['received_date'],
                'remarks' => 'Raw materials received from supplier',
            ]);

            // Update supplier order status
            $supplierOrder = SupplierOrder::find($data['supplier_po_id']);
            $supplierOrder->update(['status' => 'received']);

            return $goodsReceipt;
        });
    }

    /**
     * Create material request
     */
    public function createMaterialRequest(array $data): MaterialRequest
    {
        return MaterialRequest::create($data);
    }

    /**
     * Process material request and consume inventory
     */
    public function processMaterialRequest(int $materialRequestId): MaterialRequest
    {
        return DB::transaction(function () use ($materialRequestId) {
            $materialRequest = MaterialRequest::findOrFail($materialRequestId);
            
            // Find available raw materials
            $availableInventory = \App\Models\Inventory::where('item_code', $materialRequest->material_code)
                ->where('category', 'RAW')
                ->where('qty_available', '>', 0)
                ->orderBy('created_at')
                ->first();

            if (!$availableInventory) {
                throw new \Exception('Insufficient raw materials available');
            }

            // Consume materials
            $this->inventoryService->recordTransaction([
                'lot_code' => $availableInventory->lot_code,
                'item_code' => $materialRequest->material_code,
                'category' => 'RAW',
                'txn_type' => 'consume',
                'qty' => $materialRequest->qty_requested,
                'uom' => $materialRequest->uom,
                'warehouse' => 'MAIN',
                'related_doc_type' => 'MaterialRequest',
                'related_doc_id' => $materialRequest->id,
                'txn_date' => now()->toDateString(),
                'remarks' => 'Materials issued for production',
            ]);

            // Update material request status
            $materialRequest->update(['status' => 'issued']);

            return $materialRequest;
        });
    }

    /**
     * Complete job order and move WIP to FG
     */
    public function completeJobOrder(int $jobOrderId): JobOrder
    {
        return DB::transaction(function () use ($jobOrderId) {
            $jobOrder = JobOrder::findOrFail($jobOrderId);
            
            // Generate FG lot code
            $fgLotCode = $this->inventoryService->generateLotCode('FG', $jobOrderId);
            
            // Move WIP to FG
            $this->inventoryService->moveWipToFg(
                'WIP-' . $jobOrderId,
                $fgLotCode,
                $jobOrder->qty_to_make,
                $jobOrderId
            );

            // Update job order status
            $jobOrder->update(['status' => 'completed']);

            return $jobOrder;
        });
    }

    /**
     * Create delivery note and deliver FG
     */
    public function createDeliveryNote(array $data): DeliveryNote
    {
        return DB::transaction(function () use ($data) {
            // Create delivery note
            $deliveryNote = DeliveryNote::create($data);

            // Deliver FG
            $this->inventoryService->deliverFg(
                $data['fg_code'],
                $data['qty_delivered'],
                $data['customer_order_id']
            );

            // Update customer order status
            $customerOrder = CustomerOrder::find($data['customer_order_id']);
            $customerOrder->update(['status' => 'delivered']);

            return $deliveryNote;
        });
    }

    /**
     * Generate job order number
     */
    public function generateJobOrderNumber(): string
    {
        $count = JobOrder::count() + 1;
        return 'JO' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate supplier PO number
     */
    public function generateSupplierPONumber(): string
    {
        $count = SupplierOrder::count() + 1;
        return 'PO' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate GRN number
     */
    public function generateGRNNumber(): string
    {
        $count = GoodsReceipt::count() + 1;
        return 'GRN' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate delivery note number
     */
    public function generateDeliveryNoteNumber(): string
    {
        $count = DeliveryNote::count() + 1;
        return 'DN' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate material request number
     */
    public function generateMaterialRequestNumber(): string
    {
        $count = MaterialRequest::count() + 1;
        return 'MR' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generate customer order number
     */
    public function generateCustomerOrderNumber(): string
    {
        $count = CustomerOrder::count() + 1;
        return 'CO-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Process delivery note and deliver finished goods
     */
    public function processDeliveryNote(array $data): DeliveryNote
    {
        return DB::transaction(function () use ($data) {
            // Create delivery note
            $deliveryNote = DeliveryNote::create($data);

            // Find available finished goods
            $availableFG = \App\Models\Inventory::where('item_code', $data['fg_code'])
                ->where('category', 'FG')
                ->where('qty_available', '>=', $data['qty_delivered'])
                ->orderBy('created_at')
                ->first();

            if (!$availableFG) {
                throw new \Exception('Insufficient finished goods available');
            }

            // Record delivery transaction
            $this->inventoryService->recordTransaction([
                'lot_code' => $availableFG->lot_code,
                'item_code' => $data['fg_code'],
                'category' => 'FG',
                'txn_type' => 'delivery',
                'qty' => $data['qty_delivered'],
                'uom' => 'PCS',
                'warehouse' => 'FINISHED_GOODS',
                'related_doc_type' => 'DeliveryNote',
                'related_doc_id' => $deliveryNote->id,
                'txn_date' => $data['delivery_date'],
                'remarks' => 'Finished goods delivered to customer',
            ]);

            // Update customer order status
            $customerOrder = CustomerOrder::find($data['customer_order_id']);
            $customerOrder->update(['status' => 'delivered']);

            return $deliveryNote;
        });
    }
}
