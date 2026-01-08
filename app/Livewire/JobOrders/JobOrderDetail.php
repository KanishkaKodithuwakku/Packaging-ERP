<?php

namespace App\Livewire\JobOrders;

use App\Models\Customer;
use App\Models\Supplier;
use App\Models\JobOrder;
use App\Models\JobOrderBox;
use App\Models\JobOrderDivider;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class JobOrderDetail extends Component
{
    public $jobOrder;
    public $jobOrderId;
    public $showBoxDividerModal = false;
    public $activeTab = 'boxes';
    public $boxFormTab = 'basic'; // Sub-tab for box form: 'basic', 'dimensions', 'material', 'calculated'
    public $isEditMode = false;
    public $hasPurchaseOrder = false;
    public $showPrintPreviewModal = false;

    // View box modal
    public $showBoxViewModal = false;
    public $boxViewData = null;
    // View divider modal
    public $showDividerViewModal = false;
    public $dividerViewData = null;
    public $printDisplayFormat = 'dimensions';

    // Form data
    public $form = [];
    public $boxes = [];
    public $dividers = [];
    public float $productionPercent = 0;
    public int $productionCompleted = 0;
    public int $productionFullyCompleted = 0;
    public int $productionInProgressQty = 0;
    public int $productionTotal = 0;
    public string $productionStatusText = 'Not started';
    public int $poCount = 0;
    public int $poProcessedCount = 0;
    public string $poStatusText = 'None';
    public int $grnCount = 0;
    public int $grnProcessedCount = 0;
    public int $deliveryCount = 0;
    public float $dispatchedQuantity = 0;
    public $purchaseOrders = [];
    public $fgGrns = []; // Finished Goods GRNs

    // Box form
    public $boxForm = [
        'order_qty' => '',
        'selling_price' => '',
        'activity' => '',
        'printing_instruction' => '',
        'no_of_colours' => '',
        'stitched_glued' => '',
        'sample_available' => false,
        'sample_attached' => false,
        'length' => '',
        'width' => '',
        'height' => '',
        'unit' => 'CM',
        'dimension_type' => 'INTERNAL',
        'top_liner' => 'WHITE',
        'ply' => '3',
        'combination_1' => '',
        'combination_2' => '',
        'combination_3' => '',
        'combination_4' => '',
        'combination_5' => '',
        'combination_6' => '',
        'combination_7' => '',
        'flute' => 'B',
        'fsc_claim' => '100%',
        'no_of_ups' => '',
        'supplier_price' => '',
        'board_qty' => '',
    ];

    // Divider form
    public $dividerForm = [
        'quantity' => '',
        'ply' => '3',
        'combination_1' => '',
        'combination_2' => '',
        'combination_3' => '',
        'combination_4' => '',
        'combination_5' => '',
        'combination_6' => '',
        'combination_7' => '',
        'unit' => 'CM',
        'fsc_claim' => '100%',
        'supplier_price' => '',
    ];

    // Calculated values
    public $calculatedReelSize = 0;
    public $calculatedCutSize = 0;
    public $calculatedBoardQty = 0;
    
    // Box editing state
    public $editingBoxIndex = null;
    public $showEditBoxModal = false;
    public $editingBoxData = [];

    // Box viewing state
    public $viewingBoxIndex = null;
    public $showViewBoxModal = false;
    public $viewingBoxData = [];

    // Divider editing state
    public $editingDividerIndex = null;
    public $showEditDividerModal = false;
    public $editingDividerData = [];

    // Divider viewing state
    public $viewingDividerIndex = null;
    public $showViewDividerModal = false;
    public $viewingDividerData = [];

    public function mount($id, $edit = false)
    {
        $this->jobOrderId = $id;

        // Check for edit parameter in URL query string
        $editFromQuery = request()->query('edit');

        $this->isEditMode = $edit === 'edit' || $edit === 'true' || $edit === true || $editFromQuery === 'true';
        $this->loadJobOrder();
    }

    public function hydrate()
    {
        // Refresh data when component is hydrated (e.g., when navigating back to page)
        if ($this->jobOrderId) {
            $this->refreshPurchaseOrderCount();
            if ($this->jobOrder) {
                $this->calculateProductionProgress();
            }
        }
    }

    public function loadJobOrder()
    {
        $this->jobOrder = JobOrder::with(['boxes', 'dividers', 'supplier', 'customer'])->findOrFail($this->jobOrderId);

        $this->form = $this->jobOrder->toArray();
        $this->form['date'] = $this->jobOrder->date ? $this->jobOrder->date->format('Y-m-d') : null;
        $this->form['po_date'] = $this->jobOrder->po_date ? $this->jobOrder->po_date->format('Y-m-d') : null;

        // Load boxes and dividers
        $this->boxes = $this->jobOrder->boxes->map(function($box) {
            return $box->toArray();
        })->toArray();

        $this->dividers = $this->jobOrder->dividers->map(function($divider) {
            return $divider->toArray();
        })->toArray();

        // Check if a purchase order already exists for this job order
        // Purchase orders are linked to job orders through purchase_order_items table
        $this->hasPurchaseOrder = \App\Models\PurchaseOrder::where('status', '!=', 'cancelled')
            ->whereHas('items', function($query) {
                $query->where('job_order_id', $this->jobOrderId);
            })
            ->exists();

        // Calculate production progress
        $this->calculateProductionProgress();

        // Purchase Orders status summary - refresh count and store for display
        $this->refreshPurchaseOrderCount();
        
        // Get purchase orders for GRN calculation (need the IDs)
        // Purchase orders are linked to job orders through purchase_order_items table
        $purchaseOrders = \App\Models\PurchaseOrder::where('status', '!=', 'cancelled')
            ->whereHas('items', function($query) {
                $query->where('job_order_id', $this->jobOrderId);
            })
            ->get();

        // Get production orders for GRN status summary
        $productionOrders = \App\Models\ProductionOrder::where('job_order_id', $this->jobOrderId)->get();

        // GRN status summary (from POs and Production Orders linked to this Job Order)
        $poIds = $purchaseOrders->pluck('id')->all();
        $poIds2 = $productionOrders->pluck('id')->all();
        
        // Build query for GRNs - must be from this job order's purchase orders OR production orders
        $grns = \App\Models\GRN::where(function($query) use ($poIds, $poIds2) {
                if (count($poIds) > 0) {
                    $query->whereIn('purchase_order_id', $poIds);
                }
                if (count($poIds2) > 0) {
                    if (count($poIds) > 0) {
                        $query->orWhereIn('production_order_id', $poIds2);
                    } else {
                        $query->whereIn('production_order_id', $poIds2);
                    }
                }
            })
            ->with(['items.productionOrderItem', 'productionOrder'])
            ->get();
        $this->grnCount = $grns->count();
        $this->grnProcessedCount = $grns->where('status', 'processed')->count();
        
        // Load FG GRNs (GRNs from production orders) - filter to ensure they belong to this job order
        $fgGrns = $grns->filter(function($grn) use ($poIds2) {
            if (!$grn->isFromProductionOrder()) {
                return false;
            }
            // Double-check: ensure the production order belongs to this job order
            if ($grn->productionOrder && in_array($grn->productionOrder->id, $poIds2)) {
                return true;
            }
            return false;
        });
        
        $this->fgGrns = $fgGrns->map(function($grn) {
            // Calculate total waste/conserve from production order items linked to this GRN
            $totalWaste = 0;
            foreach ($grn->items as $grnItem) {
                if ($grnItem->productionOrderItem) {
                    $wasteQty = $grnItem->productionOrderItem->waste_quantity ?? 0;
                    $totalWaste += $wasteQty;
                }
            }
            
            return [
                'id' => $grn->id,
                'grn_no' => $grn->grn_no,
                'lot_code' => $grn->lot_code,
                'received_date' => $grn->received_date ? $grn->received_date->format('Y-m-d') : null,
                'received_date_formatted' => $grn->received_date ? $grn->received_date->format('M d, Y') : 'N/A',
                'status' => $grn->status,
                'processed_at' => $grn->processed_at ? $grn->processed_at->format('M d, Y H:i') : null,
                'total_quantity' => $grn->getTotalQuantity(), // Already returns FG quantity (boxes)
                'total_processed' => $grn->getTotalProcessedQuantity(), // Already returns FG quantity (boxes)
                'total_expected' => $grn->getTotalExpectedQuantity(),
                'production_order_number' => $grn->productionOrder ? $grn->productionOrder->production_order_number : 'N/A',
                'waste_quantity' => $totalWaste, // Positive = waste, Negative = conserve
            ];
        })->toArray();

        // Delivery Note status summary
        $deliveryNotes = \App\Models\DeliveryNote::where('job_order_id', $this->jobOrderId)->with('items')->get();
        $this->deliveryCount = $deliveryNotes->count();
        $this->dispatchedQuantity = $deliveryNotes->sum(function($dn) {
            return $dn->items->sum('dispatched_qty');
        });
    }

    public function toggleEditMode()
    {
        $this->isEditMode = !$this->isEditMode;
    }

    public function openBoxDividerModal()
    {
        $this->showBoxDividerModal = true;
        $this->activeTab = 'boxes';
        $this->resetBoxForm();
        $this->resetDividerForm();
    }

    public function closeBoxDividerModal()
    {
        $this->showBoxDividerModal = false;
        $this->resetBoxForm();
        $this->resetDividerForm();
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function setBoxFormTab($tab)
    {
        $this->boxFormTab = $tab;
    }

    public function updatedBoxFormPly($value)
    {
        // Reset combination fields when PLY changes
        $this->boxForm['combination_1'] = '';
        $this->boxForm['combination_2'] = '';
        $this->boxForm['combination_3'] = '';
        $this->boxForm['combination_4'] = '';
        $this->boxForm['combination_5'] = '';
        $this->boxForm['combination_6'] = '';
        $this->boxForm['combination_7'] = '';

        $this->calculateDimensions();
    }

    public function updatedBoxFormLength()
    {
        $this->calculateDimensions();
    }

    public function updatedBoxFormWidth()
    {
        $this->calculateDimensions();
    }

    public function updatedBoxFormHeight()
    {
        $this->calculateDimensions();
    }

    public function updatedBoxFormOrderQty()
    {
        $this->calculateDimensions();
        $this->calculateBoardQty();
    }

    public function updatedBoxFormNoOfUps()
    {
        $this->calculateDimensions();
        $this->calculateBoardQty();
    }
    
    public function calculateBoardQty()
    {
        $orderQty = (float)($this->boxForm['order_qty'] ?? 0);
        $noOfUps = (float)($this->boxForm['no_of_ups'] ?? 0);
        
        if ($orderQty > 0 && $noOfUps > 0) {
            $calculated = ceil($orderQty / $noOfUps);
            $this->calculatedBoardQty = $calculated;
            // Always set calculated value in boxForm
            $this->boxForm['board_qty'] = $calculated;
        } else {
            $this->calculatedBoardQty = 0;
            if (empty($orderQty) || empty($noOfUps)) {
                $this->boxForm['board_qty'] = '';
            }
        }
    }

    public function updatedDividerFormPly($value)
    {
        // Reset combination fields when PLY changes
        $this->dividerForm['combination_1'] = '';
        $this->dividerForm['combination_2'] = '';
        $this->dividerForm['combination_3'] = '';
        $this->dividerForm['combination_4'] = '';
        $this->dividerForm['combination_5'] = '';
        $this->dividerForm['combination_6'] = '';
        $this->dividerForm['combination_7'] = '';
    }

    public function calculateDimensions()
    {
        if ($this->boxForm['length'] && $this->boxForm['width'] && $this->boxForm['height']) {
            $box = new JobOrderBox();
            $box->length = (float) $this->boxForm['length'];
            $box->width = (float) $this->boxForm['width'];
            $box->height = (float) $this->boxForm['height'];
            $box->unit = $this->boxForm['unit'];
            $box->dimension_type = $this->boxForm['dimension_type'];
            $box->ply = $this->boxForm['ply'];
            $box->order_qty = (int) ($this->boxForm['order_qty'] ?? 0);
            $box->no_of_ups = (int) ($this->boxForm['no_of_ups'] ?? 0);

            // Calculate reel size and cut size
            $this->calculatedReelSize = $box->calculateReelSize($this->jobOrder->supplier_id);
            $this->calculatedCutSize = $box->calculateCutSize();
            $this->calculatedBoardQty = $box->calculateBoardQty();

            \Log::info('Calculations: ', [
                'supplier_id' => $this->jobOrder->supplier_id,
                'length' => $this->boxForm['length'],
                'width' => $this->boxForm['width'],
                'height' => $this->boxForm['height'],
                'ply' => $this->boxForm['ply'],
                'dimension_type' => $this->boxForm['dimension_type'],
                'order_qty' => $this->boxForm['order_qty'],
                'no_of_ups' => $this->boxForm['no_of_ups'],
                'reel_size' => $this->calculatedReelSize,
                'cut_size' => $this->calculatedCutSize,
                'board_qty' => $this->calculatedBoardQty,
                'boxForm_reel_size' => $this->boxForm['reel_size'],
                'boxForm_cut_size' => $this->boxForm['cut_size']
            ]);
        } else {
            // Clear values if dimensions are missing
            $this->calculatedReelSize = 0;
            $this->calculatedCutSize = 0;
            $this->boxForm['reel_size'] = '';
            $this->boxForm['cut_size'] = '';
        }
    }

    public function addBox()
    {
        // Build base validation rules
        $rules = [
            'boxForm.order_qty' => 'required|numeric|min:1',
            'boxForm.selling_price' => 'required|numeric|min:0',
            'boxForm.activity' => 'required|string',
            'boxForm.printing_instruction' => 'required|string',
            'boxForm.no_of_colours' => 'required|numeric|min:0',
            'boxForm.stitched_glued' => 'required|string',
            'boxForm.sample_available' => 'required|string',
            'boxForm.sample_attached' => 'required|string',
            'boxForm.length' => 'required|numeric|min:0.01',
            'boxForm.width' => 'required|numeric|min:0.01',
            'boxForm.height' => 'required|numeric|min:0.01',
            'boxForm.unit' => 'required|string',
            'boxForm.dimension_type' => 'required|string',
            'boxForm.top_liner' => 'required|string',
            'boxForm.ply' => 'required|in:3,5,7',
            'boxForm.flute' => 'required|string',
            'boxForm.fsc_claim' => 'required|string',
            'boxForm.no_of_ups' => 'required|numeric|min:1',
            'boxForm.supplier_price' => 'required|numeric|min:0',
            'boxForm.notes' => 'nullable|string',
        ];

        // Add combination fields validation based on PLY
        $ply = $this->boxForm['ply'] ?? null;
        if ($ply == '3') {
            $rules['boxForm.combination_1'] = 'required|string';
            $rules['boxForm.combination_2'] = 'required|string';
            $rules['boxForm.combination_3'] = 'required|string';
        } elseif ($ply == '5') {
            $rules['boxForm.combination_1'] = 'required|string';
            $rules['boxForm.combination_2'] = 'required|string';
            $rules['boxForm.combination_3'] = 'required|string';
            $rules['boxForm.combination_4'] = 'required|string';
            $rules['boxForm.combination_5'] = 'required|string';
        } elseif ($ply == '7') {
            $rules['boxForm.combination_1'] = 'required|string';
            $rules['boxForm.combination_2'] = 'required|string';
            $rules['boxForm.combination_3'] = 'required|string';
            $rules['boxForm.combination_4'] = 'required|string';
            $rules['boxForm.combination_5'] = 'required|string';
            $rules['boxForm.combination_6'] = 'required|string';
            $rules['boxForm.combination_7'] = 'required|string';
        }

        $this->validate($rules);

        try {
            // Create the box directly in the database
            $boxData = [
                'job_order_id' => $this->jobOrderId,
                'order_qty' => $this->boxForm['order_qty'],
                'selling_price' => $this->boxForm['selling_price'],
                'activity' => $this->boxForm['activity'],
                'printing_instruction' => $this->boxForm['printing_instruction'],
                'no_of_colours' => (int)$this->boxForm['no_of_colours'],
                'stitched_glued' => ($this->boxForm['stitched_glued'] === 'None' || $this->boxForm['stitched_glued'] === '') ? null : strtolower($this->boxForm['stitched_glued']),
                'sample_available' => $this->boxForm['sample_available'] === 'Yes',
                'sample_attached' => $this->boxForm['sample_attached'] === 'Yes',
                'length' => $this->boxForm['length'],
                'width' => $this->boxForm['width'],
                'height' => $this->boxForm['height'],
                'unit' => $this->boxForm['unit'],
                'dimension_type' => $this->boxForm['dimension_type'],
                'top_liner' => $this->boxForm['top_liner'],
                'ply' => $this->boxForm['ply'],
                'combination_1' => $this->boxForm['combination_1'] ?? '',
                'combination_2' => $this->boxForm['combination_2'] ?? '',
                'combination_3' => $this->boxForm['combination_3'] ?? '',
                'combination_4' => $this->boxForm['combination_4'] ?? '',
                'combination_5' => $this->boxForm['combination_5'] ?? '',
                'combination_6' => $this->boxForm['combination_6'] ?? '',
                'combination_7' => $this->boxForm['combination_7'] ?? '',
                'flute' => $this->boxForm['flute'],
                'fsc_claim' => $this->boxForm['fsc_claim'],
                'no_of_ups' => (int)$this->boxForm['no_of_ups'],
                'supplier_price' => $this->boxForm['supplier_price'],
                'notes' => !empty($this->boxForm['notes']) ? $this->boxForm['notes'] : null,
                // Use manual values if entered (check for null/empty, not just falsy), otherwise use calculated values
                'reel_size' => (isset($this->boxForm['reel_size']) && $this->boxForm['reel_size'] !== '' && $this->boxForm['reel_size'] !== null) ? (float) $this->boxForm['reel_size'] : (($this->calculatedReelSize > 0) ? $this->calculatedReelSize : 0),
                'cut_size' => (isset($this->boxForm['cut_size']) && $this->boxForm['cut_size'] !== '' && $this->boxForm['cut_size'] !== null) ? (float) $this->boxForm['cut_size'] : (($this->calculatedCutSize > 0) ? $this->calculatedCutSize : 0),
                'board_qty' => (isset($this->boxForm['board_qty']) && $this->boxForm['board_qty'] !== '' && $this->boxForm['board_qty'] !== null) ? (float) $this->boxForm['board_qty'] : (($this->calculatedBoardQty > 0) ? $this->calculatedBoardQty : 0),
            ];

            $this->jobOrder->boxes()->create($boxData);

            // Reload the job order to get updated data
            $this->loadJobOrder();

            $this->resetBoxForm();
            $this->activeTab = 'boxes';

            // Close the modal
            $this->closeBoxDividerModal();

            session()->flash('success', 'Box added successfully!');
        } catch (\Exception $e) {
            \Log::error('Error adding box: ' . $e->getMessage());
            session()->flash('error', 'Error adding box: ' . $e->getMessage());
        }
    }

    public function addDivider()
    {
        // Build base validation rules
        $rules = [
            'dividerForm.quantity' => 'required|numeric|min:1',
            'dividerForm.ply' => 'required|in:3,5,7',
            'dividerForm.unit' => 'required|string',
            'dividerForm.fsc_claim' => 'required|string',
            'dividerForm.supplier_price' => 'required|numeric|min:0',
        ];

        // Add combination fields validation based on PLY
        $ply = $this->dividerForm['ply'] ?? null;
        if ($ply == '3') {
            $rules['dividerForm.combination_1'] = 'required|string';
            $rules['dividerForm.combination_2'] = 'required|string';
            $rules['dividerForm.combination_3'] = 'required|string';
        } elseif ($ply == '5') {
            $rules['dividerForm.combination_1'] = 'required|string';
            $rules['dividerForm.combination_2'] = 'required|string';
            $rules['dividerForm.combination_3'] = 'required|string';
            $rules['dividerForm.combination_4'] = 'required|string';
            $rules['dividerForm.combination_5'] = 'required|string';
        } elseif ($ply == '7') {
            $rules['dividerForm.combination_1'] = 'required|string';
            $rules['dividerForm.combination_2'] = 'required|string';
            $rules['dividerForm.combination_3'] = 'required|string';
            $rules['dividerForm.combination_4'] = 'required|string';
            $rules['dividerForm.combination_5'] = 'required|string';
            $rules['dividerForm.combination_6'] = 'required|string';
            $rules['dividerForm.combination_7'] = 'required|string';
        }

        $this->validate($rules);

        try {
            // Create the divider directly in the database
            $dividerData = [
                'job_order_id' => $this->jobOrderId,
                'quantity' => $this->dividerForm['quantity'],
                'ply' => $this->dividerForm['ply'],
                'combination_1' => $this->dividerForm['combination_1'] ?? '',
                'combination_2' => $this->dividerForm['combination_2'] ?? '',
                'combination_3' => $this->dividerForm['combination_3'] ?? '',
                'combination_4' => $this->dividerForm['combination_4'] ?? '',
                'combination_5' => $this->dividerForm['combination_5'] ?? '',
                'combination_6' => $this->dividerForm['combination_6'] ?? '',
                'combination_7' => $this->dividerForm['combination_7'] ?? '',
                'unit' => $this->dividerForm['unit'],
                'fsc_claim' => $this->dividerForm['fsc_claim'],
                'supplier_price' => $this->dividerForm['supplier_price'],
            ];

            $this->jobOrder->dividers()->create($dividerData);

            // Reload the job order to get updated data
            $this->loadJobOrder();

            $this->resetDividerForm();
            $this->activeTab = 'dividers';

            // Close the modal
            $this->closeBoxDividerModal();

            session()->flash('success', 'Divider added successfully!');
        } catch (\Exception $e) {
            \Log::error('Error adding divider: ' . $e->getMessage());
            session()->flash('error', 'Error adding divider: ' . $e->getMessage());
        }
    }

    public function editBox($index)
    {
        if (isset($this->boxes[$index])) {
            // Check if job order is confirmed or beyond - cannot edit items
            if ($this->jobOrder && !in_array($this->jobOrder->status, ['draft', 'pending'])) {
                session()->flash('error', 'Cannot edit boxes from confirmed job orders.');
                return;
            }

            $this->editingBoxIndex = $index;
            $box = $this->boxes[$index];
            
            // Populate all box data for editing with 2 decimal places for numeric values
            $this->editingBoxData = [
                'order_qty' => $box['order_qty'] ?? '',
                'selling_price' => isset($box['selling_price']) && $box['selling_price'] !== '' ? number_format((float)$box['selling_price'], 2, '.', '') : '',
                'activity' => $box['activity'] ?? '',
                'printing_instruction' => $box['printing_instruction'] ?? '',
                'no_of_colours' => $box['no_of_colours'] ?? '',
                'stitched_glued' => isset($box['stitched_glued']) && $box['stitched_glued'] !== '' && $box['stitched_glued'] !== null ? ucfirst(strtolower($box['stitched_glued'])) : 'None',
                'sample_available' => $box['sample_available'] ?? false ? 'Yes' : 'No',
                'sample_attached' => $box['sample_attached'] ?? false ? 'Yes' : 'No',
                'length' => isset($box['length']) && $box['length'] !== '' ? number_format((float)$box['length'], 2, '.', '') : '',
                'width' => isset($box['width']) && $box['width'] !== '' ? number_format((float)$box['width'], 2, '.', '') : '',
                'height' => isset($box['height']) && $box['height'] !== '' ? number_format((float)$box['height'], 2, '.', '') : '',
                'unit' => $box['unit'] ?? 'CM',
                'dimension_type' => $box['dimension_type'] ?? 'INTERNAL',
                'top_liner' => $box['top_liner'] ?? 'WHITE',
                'ply' => $box['ply'] ?? '',
                'combination_1' => $box['combination_1'] ?? '',
                'combination_2' => $box['combination_2'] ?? '',
                'combination_3' => $box['combination_3'] ?? '',
                'combination_4' => $box['combination_4'] ?? '',
                'combination_5' => $box['combination_5'] ?? '',
                'combination_6' => $box['combination_6'] ?? '',
                'combination_7' => $box['combination_7'] ?? '',
                'flute' => $box['flute'] ?? 'B',
                'fsc_claim' => $box['fsc_claim'] ?? '100%',
                'no_of_ups' => $box['no_of_ups'] ?? '',
                'supplier_price' => isset($box['supplier_price']) && $box['supplier_price'] !== '' ? number_format((float)$box['supplier_price'], 2, '.', '') : '',
                'reel_size' => isset($box['reel_size']) && $box['reel_size'] !== '' ? number_format((float)$box['reel_size'], 2, '.', '') : '',
                'cut_size' => isset($box['cut_size']) && $box['cut_size'] !== '' ? number_format((float)$box['cut_size'], 2, '.', '') : '',
                'board_qty' => isset($box['board_qty']) && $box['board_qty'] !== '' ? number_format((float)$box['board_qty'], 2, '.', '') : '',
                'notes' => $box['notes'] ?? ''
            ];
            
            $this->showEditBoxModal = true;
        }
    }

    public function closeEditBoxModal()
    {
        $this->showEditBoxModal = false;
        $this->editingBoxIndex = null;
        $this->editingBoxData = [];
    }

    public function viewBox($index)
    {
        if (isset($this->boxes[$index])) {
            $this->viewingBoxIndex = $index;
            $box = $this->boxes[$index];
            
            // Populate all box data for viewing
            $this->viewingBoxData = [
                'order_qty' => number_format($box['order_qty'] ?? 0),
                'selling_price' => isset($box['selling_price']) && $box['selling_price'] !== '' ? number_format((float)$box['selling_price'], 2) : '-',
                'activity' => $box['activity'] ?? '-',
                'printing_instruction' => $box['printing_instruction'] ?? '-',
                'no_of_colours' => $box['no_of_colours'] ?? '-',
                'stitched_glued' => isset($box['stitched_glued']) && $box['stitched_glued'] !== '' && $box['stitched_glued'] !== null ? ucfirst(strtolower($box['stitched_glued'])) : '-',
                'sample_available' => $box['sample_available'] ?? false ? 'Yes' : 'No',
                'sample_attached' => $box['sample_attached'] ?? false ? 'Yes' : 'No',
                'length' => isset($box['length']) ? number_format((float)$box['length'], 3) : '-',
                'width' => isset($box['width']) ? number_format((float)$box['width'], 3) : '-',
                'height' => isset($box['height']) ? number_format((float)$box['height'], 3) : '-',
                'unit' => $box['unit'] ?? 'CM',
                'dimension_type' => $box['dimension_type'] ?? '-',
                'top_liner' => $box['top_liner'] ?? '-',
                'ply' => $box['ply'] ?? '-',
                'combination_1' => $box['combination_1'] ?? '-',
                'combination_2' => $box['combination_2'] ?? '-',
                'combination_3' => $box['combination_3'] ?? '-',
                'combination_4' => $box['combination_4'] ?? '-',
                'combination_5' => $box['combination_5'] ?? '-',
                'combination_6' => $box['combination_6'] ?? '-',
                'combination_7' => $box['combination_7'] ?? '-',
                'flute' => $box['flute'] ?? '-',
                'fsc_claim' => $box['fsc_claim'] ?? '-',
                'no_of_ups' => $box['no_of_ups'] ?? '-',
                'supplier_price' => isset($box['supplier_price']) && $box['supplier_price'] !== '' ? number_format((float)$box['supplier_price'], 2) : '-',
                'reel_size' => isset($box['reel_size']) && $box['reel_size'] !== '' ? number_format((float)$box['reel_size'], 3) : '-',
                'cut_size' => isset($box['cut_size']) && $box['cut_size'] !== '' ? number_format((float)$box['cut_size'], 3) : '-',
                'board_qty' => isset($box['board_qty']) && $box['board_qty'] !== '' ? number_format((float)$box['board_qty'], 3) : '-',
                'notes' => $box['notes'] ?? '-'
            ];
            
            // Show the detailed modal
            $this->showViewBoxModal = true;
        }
    }

    public function closeViewBoxModal()
    {
        $this->showViewBoxModal = false;
        $this->viewingBoxIndex = null;
        $this->viewingBoxData = [];
    }

    public function calculateBoardQtyForEdit()
    {
        if (isset($this->editingBoxData['order_qty']) && isset($this->editingBoxData['no_of_ups']) && 
            $this->editingBoxData['order_qty'] && $this->editingBoxData['no_of_ups'] && $this->editingBoxData['no_of_ups'] > 0) {
            $calculated = ceil($this->editingBoxData['order_qty'] / $this->editingBoxData['no_of_ups']);
            // Only auto-populate board_qty if it's empty or not set (don't overwrite user input)
            if (empty($this->editingBoxData['board_qty']) || $this->editingBoxData['board_qty'] === '' || $this->editingBoxData['board_qty'] === null) {
                $this->editingBoxData['board_qty'] = number_format($calculated, 2, '.', '');
            }
        }
    }

    public function saveCalculatedFields()
    {
        // Recalculate dimensions if needed
        if ($this->boxForm['length'] && $this->boxForm['width'] && $this->boxForm['height']) {
            $this->calculateDimensions();
        }

        // Use manual values if entered, otherwise use calculated values
        // Only overwrite if field is truly empty (null, empty string, or not set)
        if ((!isset($this->boxForm['reel_size']) || $this->boxForm['reel_size'] === '' || $this->boxForm['reel_size'] === null) && $this->calculatedReelSize > 0) {
            $this->boxForm['reel_size'] = $this->calculatedReelSize;
        }
        if ((!isset($this->boxForm['cut_size']) || $this->boxForm['cut_size'] === '' || $this->boxForm['cut_size'] === null) && $this->calculatedCutSize > 0) {
            $this->boxForm['cut_size'] = $this->calculatedCutSize;
        }
        if ((!isset($this->boxForm['board_qty']) || $this->boxForm['board_qty'] === '' || $this->boxForm['board_qty'] === null) && $this->calculatedBoardQty > 0) {
            $this->boxForm['board_qty'] = $this->calculatedBoardQty;
        }

        // Validate the calculated fields
        $this->validate([
            'boxForm.reel_size' => 'required|numeric|min:0',
            'boxForm.cut_size' => 'required|numeric|min:0',
            'boxForm.board_qty' => 'required|numeric|min:0',
        ], [
            'boxForm.reel_size.required' => 'Reel Size is required',
            'boxForm.reel_size.numeric' => 'Reel Size must be a number',
            'boxForm.cut_size.required' => 'Cut Size is required',
            'boxForm.cut_size.numeric' => 'Cut Size must be a number',
            'boxForm.board_qty.required' => 'Board Qty is required',
            'boxForm.board_qty.numeric' => 'Board Qty must be a number',
        ]);

        // Ensure values are properly formatted as floats
        $this->boxForm['reel_size'] = (float) $this->boxForm['reel_size'];
        $this->boxForm['cut_size'] = (float) $this->boxForm['cut_size'];
        $this->boxForm['board_qty'] = (float) $this->boxForm['board_qty'];

        session()->flash('success', 'Calculated fields saved successfully.');
    }

    public function updatedEditingBoxData($value, $field)
    {
        // Calculate Board Qty when order_qty or no_of_ups changes
        if (in_array($field, ['order_qty', 'no_of_ups'])) {
            $this->calculateBoardQtyForEdit();
        }
        
        // Calculate dimensions when length, width, height, unit, dimension_type, or ply changes
        if (in_array($field, ['length', 'width', 'height', 'unit', 'dimension_type', 'ply'])) {
            $this->calculateDimensionsForEdit();
        }
    }

    public function calculateDimensionsForEdit()
    {
        if (isset($this->editingBoxData['length']) && isset($this->editingBoxData['width']) && isset($this->editingBoxData['height']) &&
            $this->editingBoxData['length'] && $this->editingBoxData['width'] && $this->editingBoxData['height']) {
            
            // Create a temporary JobOrderBox instance to use its calculation methods
            $box = new JobOrderBox();
            $box->length = (float) $this->editingBoxData['length'];
            $box->width = (float) $this->editingBoxData['width'];
            $box->height = (float) $this->editingBoxData['height'];
            $box->unit = $this->editingBoxData['unit'] ?? 'CM';
            $box->dimension_type = $this->editingBoxData['dimension_type'] ?? 'INTERNAL';
            $box->ply = $this->editingBoxData['ply'] ?? '3';

            // Calculate reel size and cut size using the model methods
            $supplierId = $this->jobOrder->supplier_id ?? null;
            $calculatedReelSize = $box->calculateReelSize($supplierId);
            $calculatedCutSize = $box->calculateCutSize();

            // Only auto-populate if fields are empty (don't overwrite user input)
            if (empty($this->editingBoxData['reel_size']) || $this->editingBoxData['reel_size'] === '' || $this->editingBoxData['reel_size'] === null) {
                $this->editingBoxData['reel_size'] = number_format($calculatedReelSize, 2, '.', '');
            }
            if (empty($this->editingBoxData['cut_size']) || $this->editingBoxData['cut_size'] === '' || $this->editingBoxData['cut_size'] === null) {
                $this->editingBoxData['cut_size'] = number_format($calculatedCutSize, 2, '.', '');
            }
        }
    }

    public function saveBox($index)
    {
        if (isset($this->boxes[$index])) {
            // Check if job order is confirmed or beyond - cannot edit items
            if ($this->jobOrder && !in_array($this->jobOrder->status, ['draft', 'pending'])) {
                session()->flash('error', 'Cannot edit boxes from confirmed job orders.');
                $this->closeEditBoxModal();
                return;
            }

            // Validate the edited values
            $this->validate([
                'editingBoxData.order_qty' => 'required|integer|min:1',
                'editingBoxData.selling_price' => 'required|numeric|min:0',
                'editingBoxData.length' => 'required|numeric|min:0.01',
                'editingBoxData.width' => 'required|numeric|min:0.01',
                'editingBoxData.height' => 'required|numeric|min:0.01',
                'editingBoxData.unit' => 'required',
                'editingBoxData.dimension_type' => 'required',
                'editingBoxData.top_liner' => 'required',
                'editingBoxData.ply' => 'required',
                'editingBoxData.flute' => 'required',
                'editingBoxData.fsc_claim' => 'required',
                'editingBoxData.no_of_colours' => 'required|integer|min:0',
                'editingBoxData.reel_size' => 'required|numeric|min:0',
                'editingBoxData.cut_size' => 'required|numeric|min:0',
                'editingBoxData.board_qty' => 'required|numeric|min:0',
            ]);

            try {
                // Get the box from the current boxes array
                $box = $this->boxes[$index];
                
                // Update the box in database
                $jobOrderBox = JobOrderBox::find($box['id']);
                if ($jobOrderBox) {
                    $jobOrderBox->order_qty = $this->editingBoxData['order_qty'];
                    $jobOrderBox->selling_price = $this->editingBoxData['selling_price'];
                    $jobOrderBox->activity = $this->editingBoxData['activity'];
                    $jobOrderBox->printing_instruction = $this->editingBoxData['printing_instruction'];
                    $jobOrderBox->no_of_colours = (int) $this->editingBoxData['no_of_colours'];
                    $jobOrderBox->stitched_glued = ($this->editingBoxData['stitched_glued'] === 'None' || $this->editingBoxData['stitched_glued'] === '') ? null : strtolower($this->editingBoxData['stitched_glued']);
                    $jobOrderBox->sample_available = $this->editingBoxData['sample_available'] === 'Yes';
                    $jobOrderBox->sample_attached = $this->editingBoxData['sample_attached'] === 'Yes';
                    $jobOrderBox->length = (float) $this->editingBoxData['length'];
                    $jobOrderBox->width = (float) $this->editingBoxData['width'];
                    $jobOrderBox->height = (float) $this->editingBoxData['height'];
                    $jobOrderBox->unit = $this->editingBoxData['unit'];
                    $jobOrderBox->dimension_type = $this->editingBoxData['dimension_type'];
                    $jobOrderBox->top_liner = $this->editingBoxData['top_liner'];
                    $jobOrderBox->ply = $this->editingBoxData['ply'];
                    $jobOrderBox->combination_1 = $this->editingBoxData['combination_1'];
                    $jobOrderBox->combination_2 = $this->editingBoxData['combination_2'];
                    $jobOrderBox->combination_3 = $this->editingBoxData['combination_3'];
                    $jobOrderBox->combination_4 = $this->editingBoxData['combination_4'] ?? null;
                    $jobOrderBox->combination_5 = $this->editingBoxData['combination_5'] ?? null;
                    $jobOrderBox->combination_6 = $this->editingBoxData['combination_6'] ?? null;
                    $jobOrderBox->combination_7 = $this->editingBoxData['combination_7'] ?? null;
                    $jobOrderBox->flute = $this->editingBoxData['flute'];
                    $jobOrderBox->fsc_claim = $this->editingBoxData['fsc_claim'];
                    $jobOrderBox->no_of_ups = $this->editingBoxData['no_of_ups'];
                    $jobOrderBox->supplier_price = isset($this->editingBoxData['supplier_price']) && $this->editingBoxData['supplier_price'] !== '' ? (float) $this->editingBoxData['supplier_price'] : null;
                    $jobOrderBox->reel_size = isset($this->editingBoxData['reel_size']) && $this->editingBoxData['reel_size'] !== '' ? (float) $this->editingBoxData['reel_size'] : null;
                    $jobOrderBox->cut_size = isset($this->editingBoxData['cut_size']) && $this->editingBoxData['cut_size'] !== '' ? (float) $this->editingBoxData['cut_size'] : null;
                    $jobOrderBox->board_qty = isset($this->editingBoxData['board_qty']) && $this->editingBoxData['board_qty'] !== '' ? (float) $this->editingBoxData['board_qty'] : null;
                    $jobOrderBox->save();
                }

                // Reload the job order to get updated data
                $this->loadJobOrder();

                // Close modal and reset
                $this->closeEditBoxModal();

                session()->flash('success', 'Box updated successfully.');
            } catch (\Exception $e) {
                \Log::error('Error updating box: ' . $e->getMessage());
                session()->flash('error', 'Error updating box: ' . $e->getMessage());
            }
        }
    }

    public function removeBox($index)
    {
        try {
            // Check if job order is confirmed or beyond - cannot delete items
            if ($this->jobOrder && !in_array($this->jobOrder->status, ['draft', 'pending'])) {
                session()->flash('error', 'Cannot delete boxes from confirmed job orders.');
                return;
            }

            // Reset editing if the removed box was being edited
            if ($this->editingBoxIndex === $index) {
                $this->cancelEditBox();
            } elseif ($this->editingBoxIndex !== null && $this->editingBoxIndex > $index) {
                // Adjust editing index if a box before the editing one was removed
                $this->editingBoxIndex--;
            }

            // Get the box from the current boxes array
            $box = $this->boxes[$index];

            // Find and delete the box from database
            $this->jobOrder->boxes()->where('id', $box['id'])->delete();

            // Reload the job order to get updated data
            $this->loadJobOrder();

            session()->flash('success', 'Box removed successfully!');

        } catch (\Exception $e) {
            \Log::error('Error removing box: ' . $e->getMessage());
            session()->flash('error', 'Error removing box: ' . $e->getMessage());
        }
    }

    public function editDivider($index)
    {
        if (isset($this->dividers[$index])) {
            // Check if job order is confirmed or beyond - cannot edit items
            if ($this->jobOrder && !in_array($this->jobOrder->status, ['draft', 'pending'])) {
                session()->flash('error', 'Cannot edit dividers from confirmed job orders.');
                return;
            }

            $this->editingDividerIndex = $index;
            $divider = $this->dividers[$index];
            
            // Populate all divider data for editing
            $this->editingDividerData = [
                'combination_1' => $divider['combination_1'] ?? '',
                'combination_2' => $divider['combination_2'] ?? '',
                'combination_3' => $divider['combination_3'] ?? '',
                'combination_4' => $divider['combination_4'] ?? '',
                'combination_5' => $divider['combination_5'] ?? '',
                'combination_6' => $divider['combination_6'] ?? '',
                'combination_7' => $divider['combination_7'] ?? '',
                'ply' => $divider['ply'] ?? '',
                'quantity' => $divider['quantity'] ?? '',
                'unit' => $divider['unit'] ?? 'CM',
                'fsc_claim' => $divider['fsc_claim'] ?? '100%',
                'supplier_price' => isset($divider['supplier_price']) && $divider['supplier_price'] !== '' ? number_format((float)$divider['supplier_price'], 2, '.', '') : '',
            ];
            
            $this->showEditDividerModal = true;
        }
    }

    public function viewDivider($index)
    {
        if (isset($this->dividers[$index])) {
            $this->viewingDividerIndex = $index;
            $divider = $this->dividers[$index];
            
            // Populate all divider data for viewing
            $this->viewingDividerData = [
                'combination_1' => $divider['combination_1'] ?? '-',
                'combination_2' => $divider['combination_2'] ?? '-',
                'combination_3' => $divider['combination_3'] ?? '-',
                'combination_4' => $divider['combination_4'] ?? '-',
                'combination_5' => $divider['combination_5'] ?? '-',
                'combination_6' => $divider['combination_6'] ?? '-',
                'combination_7' => $divider['combination_7'] ?? '-',
                'ply' => $divider['ply'] ?? '-',
                'quantity' => number_format($divider['quantity'] ?? 0),
                'unit' => $divider['unit'] ?? 'CM',
                'fsc_claim' => $divider['fsc_claim'] ?? '-',
                'supplier_price' => isset($divider['supplier_price']) && $divider['supplier_price'] !== '' ? number_format((float)$divider['supplier_price'], 2) : '-',
            ];
            
            // Show the detailed modal
            $this->showViewDividerModal = true;
        }
    }

    public function closeViewDividerModal()
    {
        $this->showViewDividerModal = false;
        $this->viewingDividerIndex = null;
        $this->viewingDividerData = [];
    }

    public function closeEditDividerModal()
    {
        $this->showEditDividerModal = false;
        $this->editingDividerIndex = null;
        $this->editingDividerData = [];
    }

    public function saveDivider($index)
    {
        if (isset($this->dividers[$index])) {
            // Check if job order is confirmed or beyond - cannot edit items
            if ($this->jobOrder && !in_array($this->jobOrder->status, ['draft', 'pending'])) {
                session()->flash('error', 'Cannot edit dividers from confirmed job orders.');
                $this->closeEditDividerModal();
                return;
            }

            // Build validation rules based on PLY
            $rules = [
                'editingDividerData.quantity' => 'required|integer|min:1',
                'editingDividerData.unit' => 'required',
                'editingDividerData.ply' => 'required|in:3,5,7',
                'editingDividerData.fsc_claim' => 'required',
            ];

            // Add combination fields validation based on PLY
            $ply = $this->editingDividerData['ply'] ?? null;
            if ($ply == '3') {
                $rules['editingDividerData.combination_1'] = 'required|string';
                $rules['editingDividerData.combination_2'] = 'required|string';
                $rules['editingDividerData.combination_3'] = 'required|string';
            } elseif ($ply == '5') {
                $rules['editingDividerData.combination_1'] = 'required|string';
                $rules['editingDividerData.combination_2'] = 'required|string';
                $rules['editingDividerData.combination_3'] = 'required|string';
                $rules['editingDividerData.combination_4'] = 'required|string';
                $rules['editingDividerData.combination_5'] = 'required|string';
            } elseif ($ply == '7') {
                $rules['editingDividerData.combination_1'] = 'required|string';
                $rules['editingDividerData.combination_2'] = 'required|string';
                $rules['editingDividerData.combination_3'] = 'required|string';
                $rules['editingDividerData.combination_4'] = 'required|string';
                $rules['editingDividerData.combination_5'] = 'required|string';
                $rules['editingDividerData.combination_6'] = 'required|string';
                $rules['editingDividerData.combination_7'] = 'required|string';
            }

            // Validate the edited values
            $this->validate($rules);

            try {
                // Get the divider from the current dividers array
                $divider = $this->dividers[$index];
                
                // Update the divider in database
                $jobOrderDivider = \App\Models\JobOrderDivider::find($divider['id']);
                if ($jobOrderDivider) {
                    $jobOrderDivider->combination_1 = $this->editingDividerData['combination_1'];
                    $jobOrderDivider->combination_2 = $this->editingDividerData['combination_2'];
                    $jobOrderDivider->combination_3 = $this->editingDividerData['combination_3'];
                    $jobOrderDivider->combination_4 = $this->editingDividerData['combination_4'] ?? null;
                    $jobOrderDivider->combination_5 = $this->editingDividerData['combination_5'] ?? null;
                    $jobOrderDivider->combination_6 = $this->editingDividerData['combination_6'] ?? null;
                    $jobOrderDivider->combination_7 = $this->editingDividerData['combination_7'] ?? null;
                    $jobOrderDivider->ply = $this->editingDividerData['ply'];
                    $jobOrderDivider->quantity = (int) $this->editingDividerData['quantity'];
                    $jobOrderDivider->unit = $this->editingDividerData['unit'];
                    $jobOrderDivider->fsc_claim = $this->editingDividerData['fsc_claim'];
                    $jobOrderDivider->supplier_price = !empty($this->editingDividerData['supplier_price']) ? (float) $this->editingDividerData['supplier_price'] : null;
                    $jobOrderDivider->save();
                }

                // Reload the job order to get updated data
                $this->loadJobOrder();

                // Close modal and reset
                $this->closeEditDividerModal();

                session()->flash('success', 'Divider updated successfully.');
            } catch (\Exception $e) {
                \Log::error('Error updating divider: ' . $e->getMessage());
                session()->flash('error', 'Error updating divider: ' . $e->getMessage());
            }
        }
    }

    public function removeDivider($index)
    {
        try {
            // Check if job order is confirmed or beyond - cannot delete items
            if ($this->jobOrder && !in_array($this->jobOrder->status, ['draft', 'pending'])) {
                session()->flash('error', 'Cannot delete dividers from confirmed job orders.');
                return;
            }

            // Reset editing if the removed divider was being edited
            if ($this->editingDividerIndex === $index) {
                $this->closeEditDividerModal();
            } elseif ($this->editingDividerIndex !== null && $this->editingDividerIndex > $index) {
                // Adjust editing index if a divider before the editing one was removed
                $this->editingDividerIndex--;
            }

            // Get the divider from the current dividers array
            $divider = $this->dividers[$index];

            // Find and delete the divider from database
            $this->jobOrder->dividers()->where('id', $divider['id'])->delete();

            // Reload the job order to get updated data
            $this->loadJobOrder();

            session()->flash('success', 'Divider removed successfully!');

        } catch (\Exception $e) {
            \Log::error('Error removing divider: ' . $e->getMessage());
            session()->flash('error', 'Error removing divider: ' . $e->getMessage());
        }
    }



    public function saveJobOrder()
    {
        try {
            // Validate form data
            $this->validate([
                'form.date' => 'required|date',
                'form.supplier_id' => 'required|exists:suppliers,id',
                'form.customer_id' => 'required|exists:customers,id',
                'form.purchase_order_no' => 'nullable|string|max:255',
                'form.po_date' => 'nullable|date',
                'form.notes' => 'nullable|string',
                'form.status' => 'required|in:pending,draft,confirmed,in_production,completed,cancelled',
            ]);

            // Update job order details only (boxes and dividers are saved directly when added)
            $this->jobOrder->update([
                'date' => $this->form['date'],
                'supplier_id' => $this->form['supplier_id'],
                'customer_id' => $this->form['customer_id'],
                'customer_address' => $this->form['customer_address'],
                'purchase_order_no' => $this->form['purchase_order_no'],
                'po_date' => $this->form['po_date'],
                'notes' => $this->form['notes'],
                'status' => $this->form['status'],
            ]);

            // Reload the job order
            $this->loadJobOrder();

            // Exit edit mode
            $this->isEditMode = false;

            session()->flash('success', 'Job order updated successfully!');

        } catch (\Exception $e) {
            \Log::error('Error saving job order: ' . $e->getMessage());
            session()->flash('error', 'Error updating job order: ' . $e->getMessage());
        }
    }

    public function saveChanges()
    {
        // This method is no longer needed since boxes and dividers are saved directly
        // when they are added. This method can be removed or kept for backward compatibility.
        session()->flash('info', 'Items are saved automatically when added.');
    }

    public function resetBoxForm()
    {
        $this->boxFormTab = 'basic'; // Reset to first tab
        $this->boxForm = [
            'order_qty' => '',
            'selling_price' => '',
            'activity' => '',
            'printing_instruction' => '',
            'no_of_colours' => '',
            'stitched_glued' => '',
            'sample_available' => false,
            'sample_attached' => false,
            'length' => '',
            'width' => '',
            'height' => '',
            'unit' => 'CM',
            'dimension_type' => 'INTERNAL',
            'top_liner' => 'WHITE',
            'ply' => '3',
            'combination_1' => '',
            'combination_2' => '',
            'combination_3' => '',
            'combination_4' => '',
            'combination_5' => '',
            'combination_6' => '',
            'combination_7' => '',
            'flute' => 'B',
            'fsc_claim' => '100%',
            'no_of_ups' => '',
            'supplier_price' => '',
        ];

        $this->calculatedReelSize = 0;
        $this->calculatedCutSize = 0;
        $this->calculatedBoardQty = 0;
    }

    public function resetDividerForm()
    {
        $this->dividerForm = [
            'quantity' => '',
            'ply' => '3',
            'combination_1' => '',
            'combination_2' => '',
            'combination_3' => '',
            'combination_4' => '',
            'combination_5' => '',
            'combination_6' => '',
            'combination_7' => '',
            'unit' => 'CM',
            'fsc_claim' => '100%',
            'supplier_price' => '',
        ];
    }

    public function updatedFormCustomerId($value)
    {
        if ($value) {
            $customer = Customer::find($value);
            if ($customer) {
                $this->form['customer_address'] = $customer->address;
            }
        }
    }

    public function generatePurchaseOrder()
    {
        if (!$this->jobOrder) {
            session()->flash('error', 'Job order not found.');
            return;
        }

        // Check if job order has items
        if ($this->jobOrder->boxes->count() === 0 && $this->jobOrder->dividers->count() === 0) {
            session()->flash('error', 'Cannot generate purchase order: Job order has no boxes or dividers.');
            return;
        }

        // Check if job order is confirmed
        if ($this->jobOrder->status !== 'confirmed') {
            session()->flash('error', 'Cannot generate purchase order: Job order must be confirmed first.');
            return;
        }

        try {
            // Check if purchase order already exists for this job order
            // Purchase orders are linked to job orders through purchase_order_items table
            $existingPO = \App\Models\PurchaseOrder::whereHas('items', function($query) {
                $query->where('job_order_id', $this->jobOrderId);
            })->first();
            if ($existingPO) {
                session()->flash('error', 'Purchase order already exists for this job order.');
                return redirect()->route('purchase-order-management', ['purchase_order' => $existingPO->id]);
            }

            // Get supplier to copy currency
            $supplier = $this->jobOrder->supplier;
            $currency = $supplier ? ($supplier->currency ?? null) : null;
            
            // Create purchase order
            $purchaseOrder = \App\Models\PurchaseOrder::create([
                'po_number' => \App\Models\PurchaseOrder::generatePONumber(),
                'date' => now()->format('Y-m-d'),
                'supplier_id' => $this->jobOrder->supplier_id,
                'currency' => $currency, // Copy currency from supplier
                'job_order_id' => $this->jobOrderId,
                'status' => 'draft',
                'notes' => "Generated from Job Order: {$this->jobOrder->job_number}",
            ]);

            // Create purchase order items from boxes
            foreach ($this->jobOrder->boxes as $box) {
                $description = "Box - {$box['length']}x{$box['width']}x{$box['height']} {$box['unit']} {$box['dimension_type']} - {$box['ply']} PLY";
                $unitPrice = $box['supplier_price'] ?? 0;
                $totalPrice = $unitPrice * $box['order_qty'];

                \App\Models\PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'item_type' => 'box',
                    'item_id' => $box['id'],
                    'description' => $description,
                    'reel_size' => $box['reel_size'],
                    'cut_size' => $box['cut_size'],
                    'quantity' => $box['order_qty'],
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ]);
            }

            // Create purchase order items from dividers
            foreach ($this->jobOrder->dividers as $divider) {
                $description = "Divider - {$divider['ply']} PLY - Qty: {$divider['quantity']}";
                $unitPrice = $divider['supplier_price'] ?? 0;
                $totalPrice = $unitPrice * $divider['quantity'];

                \App\Models\PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'item_type' => 'divider',
                    'item_id' => $divider['id'],
                    'description' => $description,
                    'reel_size' => 0,
                    'cut_size' => 0,
                    'quantity' => $divider['quantity'],
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ]);
            }

            session()->flash('success', "Purchase order {$purchaseOrder->po_number} has been generated successfully!");

            // Redirect to the purchase order detail page
            return redirect()->route('purchase-order-management', ['purchase_order' => $purchaseOrder->id]);

        } catch (\Exception $e) {
            \Log::error('Error generating purchase order: ' . $e->getMessage());
            session()->flash('error', 'Error generating purchase order: ' . $e->getMessage());
        }
    }

    public function confirmJobOrder()
    {
        try {
            if (!$this->jobOrder) {
                session()->flash('error', 'Job order not found.');
                return;
            }

            // Update job order status to confirmed
            $this->jobOrder->update(['status' => 'confirmed']);

            session()->flash('success', 'Job order has been confirmed successfully!');

            // Redirect to job order list using Livewire's navigation
            return $this->redirect(route('job-order-management'), navigate: true);

        } catch (\Exception $e) {
            \Log::error('Error confirming job order: ' . $e->getMessage());
            session()->flash('error', 'Error confirming job order: ' . $e->getMessage());
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

    public function printJobOrder()
    {
        $this->closePrintPreviewModal();

        // Dispatch event to trigger JavaScript print function
        $this->js('window.printJobOrder();');
    }

    protected function calculateProductionProgress()
    {
        // Compute overall production status for the JOB ORDER
        // Total required = sum of job order item quantities (boxes + dividers)
        $total = 0;
        if ($this->jobOrder) {
            foreach ($this->jobOrder->boxes as $box) {
                $total += (int) ($box->order_qty ?? 0);
            }
            foreach ($this->jobOrder->dividers as $divider) {
                $total += (int) ($divider->quantity ?? 0);
            }

            // Calculate production progress
            // Completed = sum of completed quantities across all production orders for this job order
            // Also include items that are "in production" (received but not yet completed)
            $productionOrders = \App\Models\ProductionOrder::where('job_order_id', $this->jobOrderId)
                ->with(['items'])
                ->get();

            $completed = 0;
            $inProgressQty = 0;

            foreach ($productionOrders as $po) {
                foreach ($po->items as $item) {
                    // Count completed quantity - use finished_goods_quantity (boxes), not completed_quantity (boards)
                    $itemCompleted = (int) ($item->finished_goods_quantity ?? 0);
                    $completed += $itemCompleted;

                    // Calculate work in progress
                    // For FG, calculate remaining from production order quantity × No of UPS
                    $noOfUps = $item->getNoOfUps();
                    $productionOrderFgQty = $noOfUps > 0 ? ($item->quantity * $noOfUps) : $item->quantity;
                    if (in_array($po->status, ['pending', 'in_production', 'ready_for_production'])) {
                        $remaining = $productionOrderFgQty - $itemCompleted;
                        if ($remaining > 0) {
                            $inProgressQty += $remaining;
                        }
                    }
                }
            }

            // Total in progress = completed + items that are started but not completed
            $inProgress = $completed + $inProgressQty;

            $this->productionTotal = $total;
            // Store separately for segmented progress bar
            $this->productionFullyCompleted = $completed;
            $this->productionInProgressQty = $inProgressQty;
            // Show total in progress (completed + items received but not completed)
            $this->productionCompleted = $inProgress > 0 ? $inProgress : $completed;
            $this->productionPercent = $total > 0 ? round(($this->productionCompleted / $total) * 100, 1) : 0;

            if ($total === 0) {
                $this->productionStatusText = 'Not started';
            } elseif ($completed >= $total) {
                $this->productionStatusText = 'Completed';
            } elseif ($inProgress > 0 || $completed > 0) {
                $this->productionStatusText = 'In production';
            } else {
                $this->productionStatusText = 'Not started';
            }
        } else {
            // Reset values if job order is not loaded
            $this->productionTotal = 0;
            $this->productionCompleted = 0;
            $this->productionFullyCompleted = 0;
            $this->productionInProgressQty = 0;
            $this->productionPercent = 0;
            $this->productionStatusText = 'Not started';
        }
    }

    public function refreshProductionProgress()
    {
        // Reload job order data and recalculate progress
        $this->loadJobOrder();
    }

    public function render()
    {
        // Always refresh purchase order count on each render to ensure button state is correct
        // This ensures the print button enables/disables based on current purchase order status
        if ($this->jobOrderId) {
            // Reload job order if it doesn't exist (e.g., when navigating back to page)
            if (!$this->jobOrder) {
                $this->loadJobOrder();
            }
            
            // Always refresh purchase order count - this will update $poCount and $hasPurchaseOrder which controls the button
            // This is critical to ensure button state is accurate when navigating back to page
            $this->refreshPurchaseOrderCount();
            
            // Ensure production progress is calculated on each render
            if ($this->jobOrder) {
                $this->calculateProductionProgress();
            }
        }

        return view('livewire.job-orders.job-order-detail', [
            'suppliers' => Supplier::all(),
            'customers' => Customer::all(),
        ]);
    }
    
    public function updated($propertyName)
    {
        // Refresh purchase order count when component properties are updated
        if ($this->jobOrderId && in_array($propertyName, ['jobOrderId'])) {
            $this->loadJobOrder();
        }
    }
    
    public function refreshPurchaseOrderCount()
    {
        // Refresh purchase order count - can be called from anywhere
        // Always reset to 0 first to ensure clean state
        $this->poCount = 0;
        $this->poProcessedCount = 0;
        $this->poStatusText = 'No purchase orders';
        $this->purchaseOrders = [];
        
        if ($this->jobOrderId) {
            // Purchase orders are linked to job orders through purchase_order_items table
            $purchaseOrders = \App\Models\PurchaseOrder::where('status', '!=', 'cancelled')
                ->whereHas('items', function($query) {
                    $query->where('job_order_id', $this->jobOrderId);
                })
                ->with(['items' => function($query) {
                    $query->where('job_order_id', $this->jobOrderId);
                }, 'supplier'])
                ->get();
            
            $this->poCount = $purchaseOrders->count();
            // Count confirmed purchase orders - check for any non-cancelled purchase orders
            // A purchase order can be printed once it's created and confirmed
            $this->poProcessedCount = $purchaseOrders->filter(function($po) {
                return strtolower($po->status) === 'confirmed' || $po->status === 'confirmed';
            })->count();
            
            // Also update hasPurchaseOrder flag to reflect current state
            $this->hasPurchaseOrder = $this->poCount > 0;
            
            if ($this->poCount === 0) {
                $this->poStatusText = 'No purchase orders';
            } else {
                $this->poStatusText = $this->poProcessedCount . ' / ' . $this->poCount . ' confirmed';
            }
            
            // Store purchase orders for display
            $this->purchaseOrders = $purchaseOrders->map(function($po) {
                return [
                    'id' => $po->id,
                    'po_number' => $po->po_number,
                    'date' => $po->date ? $po->date->format('Y-m-d') : null,
                    'date_formatted' => $po->date ? $po->date->format('M d, Y') : 'N/A',
                    'status' => $po->status,
                    'notes' => $po->notes,
                    'currency_symbol' => $po->getCurrencySymbol(),
                    'supplier' => $po->supplier ? [
                        'name' => $po->supplier->name,
                        'code' => $po->supplier->code,
                        'address' => $po->supplier->address,
                    ] : null,
                    'items' => $po->items->map(function($item) {
                        return [
                            'id' => $item->id,
                            'item_type' => $item->item_type,
                            'description' => $item->description,
                            'reel_size' => $item->reel_size,
                            'cut_size' => $item->cut_size,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'total_price' => $item->total_price,
                        ];
                    })->toArray(),
                    'total_amount' => $po->getTotalAmount(),
                ];
            })->toArray();
        }
    }
    
    public function refreshData()
    {
        // Public method to manually refresh all data
        $this->loadJobOrder();
    }
}
