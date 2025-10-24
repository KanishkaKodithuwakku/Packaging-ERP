<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PurchaseOrder;
use App\Models\JobOrder;
use App\Models\Supplier;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use Illuminate\Support\Facades\Log;

class PurchaseOrderManagement extends Component
{
    public $purchaseOrders = [];
    public $showCreateModal = false;
    public $showViewModal = false;
    public $showPhoneConfirmModal = false;
    public $showPhoneConfirmConfirmModal = false;
    public $showGRNConfirmModal = false;
    public $showCancelConfirmModal = false;
    public $selectedPurchaseOrder = null;
    public $cancellationReason = '';
    public $redirectToProductionOrder = null;
    public $selectedJobOrderId = null;
    public $selectedJobOrder = null;
    public $form = [];
    public $phoneConfirmForm = [];

    protected $rules = [
        'form.supplier_id' => 'required',
        'form.job_order_id' => 'required',
        'form.date' => 'required|date',
        'form.notes' => 'nullable|string',
    ];

    public function mount()
    {
        $this->loadPurchaseOrders();
        $this->resetForm();
        
        // Pre-fill job order if passed in URL but don't auto-open modal
        $jobOrderId = request()->query('job_order');
        if ($jobOrderId) {
            $this->selectedJobOrderId = $jobOrderId;
            $this->selectedJobOrder = JobOrder::with(['supplier', 'customer', 'boxes', 'dividers'])->find($jobOrderId);
            
            if ($this->selectedJobOrder) {
                $this->form['job_order_id'] = $jobOrderId;
                $this->form['supplier_id'] = $this->selectedJobOrder->supplier_id;
                // Don't auto-open the modal - let user click the button
            }
        }
    }

