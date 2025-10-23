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
    
    // Partial receiving properties
    public bool $showPartialReceivingModal = false;
    public ?\App\Models\GRNItem $selectedGRNItem = null;
    public array $partialReceivingForm = [
        'quantity' => '',
        'notes' => ''
    ];

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
        
        // Initialize partial quantities with received partial quantities
        $this->partialQuantities = [];
        foreach ($this->grn->items as $item) {
            // Use the received partial quantity if available, otherwise use full quantity
            $this->partialQuantities[$item->id] = $item->qty_received_partial > 0 ? $item->qty_received_partial : $item->qty_received;
        }
    }


    public function closeProcessingModal()
    {
        $this->showProcessingModal = false;
    }

    public function testButton()
    {
        session()->flash('success', 'Button click is working!');
    }

    public function simpleTest()
    {
        session()->flash('success', 'Simple test method called!');
    }

    public function processToStock()
    {
        try {
            $processingService = app(GRNProcessingService::class);
            
            // Get configuration values
            $costingMethod = \App\Models\SystemConfiguration::getValue('grn_default_costing_method', 'FIFO');
            $enablePartialProcessing = \App\Models\SystemConfiguration::getValue('grn_enable_partial_processing', false);
            
            // Debug: Log the quantities being processed
            \Log::info('Processing quantities', [
                'partial_quantities' => $this->partialQuantities,
                'enable_partial_processing' => $enablePartialProcessing
            ]);
            
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
                
                // Close modal
                $this->showProcessingModal = false;
                
                // Refresh GRN data
                $this->grn = GRN::with(['supplierOrder.supplier', 'productionOrder.supplier', 'items'])->findOrFail($this->grnId);
                $this->loadProcessingStatus();
                
            } else {
                session()->flash('error', 'Failed to process GRN to stock.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Error processing GRN: ' . $e->getMessage());
        }
    }

    public function processGRNToStock()
    {
        session()->flash('success', 'Alternative method called successfully!');
    }

    public function updatePartialQuantity($itemId, $quantity)
    {
        $this->partialQuantities[$itemId] = max(0, (float)$quantity);
    }

    // Partial receiving methods
    public function openPartialReceivingModal($itemId)
    {
        $this->selectedGRNItem = \App\Models\GRNItem::find($itemId);
        if ($this->selectedGRNItem) {
            $this->partialReceivingForm = [
                'quantity' => '',
                'notes' => ''
            ];
            $this->showPartialReceivingModal = true;
        }
    }

    public function closePartialReceivingModal()
    {
        $this->showPartialReceivingModal = false;
        $this->selectedGRNItem = null;
        $this->partialReceivingForm = [
            'quantity' => '',
            'notes' => ''
        ];
    }

    public function addPartialReceiving()
    {
        $this->validate([
            'partialReceivingForm.quantity' => 'required|numeric|min:0.01|max:' . ($this->selectedGRNItem->qty_pending ?? 0),
            'partialReceivingForm.notes' => 'nullable|string|max:500'
        ], [
            'partialReceivingForm.quantity.required' => 'Quantity is required',
            'partialReceivingForm.quantity.numeric' => 'Quantity must be a number',
            'partialReceivingForm.quantity.min' => 'Quantity must be greater than 0',
            'partialReceivingForm.quantity.max' => 'Quantity cannot exceed pending amount',
            'partialReceivingForm.notes.max' => 'Notes cannot exceed 500 characters'
        ]);

        try {
            $this->selectedGRNItem->addPartialReceiving(
                $this->partialReceivingForm['quantity'],
                $this->partialReceivingForm['notes']
            );

            session()->flash('success', 'Partial receipt added successfully.');
            
            // Refresh the GRN data
            $this->grn->refresh();
            
            // Close modal
            $this->closePartialReceivingModal();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to add partial receipt: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.grn-detail');
    }
}


