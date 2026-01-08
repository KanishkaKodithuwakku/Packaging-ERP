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

    public $filters = [
        'lot_code' => '',
        'category' => '',
        'warehouse' => '',
        'txn_type' => '',
        'date_from' => '',
        'date_to' => '',
    ];

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
        $allTransactions = $query->with(['inventory', 'grn.purchaseOrder.items.jobOrder', 'grn.productionOrder.jobOrder'])
            ->orderBy('txn_date', 'asc')
            ->orderBy('created_at', 'asc')
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
        return view('livewire.inventory.inventory-transactions-history', [
            'transactions' => $this->getTransactionSummary(),
        ]);
    }
}
