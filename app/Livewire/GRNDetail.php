<?php

namespace App\Livewire;

use App\Models\GRN;
use App\Services\GRNProcessingService;
use App\Services\InventoryReportService;
use Livewire\Component;

class GRNDetail extends Component
{
    public int $grnId;
    public ?GRN $grn = null;
    public array $processingStatus = [];
    public bool $showProcessingModal = false;
    public array $partialQuantities = [];

    public function mount(int $id)
    {
        $this->grnId = $id;
        $this->grn = GRN::with(['supplierOrder.supplier', 'productionOrder.supplier', 'items'])->findOrFail($id);
        
        // Set default status if not set
        if (!$this->grn->status) {
            $this->grn->update(['status' => 'pending']);
        }
        
        $this->loadProcessingStatus();
    }

    public function loadProcessingStatus()
    {
        $processingService = app(GRNProcessingService::class);
        $this->processingStatus = $processingService->getGRNProcessingStatus($this->grn);
    }

    public function openModal()
    {
        $this->showProcessingModal = true;
        
        // Initialize partial quantities with full quantities
        $this->partialQuantities = [];
        foreach ($this->grn->items as $item) {
            $this->partialQuantities[$item->id] = $item->qty_received;
        }
    }


    public function closeProcessingModal()
    {
        $this->showProcessingModal = false;
    }

    public function processToStock()
    {
        try {
            $processingService = app(GRNProcessingService::class);
            
            // Get configuration values
            $costingMethod = \App\Models\SystemConfiguration::getValue('grn_default_costing_method', 'FIFO');
            $enablePartialProcessing = \App\Models\SystemConfiguration::getValue('grn_enable_partial_processing', false);
            
            if ($enablePartialProcessing) {
                // Process with partial quantities
                $result = $processingService->processGRNToStockPartial($this->grn, $this->partialQuantities, $costingMethod);
            } else {
                // Process full quantities
                $result = $processingService->processGRNToStock($this->grn, $costingMethod);
            }
            
            if ($result['success']) {
                $message = $enablePartialProcessing 
                    ? 'GRN items processed to stock successfully! Total value: $' . number_format($result['total_value'], 2)
                    : 'GRN processed to stock successfully! Total value: $' . number_format($result['total_value'], 2);
                    
                session()->flash('success', $message);
                $this->loadProcessingStatus();
                $this->closeProcessingModal();
            } else {
                session()->flash('error', 'Failed to process GRN to stock.');
            }
        } catch (\Exception $e) {
            \Log::error('Error processing GRN to stock', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Error processing GRN: ' . $e->getMessage());
        }
    }

    public function updatePartialQuantity($itemId, $quantity)
    {
        $this->partialQuantities[$itemId] = max(0, (float)$quantity);
    }


    public function render()
    {
        return view('livewire.grn-detail');
    }
}


