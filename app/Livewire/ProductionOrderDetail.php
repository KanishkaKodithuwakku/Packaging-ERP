<?php

namespace App\Livewire;

use App\Models\ProductionOrder;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\JobOrder;
use App\Models\GRN;
use App\Services\ProductionGRNService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class ProductionOrderDetail extends Component
{
    public $productionOrder;
    public $productionOrderId;
    public $isEditMode = false;
    public $showPrintPreviewModal = false;
    public $printDisplayFormat = 'dimensions';
    public $showGRNModal = false;
    public $selectedItemId = null;
    public $grnQuantity = 0;
    public $grnLotCode = '';
    public $availableQuantities = [];
    public $multiItemMode = false;
    public $selectedItems = [];
    public $itemQuantities = [];
    public bool $hasAnyGRN = false; // Whether any GRN exists for this production order
    
    // Form data
    public $form = [];
    
    public function mount($id, $edit = false)
    {
        $this->productionOrderId = $id;
        
        // Check for edit parameter in URL query string
        $editFromQuery = request()->query('edit');
        
        $this->isEditMode = $edit === 'edit' || $edit === 'true' || $edit === true || $editFromQuery === 'true';
        $this->loadProductionOrder();
    }

    public function loadProductionOrder()
    {
        $this->productionOrder = ProductionOrder::with(['supplier', 'jobOrder', 'items'])
            ->findOrFail($this->productionOrderId);
        
        $this->form = $this->productionOrder->toArray();
        $this->form['date'] = $this->productionOrder->date ? $this->productionOrder->date->format('Y-m-d') : null;
        // Compute GRN existence status
        $this->hasAnyGRN = GRN::where('production_order_id', $this->productionOrder->id)->exists();
        
        // Load available quantities for GRN generation
        $this->loadAvailableQuantities();
    }

    public function toggleEditMode()
    {
        $this->isEditMode = !$this->isEditMode;
    }

    public function saveProductionOrder()
    {
        try {
            // Validate form data
            $this->validate([
                'form.date' => 'required|date',
                'form.status' => 'required|in:pending,in_production,completed,cancelled',
                'form.notes' => 'nullable|string',
            ]);

            // Update production order details
            $this->productionOrder->update([
                'date' => $this->form['date'],
                'status' => $this->form['status'],
                'notes' => $this->form['notes'],
            ]);
            
            // Reload the production order
            $this->loadProductionOrder();
            
            // Exit edit mode
            $this->isEditMode = false;
            
            session()->flash('success', 'Production order updated successfully!');
            
        } catch (\Exception $e) {
            Log::error('Error saving production order: ' . $e->getMessage());
            session()->flash('error', 'Error updating production order: ' . $e->getMessage());
        }
    }

    public function startProduction()
    {
        try {
            if (!$this->productionOrder) {
                session()->flash('error', 'Production order not found.');
                return;
            }

            // Prevent starting production until at least one GRN exists
            if (!$this->hasAnyGRN) {
                session()->flash('error', 'Cannot start production until at least one GRN is created for this order.');
                return;
            }

            // Update production order status to in_production
            $this->productionOrder->update(['status' => 'in_production']);
            
            // Reload the production order to get updated data
            $this->loadProductionOrder();
            
            session()->flash('success', 'Production has been started successfully!');
            
        } catch (\Exception $e) {
            Log::error('Error starting production: ' . $e->getMessage());
            session()->flash('error', 'Error starting production: ' . $e->getMessage());
        }
    }

    public function completeProduction()
    {
        try {
            if (!$this->productionOrder) {
                session()->flash('error', 'Production order not found.');
                return;
            }

            // Update production order status to completed
            $this->productionOrder->update(['status' => 'completed']);
            
            // Reload the production order to get updated data
            $this->loadProductionOrder();
            
            session()->flash('success', 'Production has been completed successfully!');
            
        } catch (\Exception $e) {
            Log::error('Error completing production: ' . $e->getMessage());
            session()->flash('error', 'Error completing production: ' . $e->getMessage());
        }
    }

    public function showPrintPreview()
    {
        $this->showPrintPreviewModal = true;
    }

    public function closePrintPreviewModal()
    {
        $this->showPrintPreviewModal = false;
    }

    public function printProductionOrder()
    {
        $this->closePrintPreviewModal();
        
        // Dispatch event to trigger JavaScript print function
        $this->js('window.printProductionOrder();');
    }

    public function loadAvailableQuantities()
    {
        $grnService = new ProductionGRNService();
        $this->availableQuantities = $grnService->getAvailableQuantities($this->productionOrder);
    }

    public function openGRNModal($itemId = null)
    {
        $this->selectedItemId = $itemId;
        $this->grnQuantity = 0;
        $this->grnLotCode = '';
        $this->multiItemMode = false;
        $this->selectedItems = [];
        $this->itemQuantities = [];
        
        // Reload available quantities
        $this->loadAvailableQuantities();
        
        $this->showGRNModal = true;
        
        // Debug: Log the available quantities
        Log::info('Opening GRN Modal', [
            'production_order_id' => $this->productionOrderId,
            'available_quantities_count' => count($this->availableQuantities),
            'available_quantities' => $this->availableQuantities
        ]);
        
        // Show a simple flash message to confirm button click
        session()->flash('info', 'GRN Modal opened! Available items: ' . count($this->availableQuantities));
    }

    public function closeGRNModal()
    {
        $this->showGRNModal = false;
        $this->selectedItemId = null;
        $this->grnQuantity = 0;
        $this->grnLotCode = '';
        $this->multiItemMode = false;
        $this->selectedItems = [];
        $this->itemQuantities = [];
    }

    public function generateGRN()
    {
        $this->validate([
            'selectedItemId' => 'required|exists:production_order_items,id',
            'grnQuantity' => 'required|integer|min:1',
            'grnLotCode' => 'nullable|string|max:255',
        ]);

        try {
            $productionItem = $this->productionOrder->items()->findOrFail($this->selectedItemId);
            
            // Check if quantity is available
            if ($this->grnQuantity > $productionItem->getRemainingQuantity()) {
                session()->flash('error', 'Quantity exceeds remaining quantity for this item.');
                return;
            }

            $grnService = new ProductionGRNService();
            $grn = $grnService->generateGRNFromProductionItem(
                $productionItem, 
                $this->grnQuantity, 
                $this->grnLotCode ?: null
            );

            // Reload production order to get updated data
            $this->loadProductionOrder();
            $this->closeGRNModal();
            
            session()->flash('success', "GRN {$grn->grn_no} generated successfully for {$this->grnQuantity} items!");
            
        } catch (\Exception $e) {
            Log::error('Error generating GRN: ' . $e->getMessage());
            session()->flash('error', 'Error generating GRN: ' . $e->getMessage());
        }
    }

    public function toggleMultiItemMode()
    {
        $this->multiItemMode = !$this->multiItemMode;
        $this->selectedItems = [];
        $this->itemQuantities = [];
    }

    public function toggleItemSelection($itemId)
    {
        if (in_array($itemId, $this->selectedItems)) {
            $this->selectedItems = array_diff($this->selectedItems, [$itemId]);
            unset($this->itemQuantities[$itemId]);
            Log::info('Item deselected', [
                'item_id' => $itemId,
                'selected_items' => $this->selectedItems,
                'item_quantities' => $this->itemQuantities
            ]);
        } else {
            $this->selectedItems[] = $itemId;
            $this->itemQuantities[$itemId] = 1; // Set default quantity to 1
            Log::info('Item selected', [
                'item_id' => $itemId,
                'selected_items' => $this->selectedItems,
                'item_quantities' => $this->itemQuantities
            ]);
        }
    }

    public function updateItemQuantity($itemId, $quantity)
    {
        $this->itemQuantities[$itemId] = max(0, (int)$quantity);
        Log::info('Item quantity updated', [
            'item_id' => $itemId,
            'quantity' => $quantity,
            'item_quantities' => $this->itemQuantities
        ]);
    }

    public function generateMultiItemGRN()
    {
        $this->validate([
            'selectedItems' => 'required|array|min:1',
            'itemQuantities' => 'required|array',
            'grnLotCode' => 'nullable|string|max:255',
        ]);

        // Validate that all selected items have quantities
        foreach ($this->selectedItems as $itemId) {
            if (!isset($this->itemQuantities[$itemId]) || $this->itemQuantities[$itemId] <= 0) {
                session()->flash('error', 'Please enter quantities for all selected items.');
                return;
            }
        }

        try {
            Log::info('Generating multi-item GRN', [
                'production_order_id' => $this->productionOrder->id,
                'selected_items' => $this->selectedItems,
                'item_quantities' => $this->itemQuantities,
                'lot_code' => $this->grnLotCode
            ]);
            
            $grnService = new ProductionGRNService();
            $grn = $grnService->generateMultiItemGRN(
                $this->productionOrder,
                $this->itemQuantities,
                $this->grnLotCode ?: null
            );

            // Reload production order to get updated data
            $this->loadProductionOrder();
            $this->closeGRNModal();
            
            $totalQuantity = array_sum($this->itemQuantities);
            session()->flash('success', "Multi-item GRN {$grn->grn_no} generated successfully for {$totalQuantity} total items!");
            
        } catch (\Exception $e) {
            Log::error('Error generating multi-item GRN: ' . $e->getMessage());
            session()->flash('error', 'Error generating multi-item GRN: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.production-order-detail', [
            'suppliers' => Supplier::all(),
            'customers' => Customer::all(),
        ]);
    }
}
