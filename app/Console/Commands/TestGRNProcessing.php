<?php

namespace App\Console\Commands;

use App\Models\GRN;
use App\Services\GRNProcessingService;
use Illuminate\Console\Command;

class TestGRNProcessing extends Command
{
    protected $signature = 'test:grn-processing';
    protected $description = 'Test GRN processing functionality';

    public function handle()
    {
        $this->info('Testing GRN Processing...');
        
        $grn = GRN::with('items')->first();
        if (!$grn) {
            $this->error('No GRN found');
            return;
        }
        
        $this->info("GRN: {$grn->grn_no}");
        $this->info("Items: {$grn->items->count()}");
        $this->info("Status: {$grn->status}");
        
        // Test processing service
        $processingService = app(GRNProcessingService::class);
        $status = $processingService->getGRNProcessingStatus($grn);
        
        $this->info("Processing Status: " . json_encode($status));
        
        $this->info('GRN Processing test completed!');
    }
}
