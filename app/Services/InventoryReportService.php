<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\InventoryLayer;
use App\Models\InventoryTransaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InventoryReportService
{
    /**
     * Get FIFO/LIFO aging report
     */
    public function getAgingReport(array $filters = []): array
    {
        $query = InventoryLayer::query();
        
        if (isset($filters['item_code'])) {
            $query->where('item_code', $filters['item_code']);
        }
        
        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        
        if (isset($filters['warehouse'])) {
            $query->where('warehouse', $filters['warehouse']);
        }
        
        if (isset($filters['date_from'])) {
            $query->where('receipt_date', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('receipt_date', '<=', $filters['date_to']);
        }

        $layers = $query->orderBy('receipt_date', 'asc')->get();
        
        $agingBuckets = [
            '0-30 Days' => ['qty' => 0, 'value' => 0],
            '31-60 Days' => ['qty' => 0, 'value' => 0],
            '61-90 Days' => ['qty' => 0, 'value' => 0],
            '91-180 Days' => ['qty' => 0, 'value' => 0],
            '180+ Days' => ['qty' => 0, 'value' => 0],
        ];

        foreach ($layers as $layer) {
            $bucket = $this->getAgingBucket($layer->receipt_date);
            $agingBuckets[$bucket]['qty'] += $layer->qty_available;
            $agingBuckets[$bucket]['value'] += $layer->total_cost;
        }

        return [
            'aging_buckets' => $agingBuckets,
            'total_qty' => $layers->sum('qty_available'),
            'total_value' => $layers->sum('total_cost'),
            'layers' => $layers,
        ];
    }

    /**
     * Get inventory movement report with FIFO/LIFO costing
     */
    public function getMovementReport(array $filters = []): array
    {
        $query = InventoryTransaction::query();
        
        if (isset($filters['item_code'])) {
            $query->where('item_code', $filters['item_code']);
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
        
        if (isset($filters['costing_method'])) {
            $query->where('costing_method', $filters['costing_method']);
        }
        
        if (isset($filters['date_from'])) {
            $query->where('txn_date', '>=', $filters['date_from']);
        }
        
        if (isset($filters['date_to'])) {
            $query->where('txn_date', '<=', $filters['date_to']);
        }

        $transactions = $query->orderBy('txn_date', 'desc')->get();
        
        // Group by transaction type
        $movementSummary = $transactions->groupBy('txn_type')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total_qty' => $group->sum('qty'),
                'total_cost' => $group->sum('total_cost'),
                'avg_unit_cost' => $group->avg('unit_cost'),
            ];
        });

        // Group by costing method
        $costingSummary = $transactions->groupBy('costing_method')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total_qty' => $group->sum('qty'),
                'total_cost' => $group->sum('total_cost'),
            ];
        });

        return [
            'transactions' => $transactions,
            'movement_summary' => $movementSummary,
            'costing_summary' => $costingSummary,
            'total_transactions' => $transactions->count(),
            'total_qty_moved' => $transactions->sum('qty'),
            'total_cost_moved' => $transactions->sum('total_cost'),
        ];
    }

    /**
     * Get inventory valuation by FIFO/LIFO
     */
    public function getInventoryValuation(array $filters = []): array
    {
        $query = InventoryLayer::query();
        
        if (isset($filters['item_code'])) {
            $query->where('item_code', $filters['item_code']);
        }
        
        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        
        if (isset($filters['warehouse'])) {
            $query->where('warehouse', $filters['warehouse']);
        }

        $layers = $query->get();
        
        $valuation = $layers->groupBy(['item_code', 'category', 'warehouse'])->map(function ($itemGroup) {
            return [
                'total_qty' => $itemGroup->sum('qty_available'),
                'total_cost' => $itemGroup->sum('total_cost'),
                'avg_unit_cost' => $itemGroup->avg('unit_cost'),
                'layers_count' => $itemGroup->count(),
                'oldest_layer' => $itemGroup->min('receipt_date'),
                'newest_layer' => $itemGroup->max('receipt_date'),
            ];
        });

        return [
            'valuation' => $valuation,
            'total_items' => $valuation->count(),
            'total_qty' => $layers->sum('qty_available'),
            'total_value' => $layers->sum('total_cost'),
        ];
    }

    /**
     * Get slow moving inventory (FIFO analysis)
     */
    public function getSlowMovingInventory(int $daysThreshold = 90): array
    {
        $cutoffDate = Carbon::now()->subDays($daysThreshold);
        
        $slowMoving = InventoryLayer::where('receipt_date', '<', $cutoffDate)
            ->where('qty_available', '>', 0)
            ->orderBy('receipt_date', 'asc')
            ->get()
            ->groupBy(['item_code', 'category', 'warehouse'])
            ->map(function ($itemGroup) {
                return [
                    'total_qty' => $itemGroup->sum('qty_available'),
                    'total_cost' => $itemGroup->sum('total_cost'),
                    'oldest_receipt' => $itemGroup->min('receipt_date'),
                    'days_aged' => Carbon::parse($itemGroup->min('receipt_date'))->diffInDays(now()),
                    'layers_count' => $itemGroup->count(),
                ];
            });

        return [
            'slow_moving_items' => $slowMoving,
            'total_items' => $slowMoving->count(),
            'total_qty' => $slowMoving->sum('total_qty'),
            'total_value' => $slowMoving->sum('total_cost'),
        ];
    }

    /**
     * Get FIFO vs LIFO cost comparison
     */
    public function getFifoVsLifoComparison(string $itemCode, string $category, string $warehouse, float $qty): array
    {
        $fifoCost = app(InventoryCostingService::class)->getCostForQuantity($itemCode, $category, $warehouse, $qty, 'FIFO');
        $lifoCost = app(InventoryCostingService::class)->getCostForQuantity($itemCode, $category, $warehouse, $qty, 'LIFO');
        
        return [
            'fifo_cost' => $fifoCost,
            'lifo_cost' => $lifoCost,
            'difference' => $lifoCost - $fifoCost,
            'difference_percentage' => $fifoCost > 0 ? (($lifoCost - $fifoCost) / $fifoCost) * 100 : 0,
        ];
    }

    /**
     * Get aging bucket for a date
     */
    private function getAgingBucket(string $receiptDate): string
    {
        $days = Carbon::parse($receiptDate)->diffInDays(now());
        
        return match (true) {
            $days <= 30 => '0-30 Days',
            $days <= 60 => '31-60 Days',
            $days <= 90 => '61-90 Days',
            $days <= 180 => '91-180 Days',
            default => '180+ Days'
        };
    }
}
