<?php

namespace App\Livewire\ProductionOrders;

use App\Models\ProductionOrder;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\JobOrder;
use App\Models\GRN;
use App\Models\InventoryTransaction;
use App\Services\ProductionGRNService;
use App\Services\InventoryService;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
    public int $grnCount = 0; // Number of GRNs for this production order
    public float $totalBalanceQuantity = 0; // Total balance quantity across all GRNs
    
    // Form data
    public $form = [];
    // Per-item partial completion quantities
    public array $completeQty = []; // Used boards quantity
    public array $completeFgQty = []; // Finished goods (boxes) quantity
    public bool $canComplete = false;
    
    // Completion confirmation modal
    public bool $showCompleteConfirmModal = false;
    public ?int $pendingCompleteItemId = null;
    public int $pendingCompleteQty = 0;
    public bool $bypassGRN = false;
    
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
        $this->productionOrder = ProductionOrder::with(['supplier', 'jobOrder.customer', 'items'])
            ->findOrFail($this->productionOrderId);
        
        $this->form = $this->productionOrder->toArray();
        $this->form['date'] = $this->productionOrder->date ? $this->productionOrder->date->format('Y-m-d') : null;
        // Compute GRN existence status, count, and balance quantities
        $grns = GRN::where('production_order_id', $this->productionOrder->id)->get();
        $this->grnCount = $grns->count();
        $this->hasAnyGRN = $this->grnCount > 0;
        $this->totalBalanceQuantity = $grns->sum(function($grn) {
            return $grn->getBalanceQuantity();
        });
        
        // Determine if all items are completed (for enabling Complete button)
        $this->canComplete = $this->productionOrder->items->every(function ($item) {
            return method_exists($item, 'getRemainingQuantity') ? $item->getRemainingQuantity() <= 0 : (($item->quantity - $item->completed_quantity) <= 0);
        });

        // Load available quantities for GRN generation
        $this->loadAvailableQuantities();
    }

    public function toggleEditMode()
    {
        $this->isEditMode = !$this->isEditMode;
        
        // Initialize item quantities when entering edit mode
        if ($this->isEditMode) {
            foreach ($this->productionOrder->items as $item) {
                $this->itemQuantities[$item->id] = $item->quantity;
            }
        }
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
            
            // Update item quantities if they were changed
            if (!empty($this->itemQuantities)) {
                foreach ($this->itemQuantities as $itemId => $quantity) {
                    $item = $this->productionOrder->items()->find($itemId);
                    if ($item && $item->completed_quantity == 0) { // Only update if nothing completed
                        $item->update(['quantity' => (int) $quantity]);
                    }
                }
            }
            
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

            // Auto-generate GRN for completed quantities (Finished Goods) if none exists yet
            $existingGrns = GRN::where('production_order_id', $this->productionOrder->id)->count();
            if ($existingGrns === 0) {
                $grnNumber = (new \App\Services\ProductionGRNService())->generateGRNNumberForExternalUse ? null : null;
                // Fallback simple number if service method is private; compute here
                $lastGRN = \App\Models\GRN::orderBy('id', 'desc')->first();
                $nextNumber = $lastGRN ? $lastGRN->id + 1 : 1;
                $grnNo = 'GRN-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

                $grn = GRN::create([
                    'production_order_id' => $this->productionOrder->id,
                    'grn_no' => $grnNo,
                    'lot_code' => 'AUTO-LOT-' . now()->format('Ymd') . '-' . $this->productionOrder->id,
                    'received_date' => now()->format('Y-m-d'),
                    'notes' => 'Auto GRN from production completion',
                ]);

                foreach ($this->productionOrder->items as $item) {
                    if (($item->completed_quantity ?? 0) <= 0) continue;
                    \App\Models\GRNItem::create([
                        'grn_id' => $grn->id,
                        'production_order_item_id' => $item->id,
                        'item_type' => $item->item_type,
                        'item_id' => $item->item_id,
                        'description' => ucfirst($item->item_type) . ' finished goods',
                        'material_code' => 'FG-' . ($item->item_id),
                        'qty_received' => (int) $item->completed_quantity,
                        'uom' => 'PCS',
                    ]);
                }

                // DO NOT auto-process to stock - user must manually process from GRN detail page
                // This gives users control over when to process to stock
                
                // Redirect to GRNs page after GRN creation
                session()->flash('success', "GRN {$grnNo} has been created successfully. Please process to stock from the GRN detail page.");
                return $this->redirect(route('grns'), navigate: true);
            }
            
            // Reload the production order to get updated data
            $this->loadProductionOrder();
            
            session()->flash('success', 'Production has been completed successfully!');
            
        } catch (\Exception $e) {
            Log::error('Error completing production: ' . $e->getMessage());
            session()->flash('error', 'Error completing production: ' . $e->getMessage());
        }
    }

    /**
     * Calculate FG quantity from boards quantity
     */
    public function calculateFgFromBoards(int $itemId, int $boardsQty): int
    {
        $item = $this->productionOrder->items()->findOrFail($itemId);
        $noOfUps = $item->getNoOfUps();
        return $noOfUps > 0 ? ($boardsQty * $noOfUps) : $boardsQty;
    }

    /**
     * Calculate boards quantity from FG quantity
     */
    public function calculateBoardsFromFg(int $itemId, int $fgQty): int
    {
        $item = $this->productionOrder->items()->findOrFail($itemId);
        $noOfUps = $item->getNoOfUps();
        return $noOfUps > 0 ? (int) ceil($fgQty / $noOfUps) : $fgQty;
    }

    // Flag to prevent infinite loops in auto-calculation
    private bool $isAutoCalculating = false;

    /**
     * Validate boards quantity (removed auto-fill, kept validation)
     */
    public function updatedCompleteQty($value, $itemId)
    {
        if ($value > 0 && isset($this->completeQty[$itemId])) {
            $item = $this->productionOrder->items()->findOrFail($itemId);
            $effectiveMaxQty = $item->getEffectiveMaxQuantity();
            $maxCanComplete = $effectiveMaxQty - $item->completed_quantity;
            
            // Validate and cap the value at maximum allowed
            if ($value > $maxCanComplete) {
                $this->completeQty[$itemId] = $maxCanComplete;
                session()->flash('error', "Used boards cannot exceed {$maxCanComplete} boards (remaining quantity).");
                return;
            }
        }
    }

    /**
     * Validate FG quantity (removed auto-fill, kept validation)
     */
    public function updatedCompleteFgQty($value, $itemId)
    {
        if ($value > 0 && isset($this->completeFgQty[$itemId])) {
            $item = $this->productionOrder->items()->findOrFail($itemId);
            $effectiveMaxQty = $item->getEffectiveMaxQuantity();
            $maxCanComplete = $effectiveMaxQty - $item->completed_quantity;
            $noOfUps = $item->getNoOfUps();
            $maxFgCanProduce = $noOfUps > 0 ? ($maxCanComplete * $noOfUps) : $maxCanComplete;
            
            // Validate and cap the FG value at maximum allowed
            if ($value > $maxFgCanProduce) {
                $this->completeFgQty[$itemId] = $maxFgCanProduce;
                session()->flash('error', "FG produced cannot exceed {$maxFgCanProduce} boxes (remaining capacity).");
                return;
            }
        }
    }

    /**
     * Show confirmation modal before completing item
     */
    public function completeItemQuantity(int $itemId): void
    {
        $item = $this->productionOrder->items()->findOrFail($itemId);
        $usedBoards = (int)($this->completeQty[$itemId] ?? 0);
        $fgProduced = (int)($this->completeFgQty[$itemId] ?? 0);

        // Validate that at least one value is provided
        if ($usedBoards <= 0 && $fgProduced <= 0) {
            session()->flash('error', 'Please enter either Used Boards or FG Produced quantity.');
            return;
        }

        // If only one value is provided, calculate the other
        if ($usedBoards > 0 && $fgProduced <= 0) {
            $fgProduced = $this->calculateFgFromBoards($itemId, $usedBoards);
            $this->completeFgQty[$itemId] = $fgProduced;
        } elseif ($fgProduced > 0 && $usedBoards <= 0) {
            $usedBoards = $this->calculateBoardsFromFg($itemId, $fgProduced);
            $this->completeQty[$itemId] = $usedBoards;
        }

        // Validate against maximum allowed
        $effectiveMaxQty = $item->getEffectiveMaxQuantity();
        $maxCanComplete = $effectiveMaxQty - $item->completed_quantity;

        if ($usedBoards > $maxCanComplete) {
            $expectedFromMaterial = $item->getExpectedFinishedGoodsFromMaterial();
            $jobOrderOrderQty = $item->getJobOrderOrderQuantity();
            session()->flash('error', "Used boards quantity exceeds the maximum allowed. Maximum remaining: {$maxCanComplete} boards (Effective Max: {$effectiveMaxQty}, Expected from Material: {$expectedFromMaterial}, Job Order Qty: {$jobOrderOrderQty}).");
            return;
        }

        // Store pending completion details
        $this->pendingCompleteItemId = $itemId;
        $this->pendingCompleteQty = $usedBoards;
        $this->bypassGRN = false;
        $this->showCompleteConfirmModal = true;
    }

    /**
     * Close completion confirmation modal
     */
    public function closeCompleteConfirmModal()
    {
        $this->showCompleteConfirmModal = false;
        $this->pendingCompleteItemId = null;
        $this->pendingCompleteQty = 0;
        $this->bypassGRN = false;
    }

    /**
     * Confirm and complete item quantity
     */
    public function confirmCompleteItemQuantity()
    {
        try {
            if (!$this->pendingCompleteItemId || $this->pendingCompleteQty <= 0) {
                session()->flash('error', 'Invalid completion request.');
                $this->closeCompleteConfirmModal();
                return;
            }

            $item = $this->productionOrder->items()->findOrFail($this->pendingCompleteItemId);
            $usedBoards = $this->pendingCompleteQty;
            $fgProduced = (int)($this->completeFgQty[$this->pendingCompleteItemId] ?? 0);
            
            // If FG not provided, calculate from boards
            if ($fgProduced <= 0) {
                $fgProduced = $this->calculateFgFromBoards($this->pendingCompleteItemId, $usedBoards);
            }

            // Check if already reached job order's order quantity limit
            if ($item->hasReachedJobOrderLimit()) {
                session()->flash('error', 'Cannot complete more items. The completed quantity has reached the job order\'s order quantity limit.');
                $this->closeCompleteConfirmModal();
                return;
            }

            // Get effective maximum quantity (min of expected from material and job order order qty)
            $effectiveMaxQty = $item->getEffectiveMaxQuantity();
            $maxCanComplete = $effectiveMaxQty - $item->completed_quantity;

            if ($usedBoards > $maxCanComplete) {
                $expectedFromMaterial = $item->getExpectedFinishedGoodsFromMaterial();
                $jobOrderOrderQty = $item->getJobOrderOrderQuantity();
                session()->flash('error', "Used boards quantity exceeds the maximum allowed. Maximum remaining: {$maxCanComplete} boards (Effective Max: {$effectiveMaxQty}, Expected from Material: {$expectedFromMaterial}, Job Order Qty: {$jobOrderOrderQty}).");
                $this->closeCompleteConfirmModal();
                return;
            }

            // Calculate waste/conserve
            // Expected boards from FG: FG Produced ÷ No of UPS
            $noOfUps = $item->getNoOfUps();
            $expectedBoardsFromFg = $noOfUps > 0 ? (int) ceil($fgProduced / $noOfUps) : $fgProduced;
            $wasteQuantity = $usedBoards - $expectedBoardsFromFg; // Positive = waste, Negative = conserve

            // Increment completed quantity (boards)
            $item->increment('completed_quantity', $usedBoards);
            
            // Update finished goods quantity
            $currentFgQty = $item->finished_goods_quantity ?? 0;
            $item->update([
                'finished_goods_quantity' => $currentFgQty + $fgProduced,
                'waste_quantity' => ($item->waste_quantity ?? 0) + $wasteQuantity,
            ]);

            // Update item status
            $item->refresh();
            $isFullyCompleted = $item->getRemainingQuantity() <= 0;
            if ($isFullyCompleted) {
                $item->update(['status' => 'completed']);
            } else {
                $item->update(['status' => 'in_production']);
            }

            // Ensure order status reflects progress
            if ($this->productionOrder->status === 'pending') {
                $this->productionOrder->update(['status' => 'in_production']);
            }

            // If all items completed, mark order completed
            $this->productionOrder->refresh();
            $allCompleted = $this->productionOrder->items->every(function ($i) {
                return $i->getRemainingQuantity() <= 0;
            });
            if ($allCompleted) {
                $this->productionOrder->update(['status' => 'completed']);
                Log::info('Production order marked as completed', [
                    'production_order_id' => $this->productionOrder->id,
                    'production_order_number' => $this->productionOrder->production_order_number
                ]);
            }

            // Record inventory transaction for raw materials consumed
            $this->recordRawMaterialConsumption($usedBoards);

            // Always create a GRN when completing items
            // If bypassing GRN, create and process GRN automatically
            // If not bypassing, create GRN as pending for manual processing
            // Note: For FG GRNs, we use finished_goods_quantity (boxes), not board quantity
            if ($this->bypassGRN) {
                $this->createAndProcessGRNForCompletedQuantity($item, $fgProduced);
            } else {
                $this->createPendingGRNForCompletedQuantity($item, $fgProduced);
            }

            // Reload view data
            $this->loadProductionOrder();
            // Clear inputs for item
            $this->completeQty[$this->pendingCompleteItemId] = 0;
            $this->completeFgQty[$this->pendingCompleteItemId] = 0;

            $wasteMessage = '';
            if ($wasteQuantity > 0) {
                $wasteMessage = " Waste: {$wasteQuantity} boards.";
            } elseif ($wasteQuantity < 0) {
                $conserveQty = abs($wasteQuantity);
                $wasteMessage = " Conserve: {$conserveQty} boards.";
            }

            $message = "Recorded completion: {$usedBoards} boards used, {$fgProduced} boxes produced.{$wasteMessage}";
            if ($this->bypassGRN) {
                $message .= " GRN created and processed to stock automatically.";
            } else {
                $message .= " GRN created as pending. Please process to stock manually.";
            }
            
            session()->flash('success', $message);
            $this->closeCompleteConfirmModal();
            
            // Dispatch refresh event to update inventory dashboard if it's open
            $this->dispatch('production-order-completed', [
                'production_order_id' => $this->productionOrder->id,
                'transaction_id' => $this->extractTransactionIdFromNotes()
            ]);
        } catch (\Exception $e) {
            Log::error('Error completing production item: ' . $e->getMessage());
            session()->flash('error', 'Error completing production item: ' . $e->getMessage());
            $this->closeCompleteConfirmModal();
        }
    }

    /**
     * Record raw material consumption as inventory transaction
     */
    protected function recordRawMaterialConsumption($usedBoards)
    {
        try {
            // Extract transaction ID from production order notes
            // Format: "Production order created from inventory transaction: {lot_code} | Transaction ID: {id}"
            $originalTransactionId = $this->extractTransactionIdFromNotes();
            
            if (!$originalTransactionId) {
                Log::warning('Could not extract transaction ID from production order notes', [
                    'production_order_id' => $this->productionOrder->id,
                    'notes' => $this->productionOrder->notes
                ]);
                return;
            }

            // Get the original inventory transaction
            $originalTransaction = InventoryTransaction::find($originalTransactionId);
            
            if (!$originalTransaction) {
                Log::warning('Original inventory transaction not found', [
                    'transaction_id' => $originalTransactionId,
                    'production_order_id' => $this->productionOrder->id
                ]);
                return;
            }

            // Get inventory service
            $inventoryService = app(InventoryService::class);

            // Create consume transaction for raw materials
            $inventoryService->recordTransaction([
                'lot_code' => $originalTransaction->lot_code,
                'item_code' => $originalTransaction->item_code ?? 'RAW',
                'category' => 'RAW',
                'material_type' => $originalTransaction->material_type ?? 'raw_material',
                'txn_type' => 'consume',
                'qty' => $usedBoards,
                'uom' => $originalTransaction->uom ?? 'PCS',
                'warehouse' => $originalTransaction->warehouse ?? 'MAIN',
                'related_doc_type' => 'ProductionOrder',
                'related_doc_id' => $this->productionOrder->id,
                'txn_date' => now()->toDateString(),
                'remarks' => "Raw materials consumed for production order {$this->productionOrder->production_order_number}",
            ]);

        } catch (\Exception $e) {
            Log::error('Error recording raw material consumption: ' . $e->getMessage(), [
                'production_order_id' => $this->productionOrder->id,
                'used_boards' => $usedBoards,
                'error' => $e->getMessage()
            ]);
            // Don't throw - allow production to complete even if inventory transaction fails
        }
    }

    /**
     * Extract transaction ID from production order notes
     * Format: "Production order created from inventory transaction: {lot_code} | Transaction ID: {id}"
     */
    protected function extractTransactionIdFromNotes()
    {
        if (!$this->productionOrder || !$this->productionOrder->notes) {
            return null;
        }

        $notes = $this->productionOrder->notes;
        
        // Try to extract transaction ID using regex
        // Pattern: "Transaction ID: {id}"
        if (preg_match('/Transaction ID:\s*(\d+)/i', $notes, $matches)) {
            return (int) $matches[1];
        }

        // Fallback: Try to extract lot_code from notes and find transaction by lot_code
        // Format: "Production order created from inventory transaction: {lot_code}"
        if (preg_match('/Production order created from inventory transaction:\s*([^\s|]+)/i', $notes, $matches)) {
            $lotCode = trim($matches[1]);
            // Find the most recent receipt transaction with this lot_code
            $transaction = InventoryTransaction::where('lot_code', $lotCode)
                ->where('category', 'RAW')
                ->where('txn_type', 'receipt')
                ->orderBy('txn_date', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            
            if ($transaction) {
                return $transaction->id;
            }
        }

        return null;
    }

    /**
     * Create pending GRN for completed quantity (requires manual processing)
     * For FG GRNs, $completedQty should be finished_goods_quantity (boxes), not board quantity
     */
    protected function createPendingGRNForCompletedQuantity($item, $completedQty)
    {
        try {
            if ($completedQty <= 0) {
                return;
            }

            // For FG GRNs, check how much finished goods have already been GRN'd
            $alreadyGRNQty = \App\Models\GRNItem::where('production_order_item_id', $item->id)
                ->sum('qty_received');
            
            // Calculate how much is available to GRN (current finished_goods_quantity - already GRN'd)
            // Use finished_goods_quantity for FG GRNs, not completed_quantity (boards)
            $currentFgQty = $item->finished_goods_quantity ?? 0;
            $availableToGRN = $currentFgQty - $alreadyGRNQty;
            
            if ($availableToGRN <= 0) {
                // Already GRN'd, nothing to do
                return;
            }

            // Create new GRN for the available quantity with pending status
            $lastGRN = \App\Models\GRN::orderBy('id', 'desc')->first();
            $nextNumber = $lastGRN ? $lastGRN->id + 1 : 1;
            $grnNo = 'GRN-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

            $grn = GRN::create([
                'production_order_id' => $this->productionOrder->id,
                'grn_no' => $grnNo,
                'lot_code' => 'LOT-' . now()->format('Ymd') . '-' . $this->productionOrder->production_order_number,
                'received_date' => now()->format('Y-m-d'),
                'status' => 'pending',
                'notes' => 'Auto-generated GRN from production completion (requires manual processing)',
            ]);

            // Calculate expected quantity from production order (production order quantity × No of UPS)
            $noOfUps = $item->getNoOfUps();
            $expectedFgQty = $noOfUps > 0 ? ($item->quantity * $noOfUps) : $item->quantity;
            
            // Create GRN item with available quantity (FG quantity in boxes)
            $grnItem = \App\Models\GRNItem::create([
                'grn_id' => $grn->id,
                'production_order_item_id' => $item->id,
                'item_type' => $item->item_type,
                'item_id' => $item->item_id,
                'description' => ucfirst($item->item_type) . ' finished goods',
                'material_code' => $item->getItemCode(),
                'qty_received' => $availableToGRN, // Actual FG quantity produced (boxes)
                'qty_expected' => $expectedFgQty, // Expected FG quantity from production order (boxes)
                'uom' => 'PCS',
            ]);

            // Initialize partial receiving and mark as fully received
            $grnItem->initializePartialReceiving();
            $grnItem->update([
                'qty_received_partial' => $availableToGRN,
                'qty_expected' => $expectedFgQty,
                'qty_pending' => max(0, $expectedFgQty - $availableToGRN), // Pending = Expected - Received
                'last_received_at' => now(),
            ]);
            // Sync receiving status to ensure is_fully_received is correctly calculated
            $grnItem->syncReceivingStatus();
            $grnItem->save();

            Log::info('GRN created as pending for manual processing', [
                'grn_id' => $grn->id,
                'grn_no' => $grn->grn_no,
                'quantity' => $availableToGRN
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating pending GRN: ' . $e->getMessage());
            // Don't fail the completion if GRN creation fails
            session()->flash('warning', 'Item completed but GRN creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Create and process GRN automatically for completed quantity
     * For FG GRNs, $completedQty should be finished_goods_quantity (boxes), not board quantity
     */
    protected function createAndProcessGRNForCompletedQuantity($item, $completedQty)
    {
        try {
            if ($completedQty <= 0) {
                return;
            }

            // For FG GRNs, check how much finished goods have already been GRN'd
            $alreadyGRNQty = \App\Models\GRNItem::where('production_order_item_id', $item->id)
                ->sum('qty_received');
            
            // Calculate how much is available to GRN (current finished_goods_quantity - already GRN'd)
            // Use finished_goods_quantity for FG GRNs, not completed_quantity (boards)
            $currentFgQty = $item->finished_goods_quantity ?? 0;
            $availableToGRN = $currentFgQty - $alreadyGRNQty;
            
            if ($availableToGRN <= 0) {
                // Already GRN'd, check if needs processing
                $existingGRNItem = \App\Models\GRNItem::where('production_order_item_id', $item->id)
                    ->whereHas('grn', function($query) {
                        $query->where('status', '!=', 'processed');
                    })
                    ->first();
                
                if ($existingGRNItem) {
                    $grn = $existingGRNItem->grn;
                    $grnProcessingService = app(\App\Services\GRNProcessingService::class);
                    $costingMethod = \App\Models\SystemConfiguration::getValue('grn_default_costing_method', 'FIFO');
                    $result = $grnProcessingService->processGRNToStock($grn, $costingMethod);
                    
                    if ($result['success']) {
                        Log::info('Existing GRN auto-processed to stock', [
                            'grn_id' => $grn->id,
                            'grn_no' => $grn->grn_no
                        ]);
                    }
                }
                return;
            }

            // Create new GRN for the available quantity
            $lastGRN = \App\Models\GRN::orderBy('id', 'desc')->first();
            $nextNumber = $lastGRN ? $lastGRN->id + 1 : 1;
            $grnNo = 'GRN-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

            $grn = GRN::create([
                'production_order_id' => $this->productionOrder->id,
                'grn_no' => $grnNo,
                'lot_code' => 'LOT-' . now()->format('Ymd') . '-' . $this->productionOrder->production_order_number,
                'received_date' => now()->format('Y-m-d'),
                'status' => 'pending',
                'notes' => 'Auto-generated GRN from production completion (bypassed manual processing)',
            ]);

            // Calculate expected quantity from production order (production order quantity × No of UPS)
            $noOfUps = $item->getNoOfUps();
            $expectedFgQty = $noOfUps > 0 ? ($item->quantity * $noOfUps) : $item->quantity;
            
            // Create GRN item with available quantity (FG quantity in boxes)
            $grnItem = \App\Models\GRNItem::create([
                'grn_id' => $grn->id,
                'production_order_item_id' => $item->id,
                'item_type' => $item->item_type,
                'item_id' => $item->item_id,
                'description' => ucfirst($item->item_type) . ' finished goods',
                'material_code' => $item->getItemCode(),
                'qty_received' => $availableToGRN, // Actual FG quantity produced (boxes)
                'qty_expected' => $expectedFgQty, // Expected FG quantity from production order (boxes)
                'uom' => 'PCS',
            ]);

            // Initialize partial receiving and mark as fully received
            $grnItem->initializePartialReceiving();
            $grnItem->update([
                'qty_received_partial' => $availableToGRN,
                'qty_expected' => $expectedFgQty,
                'qty_pending' => max(0, $expectedFgQty - $availableToGRN), // Pending = Expected - Received
                'last_received_at' => now(),
            ]);
            // Sync receiving status to ensure is_fully_received is correctly calculated
            $grnItem->syncReceivingStatus();
            $grnItem->save();

            // Process GRN to stock automatically
            $grnProcessingService = app(\App\Services\GRNProcessingService::class);
            $costingMethod = \App\Models\SystemConfiguration::getValue('grn_default_costing_method', 'FIFO');
            $result = $grnProcessingService->processGRNToStock($grn, $costingMethod);

            if ($result['success']) {
                // Refresh GRN and items to ensure data is up to date
                $grn->refresh();
                $grn->load('items');
                
                Log::info('GRN auto-created and processed to stock', [
                    'grn_id' => $grn->id,
                    'grn_no' => $grn->grn_no,
                    'quantity' => $availableToGRN,
                    'status' => $grn->status,
                    'has_unprocessed_items' => $grn->hasUnprocessedItems()
                ]);
            } else {
                throw new \Exception('Failed to process GRN to stock: ' . ($result['message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            Log::error('Error creating/processing GRN automatically: ' . $e->getMessage());
            // Don't fail the completion if GRN processing fails
            session()->flash('warning', 'Item completed but GRN processing failed: ' . $e->getMessage());
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

    /**
     * Generate a GRN from the completed quantities as Finished Goods
     * Note: Does NOT auto-process to stock - user must manually process from GRN detail page
     */
    public function generateFGGRNFromCompleted()
    {
        try {
            if (!$this->productionOrder) {
                session()->flash('error', 'Production order not found.');
                return;
            }

            // Check if GRN already exists for this production order
            if ($this->hasAnyGRN) {
                session()->flash('error', 'A GRN has already been created for this production order. Cannot create duplicate GRNs.');
                return;
            }

            // Build item -> remaining-to-GRN map based on finished_goods_quantity minus already GRN qty
            // For FG GRNs, we use finished_goods_quantity (boxes), not completed_quantity (boards)
            $itemQuantities = [];
            foreach ($this->productionOrder->items as $item) {
                $completedFg = (int) ($item->finished_goods_quantity ?? 0);
                if ($completedFg <= 0) continue;
                $alreadyGRN = \App\Models\GRNItem::where('production_order_item_id', $item->id)->sum('qty_received');
                $remaining = max(0, $completedFg - (int) $alreadyGRN);
                if ($remaining > 0) {
                    $itemQuantities[$item->id] = $remaining;
                }
            }

            if (empty($itemQuantities)) {
                session()->flash('error', 'No completed quantities available to GRN.');
                return;
            }

            // Create GRN manually (we already have completed qty; bypass service validations)
            $lastGRN = \App\Models\GRN::orderBy('id', 'desc')->first();
            $nextNumber = $lastGRN ? $lastGRN->id + 1 : 1;
            $grnNo = 'GRN-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

            $grn = \App\Models\GRN::create([
                'production_order_id' => $this->productionOrder->id,
                'grn_no' => $grnNo,
                'lot_code' => 'LOT-' . now()->format('Ymd') . '-' . $this->productionOrder->production_order_number,
                'received_date' => now()->format('Y-m-d'),
                'notes' => 'Finished goods from completed quantities',
            ]);

            foreach ($this->productionOrder->items as $item) {
                // For FG GRNs, use finished_goods_quantity (boxes), not completed_quantity (boards)
                $completedFg = (int) ($item->finished_goods_quantity ?? 0);
                $alreadyGRN = \App\Models\GRNItem::where('production_order_item_id', $item->id)->sum('qty_received');
                $qty = max(0, $completedFg - (int) $alreadyGRN);
                if ($qty <= 0) continue;

                // Calculate expected quantity from production order (production order quantity × No of UPS)
                $noOfUps = $item->getNoOfUps();
                $expectedFgQty = $noOfUps > 0 ? ($item->quantity * $noOfUps) : $item->quantity;

                $grnItem = \App\Models\GRNItem::create([
                    'grn_id' => $grn->id,
                    'production_order_item_id' => $item->id,
                    'item_type' => $item->item_type,
                    'item_id' => $item->item_id,
                    'description' => ucfirst($item->item_type) . ' finished goods',
                    'material_code' => $item->getItemCode(),
                    'qty_received' => $qty, // Actual FG quantity produced (boxes)
                    'qty_expected' => $expectedFgQty, // Expected FG quantity from production order (boxes)
                    'qty_received_partial' => $qty,
                    'qty_pending' => $expectedFgQty - $qty, // Pending = Expected - Received
                    'is_fully_received' => ($qty >= $expectedFgQty),
                    'uom' => 'PCS',
                ]);
            }

            // DO NOT auto-process to stock - user must manually process from GRN detail page
            // This gives users control over when to process to stock

            session()->flash('success', "GRN {$grn->grn_no} generated from completed quantities. Please process to stock from the GRN detail page.");
            
            // Redirect to GRNs page
            return $this->redirect(route('grns'), navigate: true);
        } catch (\Exception $e) {
            \Log::error('Error generating FG GRN from completed qty: ' . $e->getMessage());
            session()->flash('error', 'Error generating GRN: ' . $e->getMessage());
        }
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

            $this->closeGRNModal();
            
            session()->flash('success', "GRN {$grn->grn_no} generated successfully for {$this->grnQuantity} items!");
            
            // Redirect to GRNs page
            return $this->redirect(route('grns'), navigate: true);
            
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
        return view('livewire.production-orders.production-order-detail', [
            'suppliers' => Supplier::all(),
            'customers' => Customer::all(),
        ]);
    }
}
