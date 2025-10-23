<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\GRN;
use App\Models\GRNItem;
use App\Services\GRNProcessingService;
use App\Services\InventoryReportService;

class TestStockProcessing extends Command
{
    protected $signature = 'test:stock-processing';
    protected $description = 'Test the complete stock processing flow';

    public function handle()
    {
        $this->info('🧪 Testing Stock Processing Implementation');
        $this->line('');

        // Check if we have any GRNs to test with
        $grns = GRN::with('items')->get();
        
        if ($grns->isEmpty()) {
            $this->warn('No GRNs found. Please create some GRNs first.');
            return;
        }

        $this->info('📋 Found ' . $grns->count() . ' GRN(s)');
        
        // Show GRN status
        foreach ($grns as $grn) {
            $this->line("GRN: {$grn->grn_no} - Status: " . ($grn->status ?? 'pending'));
            $this->line("  Items: {$grn->items->count()}");
            $this->line("  Total Value: $" . number_format($grn->total_value ?? 0, 2));
            $this->line('');
        }

        // Test processing service
        $processingService = app(GRNProcessingService::class);
        $reportService = app(InventoryReportService::class);

        $this->info('🔧 Testing GRN Processing Service...');
        
        // Test with first GRN
        $testGRN = $grns->first();
        
        if ($testGRN->status === 'pending') {
            $this->info("Processing GRN: {$testGRN->grn_no}");
            
            try {
                $result = $processingService->processGRNToStock($testGRN, 'FIFO');
                
                if ($result['success']) {
                    $this->info('✅ GRN processed successfully!');
                    $this->line("Total Value: $" . number_format($result['total_value'], 2));
                    $this->line("Items Processed: " . count($result['processed_items']));
                } else {
                    $this->error('❌ GRN processing failed');
                }
            } catch (\Exception $e) {
                $this->error('❌ Error: ' . $e->getMessage());
            }
        } else {
            $this->info("GRN {$testGRN->grn_no} is already processed");
        }

        $this->line('');

        // Test inventory reports
        $this->info('📊 Testing Inventory Reports...');
        
        try {
            $agingReport = $reportService->getAgingReport();
            $this->info('✅ Aging report generated');
            $this->line("Total Items: " . $agingReport['total_qty']);
            $this->line("Total Value: $" . number_format($agingReport['total_value'], 2));
            
            $movementReport = $reportService->getMovementReport();
            $this->info('✅ Movement report generated');
            $this->line("Total Transactions: " . $movementReport['total_transactions']);
            
        } catch (\Exception $e) {
            $this->error('❌ Report generation failed: ' . $e->getMessage());
        }

        $this->line('');
        $this->info('🎉 Stock Processing Test Complete!');
        $this->line('');
        $this->info('Next steps:');
        $this->line('1. Visit GRN Detail page to see processing UI');
        $this->line('2. Test FIFO/LIFO costing methods');
        $this->line('3. Check inventory aging reports');
        $this->line('4. Verify production order stock status');
    }
}
