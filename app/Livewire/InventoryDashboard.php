<?php

namespace App\Livewire;

use App\Models\Inventory;
use App\Services\InventoryService;
use Livewire\Component;

class InventoryDashboard extends Component
{
    protected $layout = 'components.layouts.app';

    public $inventorySummary = [];
    public $selectedCategory = '';
    public $selectedWarehouse = '';

    public function mount()
    {
        $this->loadInventorySummary();
    }

    public function loadInventorySummary()
    {
        $inventoryService = app(InventoryService::class);
        $this->inventorySummary = $inventoryService->getInventorySummary();
    }

    public function filterByCategory($category)
    {
        $this->selectedCategory = $category;
        $this->loadInventorySummary();
    }

    public function filterByWarehouse($warehouse)
    {
        $this->selectedWarehouse = $warehouse;
        $this->loadInventorySummary();
    }

    public function getInventoryByCategory()
    {
        $query = Inventory::selectRaw('category, SUM(qty_available) as total_qty')
            ->groupBy('category');

        if ($this->selectedWarehouse) {
            $query->where('warehouse', $this->selectedWarehouse);
        }

        return $query->get();
    }

    public function getInventoryByWarehouse()
    {
        $query = Inventory::selectRaw('warehouse, SUM(qty_available) as total_qty')
            ->groupBy('warehouse');

        if ($this->selectedCategory) {
            $query->where('category', $this->selectedCategory);
        }

        return $query->get();
    }

    public function getLowStockItems()
    {
        return Inventory::where('qty_available', '<', 10)
            ->orderBy('qty_available')
            ->get();
    }

    public function getRecentTransactions()
    {
        return \App\Models\InventoryTransaction::with('inventory')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.inventory-dashboard', [
            'inventoryByCategory' => $this->getInventoryByCategory(),
            'inventoryByWarehouse' => $this->getInventoryByWarehouse(),
            'lowStockItems' => $this->getLowStockItems(),
            'recentTransactions' => $this->getRecentTransactions(),
        ]);
    }
}
