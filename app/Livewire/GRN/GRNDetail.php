<?php

namespace App\Livewire\GRN;

use App\Models\GRN;
use App\Models\ConsumableItem;
use App\Models\GRNItem;
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

    // Add consumable item properties
    public bool $showAddItemModal = false;
    public $selectedConsumableItemId = '';
    public $itemQuantity = '';
    public $itemUnitCost = '';
    public $itemNotes = '';
    public $itemSearchTerm = '';
    public bool $showItemDropdown = false;
    
    // Balance cancellation
    public bool $showCancelBalanceModal = false;

    public function mount(int $id)
    {
        $this->grnId = $id;
        $this->grn = GRN::with(['supplierOrder.supplier', 'productionOrder.supplier', 'purchaseOrder.supplier', 'purchaseOrder.items.jobOrder.customer', 'supplier', 'items'])->findOrFail($id);
        
        // Set default status if not set
        if (!$this->grn->status) {
            $this->grn->update(['status' => 'pending']);
        }
        
        // Sync receiving status for all items to fix any inconsistencies
        $this->syncItemsReceivingStatus();
        
        $this->loadProcessingStatus();
    }

    /**
     * Open modal to add consumable item
     */
    public function openAddItemModal()
    {
        // Only allow adding items if GRN is not from supplier order or production order
        if ($this->grn->supplier_po_id || $this->grn->production_order_id || $this->grn->purchase_order_id) {
            session()->flash('error', 'Cannot add items to GRNs created from orders. Items are automatically added from the order.');
            return;
        }

        // Prevent adding items if GRN is already processed
        if ($this->grn->status === 'processed') {
            session()->flash('error', 'Cannot add items to a processed GRN.');
            return;
        }

        $this->selectedConsumableItemId = '';
        $this->itemQuantity = '';
        $this->itemUnitCost = '';
        $this->itemNotes = '';
        $this->itemSearchTerm = '';
        $this->showItemDropdown = false;
        $this->showAddItemModal = true;
    }

    /**
     * Close add item modal
     */
    public function closeAddItemModal()
    {
        $this->showAddItemModal = false;
        $this->selectedConsumableItemId = '';
        $this->itemQuantity = '';
        $this->itemUnitCost = '';
        $this->itemNotes = '';
        $this->itemSearchTerm = '';
        $this->showItemDropdown = false;
    }

    /**
     * Auto-fill consumable item details when selected
     */
    public function updatedSelectedConsumableItemId($value)
    {
        if ($value) {
            $consumable = ConsumableItem::find($value);
            if ($consumable) {
                $this->itemUnitCost = $consumable->default_unit_cost;
                $this->itemSearchTerm = $consumable->item_code . ' - ' . $consumable->item_name;
                $this->showItemDropdown = false;
            }
        }
    }

    /**
     * Handle search term changes and show dropdown
     */
    public function updatedItemSearchTerm($value)
    {
        // Show dropdown when typing
        if (!$this->showItemDropdown) {
            $this->showItemDropdown = true;
        }
        // Clear selection if search term is cleared
        if (empty($value)) {
            $this->selectedConsumableItemId = '';
            $this->itemUnitCost = '';
        }
    }

    /**
     * Show dropdown when input is focused
     */
    public function showItemDropdown()
    {
        $this->showItemDropdown = true;
    }


    /**
     * Select consumable item from dropdown
     */
    public function selectConsumableItem($itemId)
    {
        $this->selectedConsumableItemId = $itemId;
        $consumable = ConsumableItem::find($itemId);
        if ($consumable) {
            $this->itemUnitCost = $consumable->default_unit_cost;
            $this->itemSearchTerm = $consumable->item_code . ' - ' . $consumable->item_name;
            $this->showItemDropdown = false;
            
            // Dispatch browser event to ensure UI updates
            $this->dispatch('item-selected');
        }
    }

    /**
     * Clear item selection
     */
    public function clearItemSelection()
    {
        $this->selectedConsumableItemId = '';
        $this->itemSearchTerm = '';
        $this->itemUnitCost = '';
        $this->showItemDropdown = false;
    }

    /**
     * Get filtered consumable items for dropdown
     */
    public function getFilteredConsumableItems()
    {
        $query = ConsumableItem::where('is_active', true)
            ->orderBy('item_code');

        if (!empty($this->itemSearchTerm)) {
            $searchTerm = '%' . $this->itemSearchTerm . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('item_code', 'like', $searchTerm)
                  ->orWhere('item_name', 'like', $searchTerm);
            });
        }

        // If no search term, show first 5 items by default
        if (empty($this->itemSearchTerm)) {
            return $query->limit(5)->get();
        }

        return $query->limit(10)->get();
    }

    /**
     * Add consumable item to GRN
     */
    public function addConsumableItem()
    {
        \Log::info('addConsumableItem called', [
            'selectedConsumableItemId' => $this->selectedConsumableItemId,
            'itemQuantity' => $this->itemQuantity,
            'itemUnitCost' => $this->itemUnitCost,
            'grn_id' => $this->grn->id ?? null,
        ]);

        // Validate selectedConsumableItemId first
        if (empty($this->selectedConsumableItemId)) {
            session()->flash('error', 'Please select a consumable item from the dropdown.');
            return;
        }

        // Validate quantity
        if (empty($this->itemQuantity) || !is_numeric($this->itemQuantity) || $this->itemQuantity <= 0) {
            session()->flash('error', 'Please enter a valid quantity greater than 0.');
            return;
        }

        // Validate unit cost
        if (empty($this->itemUnitCost) || !is_numeric($this->itemUnitCost) || $this->itemUnitCost < 0) {
            session()->flash('error', 'Please enter a valid unit cost.');
            return;
        }

        try {
            $consumable = ConsumableItem::find($this->selectedConsumableItemId);
            
            if (!$consumable) {
                session()->flash('error', 'Selected consumable item not found.');
                return;
            }

            \Log::info('Creating GRNItem', [
                'grn_id' => $this->grn->id,
                'material_code' => $consumable->item_code,
                'quantity' => $this->itemQuantity,
            ]);

            // Create GRNItem
            $grnItem = GRNItem::create([
                'grn_id' => $this->grn->id,
                'production_order_item_id' => null,
                'item_type' => null,
                'item_id' => null,
                'material_code' => $consumable->item_code,
                'description' => $consumable->item_name,
                'qty_received' => $this->itemQuantity,
                'qty_expected' => $this->itemQuantity,
                'uom' => $consumable->default_uom,
                'unit_cost' => $this->itemUnitCost,
                'notes' => $this->itemNotes,
            ]);

            \Log::info('GRNItem created', ['grn_item_id' => $grnItem->id]);

            // Initialize partial receiving fields - start as NOT received
            $grnItem->initializePartialReceiving();
            
            // Keep as not received initially - user will mark as received using Complete button
            $grnItem->update([
                'qty_received_partial' => 0,
                'qty_pending' => $this->itemQuantity,
                'is_fully_received' => false,
                'last_received_at' => null,
            ]);

            session()->flash('success', 'Consumable item added successfully!');
            
            // Refresh GRN data
            $this->grn->refresh();
            $this->grn->load('items');
            
            // Close modal
            $this->closeAddItemModal();
            
        } catch (\Exception $e) {
            \Log::error('Error adding consumable item', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'Failed to add consumable item: ' . $e->getMessage());
        }
    }

    /**
     * Mark item as fully received (Complete)
     */
    public function markItemAsReceived($itemId)
    {
        try {
            $item = GRNItem::findOrFail($itemId);
            
            // Prevent marking as received if already processed
            if ($item->qty_processed > 0) {
                session()->flash('error', 'Cannot modify item that has already been processed.');
                return;
            }

            // Mark as fully received
            $item->update([
                'qty_received_partial' => $item->qty_expected,
                'qty_pending' => 0,
                'is_fully_received' => true,
                'last_received_at' => now(),
            ]);

            session()->flash('success', 'Item marked as received successfully.');
            
            // Refresh GRN data
            $this->grn->refresh();
            $this->grn->load('items');
            $this->syncItemsReceivingStatus();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to mark item as received: ' . $e->getMessage());
        }
    }

    /**
     * Delete GRN item
     */
    public function deleteItem($itemId)
    {
        try {
            $item = GRNItem::findOrFail($itemId);
            
            // Prevent deletion if item is already processed
            if ($item->qty_processed > 0) {
                session()->flash('error', 'Cannot delete item that has already been processed.');
                return;
            }

            $item->delete();
            
            session()->flash('success', 'Item deleted successfully.');
            
            // Refresh GRN data
            $this->grn->refresh();
            $this->grn->load('items');
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to delete item: ' . $e->getMessage());
        }
    }

    /**
     * Sync receiving status for all GRN items
     */
    protected function syncItemsReceivingStatus()
    {
        foreach ($this->grn->items as $item) {
            $item->syncReceivingStatus();
            // Only save if there were changes to avoid unnecessary writes
            if ($item->isDirty()) {
                $item->save();
            }
        }
    }

    public function loadProcessingStatus()
    {
        $processingService = app(GRNProcessingService::class);
        $this->processingStatus = $processingService->getGRNProcessingStatus($this->grn);
    }

    public function openModal()
    {
        // Refresh GRN data to ensure we have the latest processed quantities
        $this->grn->refresh();
        $this->grn->load('items');
        
        // Prevent opening modal if GRN is already fully processed
        if ($this->grn->status === 'processed' && $this->grn->isFullyProcessed()) {
            session()->flash('error', 'This GRN has already been fully processed and cannot be processed again.');
            return;
        }
        
        $this->showProcessingModal = true;
        
        // Initialize partial quantities with available quantities for processing
        $this->partialQuantities = [];
        foreach ($this->grn->items as $item) {
            // Only allow processing of quantities that are available for processing
            // Available = Received - Already Processed
            $availableForProcessing = $item->qty_received_partial - ($item->qty_processed ?? 0);
            $this->partialQuantities[$item->id] = max(0, $availableForProcessing);
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
            // Refresh GRN data to ensure we have the latest processed quantities
            $this->grn->refresh();
            $this->grn->load('items');
            
            // Prevent processing if GRN is already fully processed
            if ($this->grn->status === 'processed' && $this->grn->isFullyProcessed()) {
                session()->flash('error', 'This GRN has already been fully processed and cannot be processed again.');
                return;
            }

            // Check if there are any unprocessed items
            if (!$this->grn->hasUnprocessedItems()) {
                session()->flash('error', 'All items in this GRN have already been processed.');
                return;
            }

            // Filter out items with 0 received quantities
            $itemsToProcess = $this->partialQuantities;
            foreach ($itemsToProcess as $itemId => $quantity) {
                $grnItem = $this->grn->items->find($itemId);
                if ($grnItem && $grnItem->qty_received_partial <= 0) {
                    // Remove items with no received quantity from processing
                    unset($itemsToProcess[$itemId]);
                    continue;
                }
                
                // Check against remaining available quantity (received - processed)
                $availableForProcessing = $grnItem->qty_received_partial - ($grnItem->qty_processed ?? 0);
                
                // Prevent processing if item is already fully processed
                if ($availableForProcessing <= 0) {
                    unset($itemsToProcess[$itemId]);
                    continue;
                }
                
                if ($grnItem && $quantity > $availableForProcessing) {
                    session()->flash('error', "Cannot process {$quantity} units for {$grnItem->description}. Only {$availableForProcessing} units are available for processing (Received: {$grnItem->qty_received_partial}, Already Processed: " . ($grnItem->qty_processed ?? 0) . ").");
                    return;
                }
            }
            
            // Check if there are any items to process
            if (empty($itemsToProcess)) {
                session()->flash('error', 'No items with received quantities to process.');
                return;
            }
            
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
                // Process with partial quantities (only items with received quantities)
                $result = $processingService->processGRNToStockPartial($this->grn, $itemsToProcess, $costingMethod);
            } else {
                // Process full quantities
                $result = $processingService->processGRNToStock($this->grn, $costingMethod);
            }
            
            if ($result['success']) {
                // Refresh GRN data to get latest status
                $this->grn->refresh();
                $this->grn->load('items');
                
                // Update GRN status based on processing result
                $hasPending = $this->grn->getTotalPendingQuantity() > 0;
                $hasUnprocessed = $this->grn->hasUnprocessedItems();
                
                if ($hasPending || $hasUnprocessed) {
                    // Set to partially_processed if there's balance or unprocessed items
                    if ($this->grn->status !== 'partially_processed') {
                        $this->grn->update(['status' => 'partially_processed']);
                    }
                } elseif ($this->grn->isFullyProcessed()) {
                    // All received items processed, set to processed
                    $this->grn->update([
                        'status' => 'processed',
                        'processed_at' => now()
                    ]);
                }
                
                $message = $enablePartialProcessing 
                    ? 'GRN items processed to stock successfully! Total value: $' . number_format($result['total_value'], 2)
                    : 'GRN processed to stock successfully! Total value: $' . number_format($result['total_value'], 2);
                    
                if ($hasPending) {
                    $message .= ' Balance quantity (' . number_format($this->grn->getTotalPendingQuantity(), 2) . ' units) remains pending.';
                }
                    
                session()->flash('success', $message);
                
                // Close modal
                $this->showProcessingModal = false;
                
                // Refresh GRN data
                $this->grn = GRN::with(['supplierOrder.supplier', 'productionOrder.supplier', 'purchaseOrder.supplier', 'purchaseOrder.items.jobOrder.customer', 'supplier', 'items'])->findOrFail($this->grnId);
                
                // Sync all items status in case any updates were needed
                $this->syncItemsReceivingStatus();
                
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
        // Find the GRN item to get the maximum allowed quantity
        $grnItem = $this->grn->items->find($itemId);
        
        if ($grnItem) {
            // Ensure quantity doesn't exceed what's available for processing (received - processed)
            $availableForProcessing = $grnItem->qty_received_partial - ($grnItem->qty_processed ?? 0);
            $maxAllowed = max(0, $availableForProcessing);
            $this->partialQuantities[$itemId] = max(0, min((float)$quantity, $maxAllowed));
        } else {
            $this->partialQuantities[$itemId] = max(0, (float)$quantity);
        }
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
            // Store the old status to check if we need to reset it
            $oldStatus = $this->grn->status;
            
            $this->selectedGRNItem->addPartialReceiving(
                $this->partialReceivingForm['quantity'],
                $this->partialReceivingForm['notes']
            );

            session()->flash('success', 'Partial receipt added successfully.');
            
            // Refresh the GRN data
            $this->grn->refresh();
            
            // If GRN was previously 'processed' but now has unprocessed items, reset status to 'pending'
            if ($oldStatus === 'processed' && $this->grn->hasUnprocessedItems()) {
                $this->grn->update([
                    'status' => 'pending',
                    'processed_at' => null
                ]);
                $this->grn->refresh();
            }
            
            // Sync all items status in case any updates were needed
            $this->syncItemsReceivingStatus();
            
            // Close modal
            $this->closePartialReceivingModal();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to add partial receipt: ' . $e->getMessage());
        }
    }

    /**
     * Open cancel balance modal
     */
    public function openCancelBalanceModal()
    {
        $this->showCancelBalanceModal = true;
    }

    /**
     * Close cancel balance modal
     */
    public function closeCancelBalanceModal()
    {
        $this->showCancelBalanceModal = false;
    }

    /**
     * Cancel balance quantities
     */
    public function cancelBalance()
    {
        try {
            $this->grn->refresh();
            $this->grn->load('items');
            
            // Only allow cancellation if there are pending quantities
            if ($this->grn->getTotalPendingQuantity() <= 0) {
                session()->flash('error', 'No balance quantity to cancel.');
                return;
            }
            
            // Update all items to mark pending quantities as cancelled
            foreach ($this->grn->items as $item) {
                if ($item->qty_pending > 0) {
                    // Set pending to 0 and mark as fully received (even though we're cancelling the balance)
                    // This effectively closes the balance
                    $item->update([
                        'qty_pending' => 0,
                        'qty_expected' => $item->qty_received_partial, // Adjust expected to match received
                    ]);
                }
            }
            
            // Update GRN status
            if ($this->grn->hasUnprocessedItems()) {
                $this->grn->update(['status' => 'partially_processed']);
            } else {
                $this->grn->update(['status' => 'processed']);
            }
            
            session()->flash('success', 'Balance quantities have been cancelled successfully.');
            
            // Refresh GRN data
            $this->grn->refresh();
            $this->grn->load('items');
            $this->syncItemsReceivingStatus();
            
            $this->closeCancelBalanceModal();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to cancel balance: ' . $e->getMessage());
        }
    }

    /**
     * Refresh GRN data and sync status
     */
    public function refreshGRN()
    {
        $this->grn = GRN::with(['supplierOrder.supplier', 'productionOrder.supplier', 'purchaseOrder.supplier', 'supplier', 'items'])->findOrFail($this->grnId);
        $this->syncItemsReceivingStatus();
        $this->loadProcessingStatus();
    }

    public function render()
    {
        $consumableItems = ConsumableItem::where('is_active', true)
            ->orderBy('item_code')
            ->get();

        // Get filtered items for dropdown
        $filteredItems = $this->getFilteredConsumableItems();

        return view('livewire.grn.grn-detail', [
            'consumableItems' => $consumableItems,
            'filteredItems' => $filteredItems,
        ]);
    }
}


