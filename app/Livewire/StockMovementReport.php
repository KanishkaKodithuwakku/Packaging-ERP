<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\InventoryTransaction;
use App\Models\Inventory;
use App\Services\InventoryReportService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockMovementReport extends Component
{
    public $startDate;
    public $endDate;
    public $itemCode = '';
    public $category = '';
    public $warehouse = '';
    public $transactionType = '';
    
    public $movements = [];
    public $summary = [];
    public $totalPages = 0;
    public $currentPage = 1;
    public $perPage = 50;

    public function mount()
    {
        $this->startDate = now()->subDays(30)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->loadReport();
    }

    public function loadReport()
    {
        $query = InventoryTransaction::with(['inventory'])
            ->whereBetween('txn_date', [$this->startDate, $this->endDate]);

        // Apply filters
        if ($this->itemCode) {
            $query->where('item_code', 'like', '%' . $this->itemCode . '%');
        }

        if ($this->category) {
            $query->where('category', $this->category);
        }

        if ($this->warehouse) {
            $query->where('warehouse', $this->warehouse);
        }

        if ($this->transactionType) {
            $query->where('txn_type', $this->transactionType);
        }

        // Get total count for pagination
        $totalCount = $query->count();
        $this->totalPages = ceil($totalCount / $this->perPage);

        // Get paginated results
        $this->movements = $query->orderBy('txn_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->skip(($this->currentPage - 1) * $this->perPage)
            ->take($this->perPage)
            ->get();

        // Calculate summary
        $this->calculateSummary();
    }

    public function calculateSummary()
    {
        $summaryQuery = InventoryTransaction::whereBetween('txn_date', [$this->startDate, $this->endDate]);

        // Apply same filters
        if ($this->itemCode) {
            $summaryQuery->where('item_code', 'like', '%' . $this->itemCode . '%');
        }
        if ($this->category) {
            $summaryQuery->where('category', $this->category);
        }
        if ($this->warehouse) {
            $summaryQuery->where('warehouse', $this->warehouse);
        }
        if ($this->transactionType) {
            $summaryQuery->where('txn_type', $this->transactionType);
        }

        $this->summary = $summaryQuery->selectRaw('
            txn_type,
            COUNT(*) as transaction_count,
            SUM(qty) as total_quantity,
            SUM(qty * unit_cost) as total_value,
            AVG(unit_cost) as avg_unit_cost
        ')->groupBy('txn_type')->get()->keyBy('txn_type');
    }

    public function applyFilters()
    {
        $this->currentPage = 1;
        $this->loadReport();
    }

    public function resetFilters()
    {
        $this->itemCode = '';
        $this->category = '';
        $this->warehouse = '';
        $this->transactionType = '';
        $this->currentPage = 1;
        $this->loadReport();
    }

    public function goToPage($page)
    {
        if ($page >= 1 && $page <= $this->totalPages) {
            $this->currentPage = $page;
            $this->loadReport();
        }
    }

    public function exportToCsv()
    {
        // Get all data for export (without pagination)
        $query = InventoryTransaction::with(['inventory'])
            ->whereBetween('txn_date', [$this->startDate, $this->endDate]);

        if ($this->itemCode) {
            $query->where('item_code', 'like', '%' . $this->itemCode . '%');
        }
        if ($this->category) {
            $query->where('category', $this->category);
        }
        if ($this->warehouse) {
            $query->where('warehouse', $this->warehouse);
        }
        if ($this->transactionType) {
            $query->where('txn_type', $this->transactionType);
        }

        $data = $query->orderBy('txn_date', 'desc')->get();

        $filename = 'stock_movement_report_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'Date', 'Item Code', 'Description', 'Category', 'Warehouse', 
                'Transaction Type', 'Quantity', 'Unit Cost', 'Total Value', 
                'Lot Code', 'Reference Document', 'Remarks'
            ]);

            // CSV Data
            foreach ($data as $movement) {
                fputcsv($file, [
                    $movement->txn_date,
                    $movement->item_code,
                    $movement->inventory->description ?? '',
                    $movement->category,
                    $movement->warehouse,
                    $movement->txn_type,
                    $movement->qty,
                    $movement->unit_cost,
                    $movement->qty * $movement->unit_cost,
                    $movement->lot_code,
                    $movement->related_doc_type . ' - ' . $movement->related_doc_id,
                    $movement->remarks
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getCategories()
    {
        return InventoryTransaction::distinct()->pluck('category')->filter()->sort()->values();
    }

    public function getWarehouses()
    {
        return InventoryTransaction::distinct()->pluck('warehouse')->filter()->sort()->values();
    }

    public function getTransactionTypes()
    {
        return InventoryTransaction::distinct()->pluck('txn_type')->filter()->sort()->values();
    }

    public function render()
    {
        return view('livewire.stock-movement-report');
    }
}