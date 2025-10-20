<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\InventoryService;
use App\Services\InventoryCostingService;
use App\Services\InventoryReportService;

class DemoFifoLifo extends Command
{
    protected $signature = 'demo:fifo-lifo';
    protected $description = 'Demonstrate FIFO and LIFO inventory costing methods';

    public function handle()
    {
        $this->info('🚀 FIFO/LIFO Inventory Costing Demo');
        $this->line('');

        // Initialize services
        $inventoryService = app(InventoryService::class);
        $costingService = app(InventoryCostingService::class);
        $reportService = app(InventoryReportService::class);

        // Demo data
        $itemCode = 'PAPER-001';
        $category = 'RAW';
        $warehouse = 'MAIN';

        $this->info('📦 Creating sample inventory receipts...');
        
        // Receipt 1: 100 units at $10 each
        $inventoryService->recordTransaction([
            'lot_code' => 'GRN001-20241020-01',
            'item_code' => $itemCode,
            'category' => $category,
            'txn_type' => 'receipt',
            'qty' => 100,
            'unit_cost' => 10.00,
            'uom' => 'KG',
            'warehouse' => $warehouse,
            'related_doc_type' => 'GRN',
            'related_doc_id' => 1,
            'txn_date' => '2024-10-15',
            'remarks' => 'First receipt',
        ], 'FIFO');

        // Receipt 2: 50 units at $12 each
        $inventoryService->recordTransaction([
            'lot_code' => 'GRN002-20241020-02',
            'item_code' => $itemCode,
            'category' => $category,
            'txn_type' => 'receipt',
            'qty' => 50,
            'unit_cost' => 12.00,
            'uom' => 'KG',
            'warehouse' => $warehouse,
            'related_doc_type' => 'GRN',
            'related_doc_id' => 2,
            'txn_date' => '2024-10-18',
            'remarks' => 'Second receipt',
        ], 'FIFO');

        // Receipt 3: 30 units at $15 each
        $inventoryService->recordTransaction([
            'lot_code' => 'GRN003-20241020-03',
            'item_code' => $itemCode,
            'category' => $category,
            'txn_type' => 'receipt',
            'qty' => 30,
            'unit_cost' => 15.00,
            'uom' => 'KG',
            'warehouse' => $warehouse,
            'related_doc_type' => 'GRN',
            'related_doc_id' => 3,
            'txn_date' => '2024-10-20',
            'remarks' => 'Third receipt',
        ], 'FIFO');

        $this->info('✅ Sample receipts created');
        $this->line('');

        // Show current inventory layers
        $this->info('📊 Current Inventory Layers:');
        $layers = \App\Models\InventoryLayer::where('item_code', $itemCode)->get();
        
        $this->table(
            ['Lot Code', 'Qty', 'Unit Cost', 'Total Cost', 'Receipt Date'],
            $layers->map(function ($layer) {
                return [
                    $layer->lot_code,
                    $layer->qty_available,
                    '$' . number_format($layer->unit_cost, 2),
                    '$' . number_format($layer->total_cost, 2),
                    $layer->receipt_date,
                ];
            })
        );

        $this->line('');

        // Demonstrate FIFO consumption
        $this->info('🔄 FIFO Consumption (60 units):');
        $fifoResult = $costingService->processConsumption($itemCode, $category, $warehouse, 60, 'FIFO');
        $this->line("Total Cost: $" . number_format($fifoResult['total_cost'], 2));
        $this->line("Average Cost: $" . number_format($fifoResult['average_cost'], 2));
        $this->line('');

        // Show remaining layers after FIFO
        $this->info('📊 Remaining Layers after FIFO:');
        $remainingLayers = \App\Models\InventoryLayer::where('item_code', $itemCode)->get();
        
        $this->table(
            ['Lot Code', 'Qty', 'Unit Cost', 'Total Cost', 'Receipt Date'],
            $remainingLayers->map(function ($layer) {
                return [
                    $layer->lot_code,
                    $layer->qty_available,
                    '$' . number_format($layer->unit_cost, 2),
                    '$' . number_format($layer->total_cost, 2),
                    $layer->receipt_date,
                ];
            })
        );

        $this->line('');

        // Demonstrate LIFO consumption
        $this->info('🔄 LIFO Consumption (40 units):');
        $lifoResult = $costingService->processConsumption($itemCode, $category, $warehouse, 40, 'LIFO');
        $this->line("Total Cost: $" . number_format($lifoResult['total_cost'], 2));
        $this->line("Average Cost: $" . number_format($lifoResult['average_cost'], 2));
        $this->line('');

        // Show final inventory
        $this->info('📊 Final Inventory:');
        $finalLayers = \App\Models\InventoryLayer::where('item_code', $itemCode)->get();
        
        $this->table(
            ['Lot Code', 'Qty', 'Unit Cost', 'Total Cost', 'Receipt Date'],
            $finalLayers->map(function ($layer) {
                return [
                    $layer->lot_code,
                    $layer->qty_available,
                    '$' . number_format($layer->unit_cost, 2),
                    '$' . number_format($layer->total_cost, 2),
                    $layer->receipt_date,
                ];
            })
        );

        $this->line('');
        $this->info('✅ FIFO/LIFO Demo completed!');
    }
}
