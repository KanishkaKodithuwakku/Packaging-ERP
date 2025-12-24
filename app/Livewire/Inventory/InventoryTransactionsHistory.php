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

        return $query->with('inventory')
            ->orderBy('txn_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function getTransactionStats()
    {
        $query = InventoryTransaction::query();

        if ($this->filters['date_from']) {
            $query->where('txn_date', '>=', $this->filters['date_from']);
        }

        if ($this->filters['date_to']) {
            $query->where('txn_date', '<=', $this->filters['date_to']);
        }

        $stats = $query->selectRaw('
            txn_type,
            COUNT(*) as count,
            SUM(qty) as total_qty
        ')->groupBy('txn_type')->get();

        return $stats;
    }

    public function render()
    {
        return view('livewire.inventory.inventory-transactions-history', [
            'transactions' => $this->getTransactionSummary(),
            'stats' => $this->getTransactionStats(),
        ]);
    }
}
