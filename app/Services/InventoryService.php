<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Services\InventoryCostingService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryService
{
    protected $costingService;

    public function __construct(InventoryCostingService $costingService)
    {
        $this->costingService = $costingService;
    }
    /**
     * Generate a systematic lot code
     * Format: PO001-YYYYMMDD-01
     */
    public function generateLotCode(string $prefix = 'PO', int $sequence = 1): string
    {
        $date = Carbon::now()->format('Ymd');
        $sequenceFormatted = str_pad($sequence, 2, '0', STR_PAD_LEFT);
        
        return "{$prefix}{$sequence}-{$date}-{$sequenceFormatted}";
    }

    /**
     * Record inventory transaction and update inventory balance
     */
    public function recordTransaction(array $data, string $costingMethod = 'FIFO'): InventoryTransaction
    {
        return DB::transaction(function () use ($data, $costingMethod) {
            // Handle receipts with FIFO/LIFO costing
            if (in_array($data['txn_type'], ['receipt', 'produce'])) {
                return $this->costingService->processReceipt($data, $costingMethod);
            }
            
            // Handle consumption with FIFO/LIFO costing
            if (in_array($data['txn_type'], ['consume', 'delivery'])) {
                $costingResult = $this->costingService->processConsumption(
                    $data['item_code'],
                    $data['category'],
                    $data['warehouse'],
                    $data['qty'],
                    $costingMethod
                );
                
                // Create transaction record with calculated costs
                return InventoryTransaction::create([
                    ...$data,
                    'unit_cost' => $costingResult['average_cost'],
                    'total_cost' => $costingResult['total_cost'],
                    'costing_method' => $costingMethod,
                ]);
            }

            // Fallback to original method for other transaction types
            return $this->recordBasicTransaction($data);
        });
    }

    /**
     * Record basic transaction without FIFO/LIFO costing
     */
    private function recordBasicTransaction(array $data): InventoryTransaction
    {
        $transaction = InventoryTransaction::create($data);

        // Update or create inventory record
        $inventory = Inventory::where('lot_code', $data['lot_code'])->first();

        if ($inventory) {
            $newQty = $this->calculateNewQuantity(
                $inventory->qty_available,
                $data['qty'],
                $data['txn_type']
            );
            $inventory->update(['qty_available' => $newQty]);
        } else {
            Inventory::create([
                'lot_code' => $data['lot_code'],
                'item_code' => $data['item_code'],
                'category' => $data['category'],
                'qty_available' => $data['qty'],
                'uom' => $data['uom'],
                'warehouse' => $data['warehouse'],
                'source' => $data['related_doc_type'],
                'ref_doc' => $data['related_doc_id'],
            ]);
        }

        return $transaction;
    }

    /**
     * Calculate new quantity based on transaction type
     */
    private function calculateNewQuantity(float $currentQty, float $txnQty, string $txnType): float
    {
        return match ($txnType) {
            'receipt', 'produce' => $currentQty + $txnQty,
            'consume', 'delivery' => $currentQty - $txnQty,
            default => $currentQty,
        };
    }

    /**
     * Move stock from RAW to WIP
     */
    public function moveRawToWip(string $rawLotCode, string $wipLotCode, float $qty, string $jobOrderId): void
    {
        DB::transaction(function () use ($rawLotCode, $wipLotCode, $qty, $jobOrderId) {
            // Consume from RAW
            $this->recordTransaction([
                'lot_code' => $rawLotCode,
                'item_code' => 'RAW',
                'category' => 'RAW',
                'txn_type' => 'consume',
                'qty' => $qty,
                'uom' => 'KG',
                'warehouse' => 'MAIN',
                'related_doc_type' => 'JobOrder',
                'related_doc_id' => $jobOrderId,
                'txn_date' => now()->toDateString(),
                'remarks' => 'Material consumed for production',
            ]);

            // Produce WIP
            $this->recordTransaction([
                'lot_code' => $wipLotCode,
                'item_code' => 'WIP',
                'category' => 'WIP',
                'txn_type' => 'produce',
                'qty' => $qty,
                'uom' => 'PCS',
                'warehouse' => 'PRODUCTION',
                'related_doc_type' => 'JobOrder',
                'related_doc_id' => $jobOrderId,
                'txn_date' => now()->toDateString(),
                'remarks' => 'WIP produced from RAW materials',
            ]);
        });
    }

    /**
     * Move stock from WIP to FG
     */
    public function moveWipToFg(string $wipLotCode, string $fgLotCode, float $qty, string $jobOrderId): void
    {
        DB::transaction(function () use ($wipLotCode, $fgLotCode, $qty, $jobOrderId) {
            // Consume from WIP
            $this->recordTransaction([
                'lot_code' => $wipLotCode,
                'item_code' => 'WIP',
                'category' => 'WIP',
                'txn_type' => 'consume',
                'qty' => $qty,
                'uom' => 'PCS',
                'warehouse' => 'PRODUCTION',
                'related_doc_type' => 'JobOrder',
                'related_doc_id' => $jobOrderId,
                'txn_date' => now()->toDateString(),
                'remarks' => 'WIP consumed for FG production',
            ]);

            // Produce FG
            $this->recordTransaction([
                'lot_code' => $fgLotCode,
                'item_code' => 'FG',
                'category' => 'FG',
                'txn_type' => 'produce',
                'qty' => $qty,
                'uom' => 'PCS',
                'warehouse' => 'FINISHED_GOODS',
                'related_doc_type' => 'JobOrder',
                'related_doc_id' => $jobOrderId,
                'txn_date' => now()->toDateString(),
                'remarks' => 'FG produced from WIP',
            ]);
        });
    }

    /**
     * Deliver FG to customer
     */
    public function deliverFg(string $fgLotCode, float $qty, string $customerOrderId): void
    {
        $this->recordTransaction([
            'lot_code' => $fgLotCode,
            'item_code' => 'FG',
            'category' => 'FG',
            'txn_type' => 'delivery',
            'qty' => $qty,
            'uom' => 'PCS',
            'warehouse' => 'FINISHED_GOODS',
            'related_doc_type' => 'CustomerOrder',
            'related_doc_id' => $customerOrderId,
            'txn_date' => now()->toDateString(),
            'remarks' => 'FG delivered to customer',
        ]);
    }

    /**
     * Get inventory summary by category
     */
    public function getInventorySummary(): array
    {
        return Inventory::select('category', 'warehouse', DB::raw('SUM(qty_available) as total_qty'))
            ->groupBy('category', 'warehouse')
            ->get()
            ->toArray();
    }

    /**
     * Get inventory transactions with filters
     */
    public function getInventoryTransactions(array $filters = []): \Illuminate\Database\Eloquent\Collection
    {
        $query = InventoryTransaction::with('inventory');

        if (isset($filters['lot_code'])) {
            $query->where('lot_code', $filters['lot_code']);
        }

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['warehouse'])) {
            $query->where('warehouse', $filters['warehouse']);
        }

        if (isset($filters['txn_type'])) {
            $query->where('txn_type', $filters['txn_type']);
        }

        if (isset($filters['date_from'])) {
            $query->where('txn_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('txn_date', '<=', $filters['date_to']);
        }

        return $query->orderBy('txn_date', 'desc')->get();
    }
}
