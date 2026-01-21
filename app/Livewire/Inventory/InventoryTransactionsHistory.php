<?php

namespace App\Livewire\Inventory;

use App\Models\InventoryTransaction;
use App\Services\InventoryService;
use Livewire\Component;
use Livewire\WithPagination;

class InventoryTransactionsHistory extends Component
{
    use WithPagination;

    protected $layout = 'components.layouts.app';

    public $groupBy = ''; // Options: '', 'item', 'lot'

    public $filters = [
        'lot_code' => '',
        'category' => '',
        'warehouse' => '',
        'txn_type' => '',
        'date_from' => '',
        'date_to' => '',
    ];

    public function updatedGroupBy()
    {
        // Reset pagination when changing grouping
        $this->resetPage();
    }

    public function mount()
    {
        $this->filters['date_from'] = now()->subDays(30)->format('Y-m-d');
        $this->filters['date_to'] = now()->format('Y-m-d');
    }

    public function updatedFilters()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->filters = [
            'lot_code' => '',
            'category' => '',
            'warehouse' => '',
            'txn_type' => '',
            'date_from' => now()->subDays(30)->format('Y-m-d'),
            'date_to' => now()->format('Y-m-d'),
        ];
        $this->resetPage();
    }

    public function getTransactions()
    {
        $inventoryService = app(InventoryService::class);
        return $inventoryService->getInventoryTransactions($this->filters);
    }

    public function getTransactionSummary()
    {
        $query = InventoryTransaction::query();

        if ($this->filters['lot_code']) {
            $query->where('lot_code', 'like', '%' . $this->filters['lot_code'] . '%');
        }

        if ($this->filters['category']) {
            $query->where('category', $this->filters['category']);
        }

        if ($this->filters['warehouse']) {
            $query->where('warehouse', $this->filters['warehouse']);
        }

        if ($this->filters['txn_type']) {
            $query->where('txn_type', $this->filters['txn_type']);
        }

        if ($this->filters['date_from']) {
            $query->where('txn_date', '>=', $this->filters['date_from']);
        }

        if ($this->filters['date_to']) {
            $query->where('txn_date', '<=', $this->filters['date_to']);
        }

        // First, get all transactions to calculate balances
        // Important: process receipts/produce BEFORE consume/delivery for the SAME day
        // to avoid temporarily negative balances showing as 0.
        $allTransactions = $query->with(['inventory', 'grn.purchaseOrder.items.jobOrder', 'grn.productionOrder.jobOrder'])
            ->orderBy('txn_date', 'asc')
            ->orderByRaw("CASE WHEN txn_type IN ('receipt','produce') THEN 0 ELSE 1 END") // receipts first on same date
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Calculate running balances per lot code in chronological order
        $balances = $this->calculateRunningBalances($allTransactions);

        // Now sort for display (descending by date)
        $transactions = $allTransactions->sortByDesc(function($transaction) {
            return $transaction->txn_date->format('Y-m-d') . ' ' . $transaction->created_at->format('H:i:s');
        })->values();

        // Attach balances to transactions
        foreach ($transactions as $transaction) {
            $key = $transaction->lot_code . '_' . $transaction->id;
            $transaction->balance_qty = $balances[$key] ?? 0;
        }

        // Create paginator manually since we've sorted
        $currentPage = request()->get('page', 1);
        $perPage = 20;
        $items = $transactions->slice(($currentPage - 1) * $perPage, $perPage)->values();
        
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $transactions->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * Calculate running balances for transactions per lot code
     * Returns array with key: lot_code_id and value: balance after that transaction
     */
    protected function calculateRunningBalances($transactions)
    {
        $balances = [];
        $lotBalances = []; // Track current balance per lot code

        // Process transactions in chronological order
        foreach ($transactions as $transaction) {
            $lotCode = $transaction->lot_code;
            
            // Initialize balance for this lot if not exists
            if (!isset($lotBalances[$lotCode])) {
                $lotBalances[$lotCode] = 0;
            }

            // Calculate transaction impact
            $qty = (float) $transaction->qty;
            if (in_array($transaction->txn_type, ['receipt', 'produce'])) {
                $lotBalances[$lotCode] += $qty;
            } elseif (in_array($transaction->txn_type, ['consume', 'delivery'])) {
                $lotBalances[$lotCode] -= $qty;
            }

            // Store balance for this transaction (balance AFTER this transaction)
            $key = $lotCode . '_' . $transaction->id;
            $balances[$key] = max(0, $lotBalances[$lotCode]); // Ensure non-negative
        }

        return $balances;
    }

    public function render()
    {
        // If grouping is enabled, get all transactions (no pagination)
        // Otherwise use paginated results
        if ($this->groupBy) {
            $transactions = $this->getTransactionSummaryAll();
        } else {
            $transactions = $this->getTransactionSummary();
        }
        
        // Group transactions based on selection
        $groupedTransactions = null;
        if ($this->groupBy === 'item') {
            $groupedTransactions = $transactions->groupBy('item_code');
        } elseif ($this->groupBy === 'lot') {
            $groupedTransactions = $transactions->groupBy('lot_code');
        }
        
        return view('livewire.inventory.inventory-transactions-history', [
            'transactions' => $transactions,
            'groupedTransactions' => $groupedTransactions,
        ]);
    }
    
    /**
     * Get all transactions without pagination (for grouping)
     */
    public function getTransactionSummaryAll()
    {
        $query = InventoryTransaction::query();

        // Apply filters
        if ($this->filters['lot_code']) {
            $query->where('lot_code', 'like', '%' . $this->filters['lot_code'] . '%');
        }

        if ($this->filters['category']) {
            $query->where('category', $this->filters['category']);
        }

        if ($this->filters['warehouse']) {
            $query->where('warehouse', $this->filters['warehouse']);
        }

        if ($this->filters['txn_type']) {
            $query->where('txn_type', $this->filters['txn_type']);
        }

        if ($this->filters['date_from']) {
            $query->where('txn_date', '>=', $this->filters['date_from']);
        }

        if ($this->filters['date_to']) {
            $query->where('txn_date', '<=', $this->filters['date_to']);
        }

        $transactions = $query->orderBy('txn_date', 'desc')
                              ->orderBy('created_at', 'desc')
                              ->get();

        // Calculate balance for each transaction
        $balances = $this->calculateBalances($transactions);
        
        // Attach balance to each transaction
        foreach ($transactions as $transaction) {
            $key = $transaction->lot_code . '_' . $transaction->id;
            $transaction->balance_qty = $balances[$key] ?? 0;
        }

        return $transactions;
    }
}