    public function loadPurchaseOrders()
    {
        $this->purchaseOrders = PurchaseOrder::with(['supplier', 'jobOrder', 'items', 'grn'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function resetForm()
    {
        $this->form = [
            'supplier_id' => '',
            'job_order_id' => '',
            'date' => now()->toDateString(),
            'notes' => '',
        ];
        $this->selectedJobOrderId = null;
        $this->selectedJobOrder = null;
    }

    public function openCreateModal($jobOrderId = null)
    {
        // Redirect to the new dedicated creation page
        if ($jobOrderId) {
            return redirect()->route('create-purchase-order', ['job_order' => $jobOrderId]);
        }
        return redirect()->route('create-purchase-order');
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function viewPurchaseOrder($id)
    {
        $this->selectedPurchaseOrder = PurchaseOrder::with(['supplier', 'jobOrder', 'items'])->find($id);
        if ($this->selectedPurchaseOrder) {
            $this->showViewModal = true;
        } else {
            session()->flash('error', 'Purchase order not found.');
        }
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->selectedPurchaseOrder = null;
    }

    public function editPurchaseOrder($id)
    {
        // For now, redirect to a simple edit page or show edit modal
        // You can expand this later to include full editing functionality
        $purchaseOrder = PurchaseOrder::find($id);
        if ($purchaseOrder) {
            session()->flash('info', 'Edit functionality will be implemented in the next phase.');
            Log::info('Edit purchase order requested', ['po_id' => $id, 'po_number' => $purchaseOrder->po_number]);
        } else {
            session()->flash('error', 'Purchase order not found.');
        }
    }

    public function downloadPurchaseOrder($id)
    {
        // For now, just show a message - PDF generation can be implemented later
        $purchaseOrder = PurchaseOrder::find($id);
        if ($purchaseOrder) {
            session()->flash('info', 'PDF download functionality will be implemented in the next phase.');
            Log::info('Download purchase order requested', ['po_id' => $id, 'po_number' => $purchaseOrder->po_number]);
        } else {
            session()->flash('error', 'Purchase order not found.');
        }
    }

    public function openGRNConfirmModal($id)
    {
        $this->selectedPurchaseOrder = PurchaseOrder::with(['items', 'jobOrder'])->find($id);
        
        if (!$this->selectedPurchaseOrder) {
            session()->flash('error', 'Purchase order not found.');
            return;
        }

        if ($this->selectedPurchaseOrder->status !== 'confirmed') {
            session()->flash('error', 'Only confirmed purchase orders can be used to create GRNs.');
            return;
        }

        if ($this->selectedPurchaseOrder->items->isEmpty()) {
            session()->flash('error', 'Purchase order has no items to create GRN from.');
            return;
        }

        // Check if GRN already exists for this purchase order
        $existingGRN = \App\Models\GRN::where('purchase_order_id', $this->selectedPurchaseOrder->id)->first();
        if ($existingGRN) {
            session()->flash('error', "A GRN ({$existingGRN->grn_no}) already exists for this Purchase Order. Cannot create multiple GRNs for the same Purchase Order.");
            return;
        }

        $this->showGRNConfirmModal = true;
    }

    public function closeGRNConfirmModal()
    {
        $this->showGRNConfirmModal = false;
        $this->selectedPurchaseOrder = null;
    }

    public function openCancelConfirmModal($id)
    {
        $this->selectedPurchaseOrder = PurchaseOrder::with(['grn', 'grn.items'])->find($id);
        
        if (!$this->selectedPurchaseOrder) {
            session()->flash('error', 'Purchase order not found.');
            return;
        }

        if ($this->selectedPurchaseOrder->status === 'cancelled') {
            session()->flash('error', 'Purchase order is already cancelled.');
            return;
        }

        $this->showCancelConfirmModal = true;
    }

    public function closeCancelConfirmModal()
    {
        $this->showCancelConfirmModal = false;
        $this->selectedPurchaseOrder = null;
        $this->cancellationReason = '';
    }

    public function createGRNFromPurchaseOrder($id)
    {
        try {
            $purchaseOrder = PurchaseOrder::with(['items', 'jobOrder'])->find($id);
            
            if (!$purchaseOrder) {
                session()->flash('error', 'Purchase order not found.');
                return;
            }

            if ($purchaseOrder->status !== 'confirmed') {
                session()->flash('error', 'Only confirmed purchase orders can be used to create GRNs.');
                return;
            }

            if ($purchaseOrder->items->isEmpty()) {
                session()->flash('error', 'Purchase order has no items to create GRN from.');
                return;
            }

            // Check if GRN already exists for this purchase order
            $existingGRN = \App\Models\GRN::where('purchase_order_id', $purchaseOrder->id)->first();
            if ($existingGRN) {
                session()->flash('error', "A GRN ({$existingGRN->grn_no}) already exists for this Purchase Order. Cannot create multiple GRNs for the same Purchase Order.");
                return;
            }

            // Generate GRN number
            $grnNumber = $this->generateGRNNumber();
            
            // Create GRN
            $grn = \App\Models\GRN::create([
                'purchase_order_id' => $purchaseOrder->id,
                'grn_no' => $grnNumber,
                'lot_code' => $this->generateLotCode($purchaseOrder),
                'received_date' => now()->format('Y-m-d'),
                'notes' => "GRN created from Purchase Order {$purchaseOrder->po_number}",
                'status' => 'pending',
            ]);

            // Create GRN items from purchase order items
            foreach ($purchaseOrder->items as $poItem) {
                $grnItem = \App\Models\GRNItem::create([
                    'grn_id' => $grn->id,
                    'item_type' => $poItem->item_type,
                    'item_id' => $poItem->item_id,
                    'description' => $this->generateItemDescription($poItem),
                    'material_code' => $this->generateMaterialCode($poItem),
                    'qty_received' => $poItem->quantity,
                    'qty_processed' => 0,
                    'qty_remaining' => $poItem->quantity,
                    'uom' => 'PCS',
                    'unit_cost' => $poItem->unit_price,
                    'total_cost' => $poItem->unit_price * $poItem->quantity,
                ]);

                // Initialize partial receiving fields
                $grnItem->initializePartialReceiving();
            }

            session()->flash('success', "GRN {$grnNumber} has been created successfully from Purchase Order {$purchaseOrder->po_number}.");
            Log::info('GRN created from purchase order', [
                'grn_id' => $grn->id,
                'grn_number' => $grnNumber,
                'po_id' => $purchaseOrder->id,
                'po_number' => $purchaseOrder->po_number
            ]);

            // Close modal and refresh data
            $this->closeGRNConfirmModal();
            $this->loadPurchaseOrders();

        } catch (\Exception $e) {
            session()->flash('error', 'Failed to create GRN: ' . $e->getMessage());
            Log::error('Failed to create GRN from purchase order', [
                'po_id' => $id,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function cancelPurchaseOrder($id)
    {
        try {
            $purchaseOrder = PurchaseOrder::with(['grn', 'grn.items', 'items'])->find($id);
            
            if (!$purchaseOrder) {
                session()->flash('error', 'Purchase order not found.');
                return;
            }

            if ($purchaseOrder->status === 'cancelled') {
                session()->flash('error', 'Purchase order is already cancelled.');
                return;
            }

            // Start database transaction
            \DB::beginTransaction();

            // If GRN exists, reverse the quantities from inventory
            if ($purchaseOrder->grn->isNotEmpty()) {
                $grn = $purchaseOrder->grn->first();
                
                Log::info('Cancelling Purchase Order with GRN', [
                    'po_id' => $purchaseOrder->id,
                    'po_number' => $purchaseOrder->po_number,
                    'grn_id' => $grn->id,
                    'grn_number' => $grn->grn_no
                ]);

                // Reverse inventory transactions for each GRN item
                foreach ($grn->items as $grnItem) {
                    if ($grnItem->qty_processed > 0) {
                        // Create reverse inventory transaction
                        \App\Models\InventoryTransaction::create([
                            'item_type' => $grnItem->item_type,
                            'item_id' => $grnItem->item_id,
                            'transaction_type' => 'cancellation',
                            'quantity' => -$grnItem->qty_processed, // Negative quantity to reverse
                            'unit_cost' => $grnItem->unit_cost,
                            'total_cost' => -($grnItem->unit_cost * $grnItem->qty_processed),
                            'lot_code' => $grn->lot_code,
                            'reference' => "PO Cancellation - {$purchaseOrder->po_number}",
                            'notes' => "Reversed from cancelled GRN {$grn->grn_no}",
                            'created_by' => auth()->id(),
                        ]);

                        Log::info('Reversed inventory for GRN item', [
                            'grn_item_id' => $grnItem->id,
                            'item_type' => $grnItem->item_type,
                            'item_id' => $grnItem->item_id,
                            'reversed_quantity' => $grnItem->qty_processed,
                            'lot_code' => $grn->lot_code
                        ]);
                    }
                }

                // Update GRN status to cancelled
                $grn->update([
                    'status' => 'cancelled',
                    'notes' => $grn->notes . " [CANCELLED - PO {$purchaseOrder->po_number} cancelled]"
                ]);

                Log::info('GRN cancelled', [
                    'grn_id' => $grn->id,
                    'grn_number' => $grn->grn_no
                ]);
            }

            // Update purchase order status to cancelled
            $purchaseOrder->update([
                'status' => 'cancelled',
                'cancellation_reason' => $this->cancellationReason ?: 'No reason provided',
                'cancelled_at' => now(),
                'cancelled_by' => auth()->id(),
                'notes' => $purchaseOrder->notes . " [CANCELLED on " . now()->format('Y-m-d H:i:s') . "]"
            ]);

            // IMPORTANT: Do NOT delete purchase order items - this makes items available again
            // The items will automatically become available because calculateRemainingQuantity 
            // will see the cancelled PO and not count it against available quantities

            // Commit transaction
            \DB::commit();

            // Close modal and refresh data
            $this->closeCancelConfirmModal();
            $this->loadPurchaseOrders();

            $message = "Purchase Order {$purchaseOrder->po_number} has been cancelled successfully.";
            if ($purchaseOrder->grn->isNotEmpty()) {
                $message .= " All received quantities have been reversed from inventory.";
            }
            $message .= " Items are now available for new purchase orders.";

            session()->flash('success', $message);

            Log::info('Purchase Order cancelled successfully', [
                'po_id' => $purchaseOrder->id,
                'po_number' => $purchaseOrder->po_number,
                'had_grn' => $purchaseOrder->grn->isNotEmpty()
            ]);

        } catch (\Exception $e) {
            // Rollback transaction on error
            \DB::rollback();
            
            session()->flash('error', 'Failed to cancel purchase order: ' . $e->getMessage());
            Log::error('Failed to cancel purchase order', [
                'po_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    private function generateGRNNumber()
    {
        $lastGRN = \App\Models\GRN::orderBy('id', 'desc')->first();
        $nextNumber = $lastGRN ? (intval(substr($lastGRN->grn_no, 4)) + 1) : 1;
        return 'GRN-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    private function generateLotCode($purchaseOrder)
    {
        $date = now()->format('Ymd');
        $poNumber = str_replace('PO-', '', $purchaseOrder->po_number);
        return "LOT-{$date}-{$poNumber}";
    }

    private function generateItemDescription($poItem)
    {
        if ($poItem->item_type === 'box') {
            $box = \App\Models\JobOrderBox::find($poItem->item_id);
            return $box ? "Box - {$box->length}x{$box->width}x{$box->height} {$box->unit}" : "Box Item";
        } else {
            $divider = \App\Models\JobOrderDivider::find($poItem->item_id);
            return $divider ? "Divider - {$divider->length}x{$divider->width}x{$divider->height} {$divider->unit}" : "Divider Item";
        }
    }

    private function generateMaterialCode($poItem)
    {
        $itemType = strtoupper(substr($poItem->item_type, 0, 1));
        return "MAT-{$itemType}-" . str_pad($poItem->item_id, 4, '0', STR_PAD_LEFT);
    }


    public function confirmPurchaseOrder($id)
    {
        $purchaseOrder = PurchaseOrder::find($id);
        if ($purchaseOrder) {
            $purchaseOrder->update(['status' => 'confirmed']);
            session()->flash('success', "Purchase Order {$purchaseOrder->po_number} has been confirmed and is ready for production.");
            $this->loadPurchaseOrders();
            Log::info('Purchase Order confirmed', ['po_id' => $id, 'po_number' => $purchaseOrder->po_number]);
        } else {
            session()->flash('error', 'Purchase order not found.');
        }
    }


    public function openPhoneConfirmModal($id)
    {
        $this->selectedPurchaseOrder = PurchaseOrder::with(['items'])->find($id);
        if ($this->selectedPurchaseOrder) {
            // Initialize phone confirm form with current item prices
            $this->phoneConfirmForm = [];
            foreach ($this->selectedPurchaseOrder->items as $item) {
                $this->phoneConfirmForm["item_{$item->id}_unit_price"] = $item->unit_price;
            }
            $this->showPhoneConfirmModal = true;
        } else {
            session()->flash('error', 'Purchase order not found.');
        }
    }

    public function closePhoneConfirmModal()
    {
        $this->showPhoneConfirmModal = false;
        $this->selectedPurchaseOrder = null;
        $this->phoneConfirmForm = [];
    }

    public function showPhoneConfirmConfirmModal()
    {
        $this->showPhoneConfirmConfirmModal = true;
    }

    public function closePhoneConfirmConfirmModal()
    {
        $this->showPhoneConfirmConfirmModal = false;
    }

    public function testModal()
    {
        Log::info('Confirm Over button clicked - showing confirmation modal');
        $this->showPhoneConfirmConfirmModal = true;
        Log::info('Confirmation modal set to true');
    }

    public function confirmPurchaseOrderOverPhoneFinal()
    {
        Log::info('confirmPurchaseOrderOverPhoneFinal called');
        
        if (!$this->selectedPurchaseOrder) {
            Log::error('No selected purchase order found');
            session()->flash('error', 'Purchase order not found.');
            return;
        }
        
        Log::info('Selected purchase order found', ['po_id' => $this->selectedPurchaseOrder->id]);

        // Verify purchase order has items
        if ($this->selectedPurchaseOrder->items->count() === 0) {
            Log::error('Purchase order has no items', ['po_id' => $this->selectedPurchaseOrder->id]);
            session()->flash('error', 'Cannot confirm purchase order: No items found.');
            return;
        }

        try {
            // Update item prices
            foreach ($this->selectedPurchaseOrder->items as $item) {
                $unitPriceKey = "item_{$item->id}_unit_price";
                if (isset($this->phoneConfirmForm[$unitPriceKey])) {
                    $unitPrice = floatval($this->phoneConfirmForm[$unitPriceKey]);
                    $totalPrice = $unitPrice * $item->quantity;
                    
                    $item->update([
                        'unit_price' => $unitPrice,
                        'total_price' => $totalPrice
                    ]);
                }
            }

            // Update PO status to confirmed
            $this->selectedPurchaseOrder->update(['status' => 'confirmed']);

            // Create production order from the confirmed purchase order
            $productionOrder = ProductionOrder::create([
                'production_order_number' => ProductionOrder::generateProductionOrderNumber(),
                'date' => now()->toDateString(),
                'job_order_id' => $this->selectedPurchaseOrder->job_order_id,
                'supplier_id' => $this->selectedPurchaseOrder->supplier_id,
                'status' => 'pending',
                'notes' => "Generated from Purchase Order: {$this->selectedPurchaseOrder->po_number}",
            ]);

            // Create production order items from purchase order items
            foreach ($this->selectedPurchaseOrder->items as $item) {
                ProductionOrderItem::create([
                    'production_order_id' => $productionOrder->id,
                    'item_type' => $item->item_type,
                    'item_id' => $item->item_id,
                    'quantity' => $item->quantity,
                    'completed_quantity' => 0,
                    'status' => 'pending',
                ]);
            }

            // Store purchase order data before closing modals
            $poNumber = $this->selectedPurchaseOrder->po_number;
            
            Log::info('Purchase Order confirmed and Production Order created', [
                'po_id' => $this->selectedPurchaseOrder->id,
                'po_number' => $poNumber,
                'production_order_id' => $productionOrder->id,
                'production_order_number' => $productionOrder->production_order_number,
                'updated_prices' => $this->phoneConfirmForm
            ]);

            $this->closePhoneConfirmConfirmModal();
            $this->closePhoneConfirmModal();
            $this->loadPurchaseOrders();
            
            session()->flash('success', "Purchase Order {$poNumber} has been confirmed and Production Order {$productionOrder->production_order_number} has been created. <a href='" . route('production-order-management', ['production_order' => $productionOrder->id]) . "' class='underline hover:no-underline'>View Production Order</a>");
            
            // Set redirect URL for JavaScript to handle
            $this->redirectToProductionOrder = route('production-order-management', ['production_order' => $productionOrder->id]);
            
            // Emit event for JavaScript redirect
            $this->dispatch('redirectToProductionOrder', $this->redirectToProductionOrder);
            
        } catch (\Exception $e) {
            Log::error('Failed to confirm Purchase Order', [
                'error' => $e->getMessage(),
                'po_id' => $this->selectedPurchaseOrder ? $this->selectedPurchaseOrder->id : 'unknown'
            ]);
            
            session()->flash('error', 'Failed to confirm Purchase Order: ' . $e->getMessage());
        }
    }

    public function generatePurchaseOrder()
    {
        $this->validate();

        try {
            // Generate PO number
            $poNumber = PurchaseOrder::generatePONumber();

            // Create Purchase Order
            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $poNumber,
                'date' => $this->form['date'],
                'supplier_id' => $this->form['supplier_id'],
                'job_order_id' => $this->form['job_order_id'],
                'status' => 'draft',
                'notes' => $this->form['notes'],
            ]);

            // Get job order with items
            $jobOrder = JobOrder::with(['boxes', 'dividers', 'supplier'])->find($this->form['job_order_id']);
            $supplier = $jobOrder->supplier;

            // Add boxes as PO items
            foreach ($jobOrder->boxes as $box) {
                // Calculate dimensions fresh
                $reelSize = $this->calculateReelSize($box, $supplier);
                $cutSize = $this->calculateCutSize($box, $supplier);
                
                Log::info('Box calculations for PO', [
                    'box_id' => $box->id,
                    'length' => $box->length,
                    'width' => $box->width,
                    'height' => $box->height,
                    'ply' => $box->ply,
                    'top_liner' => $box->top_liner,
                    'flute' => $box->flute,
                    'calculated_reel_size' => $reelSize,
                    'calculated_cut_size' => $cutSize,
                    'supplier_uses_reel_format' => $supplier->usesReelFormat()
                ]);
                
                $description = $this->formatItemDescription($box, $supplier, 'box', $reelSize, $cutSize);
                
                Log::info('Final PO item description', [
                    'box_id' => $box->id,
                    'description' => $description
                ]);
                
                $purchaseOrder->items()->create([
                    'item_type' => 'box',
                    'item_id' => $box->id,
                    'description' => $description,
                    'reel_size' => $reelSize,
                    'cut_size' => $cutSize,
                    'quantity' => $jobOrder->quantity ?? 1,
                    'unit_price' => 0.00, // To be filled by supplier
                    'total_price' => 0.00, // To be calculated
                ]);
            }

            // Add dividers as PO items
            foreach ($jobOrder->dividers as $divider) {
                Log::info('Divider for PO', [
                    'divider_id' => $divider->id,
                    'length' => $divider->length,
                    'width' => $divider->width,
                    'supplier_uses_reel_format' => $supplier->usesReelFormat()
                ]);
                
                $description = $this->formatItemDescription($divider, $supplier, 'divider');
                
                $purchaseOrder->items()->create([
                    'item_type' => 'divider',
                    'item_id' => $divider->id,
                    'description' => $description,
                    'reel_size' => null,
                    'cut_size' => null,
                    'quantity' => $jobOrder->quantity ?? 1,
                    'unit_price' => 0.00, // To be filled by supplier
                    'total_price' => 0.00, // To be calculated
                ]);
            }

            Log::info('Purchase Order generated successfully', [
                'po_id' => $purchaseOrder->id,
                'po_number' => $poNumber,
                'job_order_id' => $this->form['job_order_id'],
                'items_count' => $purchaseOrder->items()->count()
            ]);

            $this->closeCreateModal();
            $this->loadPurchaseOrders();
            
            session()->flash('success', "Purchase Order {$poNumber} generated successfully!");
            
        } catch (\Exception $e) {
            Log::error('Failed to generate Purchase Order', [
                'error' => $e->getMessage(),
                'form_data' => $this->form
            ]);
            
            session()->flash('error', 'Failed to generate Purchase Order: ' . $e->getMessage());
        }
    }

    private function formatItemDescription($item, $supplier, $type, $reelSize = null, $cutSize = null)
    {
        // Build material specification first
        $materialSpec = $this->buildMaterialSpecification($item);
        
        // Check if supplier prefers reel/cut size or dimensions
        $useReelFormat = $supplier->usesReelFormat();
        
        Log::info('Formatting item description', [
            'item_id' => $item->id,
            'material_spec' => $materialSpec,
            'use_reel_format' => $useReelFormat,
            'type' => $type,
            'reel_size' => $reelSize,
            'cut_size' => $cutSize
        ]);
        
        if ($useReelFormat && $type === 'box') {
            // Format as Reel Size and Cut Size with material spec
            $reelSize = $reelSize ?? $item->calculated_reel_size ?? 0;
            $cutSize = $cutSize ?? $item->calculated_cut_size ?? 0;
            $description = "{$materialSpec} - Reel Size {$reelSize}\" Cut Size {$cutSize}\"";
        } else {
            // Format as dimensions with material spec
            if ($type === 'box') {
                $description = "{$materialSpec} - {$item->length}x{$item->width}x{$item->height}cm";
            } else {
                // Fix divider description - ensure we have valid dimensions
                $length = $item->length ?? 0;
                $width = $item->width ?? 0;
                $description = "{$materialSpec} - {$length}x{$width}cm";
            }
        }
        
        Log::info('Final formatted description', [
            'item_id' => $item->id,
            'final_description' => $description
        ]);
        
        return $description;
    }

    private function buildMaterialSpecification($item)
    {
        $ply = $item->ply ?? '';
        $topLiner = $item->top_liner ?? '';
        $flute = $item->flute ?? '';
        $fscClaim = $item->fsc_claim ?? '';
        
        Log::info('Building material specification', [
            'item_id' => $item->id,
            'ply' => $ply,
            'top_liner' => $topLiner,
            'flute' => $flute,
            'fsc_claim' => $fscClaim,
            'combination_1' => $item->combination_1 ?? '',
            'combination_2' => $item->combination_2 ?? '',
            'combination_3' => $item->combination_3 ?? '',
        ]);
        
        // Build PLY specification with combinations
        $plySpec = '';
        if ($ply) {
            $plySpec = $ply . 'PLY(';
            
            // Add combination parameters based on PLY
            $combinations = [];
            for ($i = 1; $i <= 7; $i++) {
                $combination = $item->{"combination_$i"} ?? '';
                if ($combination) {
                    $combinations[] = $combination;
                }
            }
            
            if (!empty($combinations)) {
                $plySpec .= implode('/', $combinations);
            }
            $plySpec .= ')';
        }
        
        // Add top liner
        if ($topLiner) {
            $plySpec .= $topLiner;
        }
        
        // Add flute
        if ($flute) {
            $plySpec .= ' ' . $flute . ' Flute';
        }
        
        // Add FSC claim
        if ($fscClaim) {
            $plySpec .= ' ' . $fscClaim . ' FSC';
        }
        
        $finalSpec = trim($plySpec);
        
        Log::info('Final material specification', [
            'item_id' => $item->id,
            'final_spec' => $finalSpec
        ]);
        
        return $finalSpec;
    }

    private function calculateReelSize($box, $supplier)
    {
        try {
            // Use the JobOrderBox model's calculation method
            $reelSize = $box->calculateReelSize();
            return $reelSize;
        } catch (\Exception $e) {
            Log::error('Error calculating reel size', [
                'box_id' => $box->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    private function calculateCutSize($box, $supplier)
    {
        try {
            // Use the JobOrderBox model's calculation method
            $cutSize = $box->calculateCutSize();
            return $cutSize;
        } catch (\Exception $e) {
            Log::error('Error calculating cut size', [
                'box_id' => $box->id,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    public function updatedFormJobOrderId()
    {
        // Handle when job order is selected
        if (!empty($this->form['job_order_id'])) {
            $jobOrder = JobOrder::find($this->form['job_order_id']);
            if ($jobOrder) {
                $this->form['supplier_id'] = $jobOrder->supplier_id;
            }
        }
    }

    public function render()
    {
        return view('livewire.purchase-order-management', [
            'jobOrders' => JobOrder::with(['supplier', 'customer'])
                ->where('status', 'confirmed')
                ->get(),
            'suppliers' => Supplier::all(),
        ]);
    }
}
