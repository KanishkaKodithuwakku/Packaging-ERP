<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Supplier;
use App\Models\JobOrder;
use App\Models\JobOrderBox;
use App\Models\JobOrderDivider;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class JobOrderManagement extends Component
{
    use WithPagination;

    // Modal states
    public $showModal = false;
    public $showFilterModal = false;
    public $showDispatchModal = false;
    public $showDeleteConfirmModal = false;
    public $selectedJobOrderForDispatch = null;
    public $jobOrderToDelete = null;
    public $dispatchComparison = [];
    public $editingJobOrder = false;
    public $activeTab = 'main';

    // Search and filters
    public $search = '';
    public $filterSupplier = '';
    public $filterCustomer = '';
    public $filterStatus = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';

    // Main form data
    public $form = [
        'job_number' => '',
        'date' => '',
        'supplier_id' => '',
        'supplier_po_number' => '',
        'supplier_address' => '',
        'customer_id' => '',
        'customer_address' => '',
        'purchase_order_no' => '',
        'po_date' => '',
        'notes' => '',
        'status' => 'pending'
    ];

    // Box form data
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
        'ply' => '',
        'combination_1' => '',
        'combination_2' => '',
        'combination_3' => '',
        'combination_4' => '',
        'combination_5' => '',
        'flute' => 'B',
        'fsc_claim' => '100%',
        'no_of_ups' => '',
        'supplier_price' => '',
        'reel_size' => '',
        'cut_size' => '',
        'board_qty' => '',
        'notes' => ''
    ];

    // Divider form data
    public $dividerForm = [
        'combination_1' => '',
        'combination_2' => '',
        'combination_3' => '',
        'combination_4' => '',
        'combination_5' => '',
        'combination_6' => '',
        'combination_7' => '',
        'ply' => '',
        'quantity' => '',
        'unit' => 'CM',
        'fsc_claim' => '100%',
        'supplier_price' => ''
    ];

    // Calculated values
    public $calculatedReelSize = 0;
    public $calculatedCutSize = 0;
    public $calculatedBoardQty = 0;

    // Current job order items
    public $currentJobOrderId = null;
    public $boxes = [];
    public $dividers = [];

    // Box editing state
    public $editingBoxIndex = null;
    public $showEditBoxModal = false;
    public $editingBoxData = [];

    // Divider editing state
    public $editingDividerIndex = null;
    public $showEditDividerModal = false;
    public $editingDividerData = [];

    protected $messages = [
        'form.po_date.required' => 'Please fill PO date',
        'form.po_date.date' => 'Please select a valid date',
    ];

    public function mount()
    {
        $this->form['date'] = now()->format('Y-m-d');
        $this->resetBoxForm();

        // Set default values and calculate immediately
        $this->boxForm['length'] = 0;
        $this->boxForm['width'] = 0;
        $this->boxForm['height'] = 0;
        $this->boxForm['unit'] = 'INCHES';
        $this->boxForm['dimension_type'] = 'INTERNAL';
        $this->boxForm['ply'] = '3';

        $this->calculateDimensions();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->editingJobOrder = false;

        // Force calculation when modal opens
        $this->calculateDimensions();

        \Log::info('Create modal opened', [
            'form' => $this->form
        ]);
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->editingJobOrder = false;
        $this->resetForm();
    }

    public function openFilterModal()
    {
        $this->showFilterModal = true;
    }

    public function closeFilterModal()
    {
        $this->showFilterModal = false;
    }

    public function resetFilters()
    {
        $this->filterSupplier = '';
        $this->filterCustomer = '';
        $this->filterStatus = '';
        $this->filterDateFrom = '';
        $this->filterDateTo = '';
        $this->search = '';
    }

    public function openDispatchModal($jobOrderId)
    {
        $this->selectedJobOrderForDispatch = \App\Models\JobOrder::with(['boxes', 'dividers', 'supplier', 'customer'])->findOrFail($jobOrderId);
        $this->loadDispatchComparison($jobOrderId);
        $this->showDispatchModal = true;
    }

    public function closeDispatchModal()
    {
        $this->showDispatchModal = false;
        $this->selectedJobOrderForDispatch = null;
        $this->dispatchComparison = [];
    }

    public function loadDispatchComparison($jobOrderId)
    {
        $jobOrder = \App\Models\JobOrder::with(['boxes', 'dividers'])->findOrFail($jobOrderId);

        // Get all delivery notes for this job order
        $deliveryNotes = \App\Models\DeliveryNote::where('job_order_id', $jobOrderId)
            ->with('items')
            ->get();

        $comparison = [];

        // Process boxes
        foreach ($jobOrder->boxes as $box) {
            $materialCode = 'BOX-' . $box->id . '-' . $box->ply . 'PLY';
            $orderQty = $box->order_qty;

            // Calculate total dispatched from all delivery notes for this box
            $dispatchedQty = 0;
            foreach ($deliveryNotes as $dn) {
                foreach ($dn->items as $item) {
                    if ($item->item_type === 'box' && $item->item_id == $box->id) {
                        $dispatchedQty += $item->dispatched_qty;
                    }
                }
            }

            $comparison[] = [
                'type' => 'box',
                'id' => $box->id,
                'description' => "Box - {$box->length}x{$box->width}x{$box->height}cm",
                'material_code' => $materialCode,
                'order_qty' => $orderQty,
                'dispatched_qty' => $dispatchedQty,
                'remaining_qty' => max(0, $orderQty - $dispatchedQty),
                'progress' => $orderQty > 0 ? min(100, ($dispatchedQty / $orderQty) * 100) : 0,
            ];
        }

        // Process dividers
        foreach ($jobOrder->dividers as $divider) {
            $materialCode = 'DIVIDER-' . $divider->id . '-' . $divider->ply . 'PLY';
            $orderQty = $divider->quantity;

            // Calculate total dispatched from all delivery notes for this divider
            $dispatchedQty = 0;
            foreach ($deliveryNotes as $dn) {
                foreach ($dn->items as $item) {
                    if ($item->item_type === 'divider' && $item->item_id == $divider->id) {
                        $dispatchedQty += $item->dispatched_qty;
                    }
                }
            }

            $comparison[] = [
                'type' => 'divider',
                'id' => $divider->id,
                'description' => "Divider - {$divider->ply} PLY",
                'material_code' => $materialCode,
                'order_qty' => $orderQty,
                'dispatched_qty' => $dispatchedQty,
                'remaining_qty' => max(0, $orderQty - $dispatchedQty),
                'progress' => $orderQty > 0 ? min(100, ($dispatchedQty / $orderQty) * 100) : 0,
            ];
        }

        $this->dispatchComparison = $comparison;
    }




    public function updatedFormSupplierId($value)
    {
        if ($value) {
            $supplier = Supplier::find($value);
            if ($supplier) {
                $this->form['job_number'] = JobOrder::generateJobNumber($supplier->id);
                $this->form['supplier_po_number'] = $this->form['job_number'];
                $this->form['supplier_address'] = $supplier->address ?? '';
            }
        }
        // Trigger dimension calculations when supplier changes
        $this->calculateDimensions();
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

    public function updatedBoxFormPly($value)
    {
        $this->resetCombinationFields();
        $this->calculateDimensions();
    }

    public function updatedDividerFormPly($value)
    {
        // Reset combination fields for dividers
        for ($i = 1; $i <= 7; $i++) {
            $this->dividerForm["combination_{$i}"] = '';
        }
    }

    public function updatedBoxFormUnit($value)
    {
        \Log::info('=== UNIT CHANGED METHOD CALLED ===', ['unit' => $value, 'form' => $this->boxForm]);
        $this->calculateDimensions();
        $this->dispatch('$refresh');
        session()->flash('success', 'Unit changed to: ' . $value);
    }

    public function updatedBoxFormDimensionType($value)
    {
        \Log::info('=== TYPE CHANGED METHOD CALLED ===', ['type' => $value, 'form' => $this->boxForm]);
        $this->calculateDimensions();
        $this->dispatch('$refresh');
        session()->flash('success', 'Type changed to: ' . $value);
    }

    public function updatedBoxFormLength($value)
    {
        $this->calculateDimensions();
        $this->dispatch('$refresh');
    }

    public function updatedBoxFormWidth($value)
    {
        $this->calculateDimensions();
        $this->dispatch('$refresh');
    }

    public function updatedBoxFormHeight($value)
    {
        $this->calculateDimensions();
        $this->dispatch('$refresh');
    }

    public function updatedBoxForm($value, $field)
    {
        \Log::info('=== BOX FORM FIELD CHANGED ===', [
            'field' => $field,
            'value' => $value,
            'boxForm' => $this->boxForm
        ]);

        // Don't trigger calculations if user is manually editing calculated fields
        if (in_array($field, ['reel_size', 'cut_size', 'board_qty'])) {
            // User is manually editing, don't recalculate
            return;
        }

        if (in_array($field, ['length', 'width', 'height', 'ply', 'unit', 'dimension_type'])) {
            $this->calculateDimensions();
            $this->dispatch('$refresh');
        }

        if (in_array($field, ['order_qty', 'no_of_ups'])) {
            $this->calculateBoardQty();
        }
    }

    public function resetCombinationFields()
    {
        for ($i = 1; $i <= 7; $i++) {
            $this->boxForm["combination_{$i}"] = '';
        }
    }

    public function calculateDimensions()
    {
        if ($this->boxForm['length'] && $this->boxForm['width'] && $this->boxForm['height']) {
            // Calculate dimensions directly in Livewire
            $length = (float) $this->boxForm['length'];
            $width = (float) $this->boxForm['width'];
            $height = (float) $this->boxForm['height'];
            $unit = $this->boxForm['unit'];
            $type = $this->boxForm['dimension_type'];

            // Debug input values
            \Log::info('Calculation input values', [
                'length' => $length,
                'width' => $width,
                'height' => $height,
                'unit' => $unit,
                'type' => $type,
                'unit_type' => gettype($unit)
            ]);

            // Convert to inches
            $lengthInches = $this->convertToInches($length, $unit);
            $widthInches = $this->convertToInches($width, $unit);
            $heightInches = $this->convertToInches($height, $unit);

            // Debug conversion
            \Log::info('Unit conversion debug', [
                'unit' => $unit,
                'original_length' => $length,
                'original_width' => $width,
                'original_height' => $height,
                'converted_length' => $lengthInches,
                'converted_width' => $widthInches,
                'converted_height' => $heightInches
            ]);

            // Calculate reel size: (W + H) + 0.75
            $reelSize = $widthInches + $heightInches + 0.75;
            $this->calculatedReelSize = $this->roundToNextReelSize($reelSize, $this->boxForm['supplier_id'] ?? null);

            // Calculate cut size: ((L + W) * 2) + addition
            $cutSize = ($lengthInches + $widthInches) * 2;
            if ($type === 'EXTERNAL') {
                $cutSize += 2; // EXTERNAL adds 2 inches
            } else {
                $cutSize += 2.5; // INTERNAL adds 2.5 inches
            }
            $this->calculatedCutSize = $cutSize;

            // Debug logging
            \Log::info('Dimensions calculated', [
                'unit' => $unit,
                'type' => $type,
                'reel_size' => $this->calculatedReelSize,
                'cut_size' => $this->calculatedCutSize
            ]);

            // Auto-populate form fields with calculated values if they're empty (don't overwrite manual edits)
            // Check if value is null, empty string, or 0 (but not if user entered 0 explicitly)
            if ((!isset($this->boxForm['reel_size']) || $this->boxForm['reel_size'] === '' || $this->boxForm['reel_size'] === null) && $this->calculatedReelSize > 0) {
                $this->boxForm['reel_size'] = $this->calculatedReelSize;
            }
            if ((!isset($this->boxForm['cut_size']) || $this->boxForm['cut_size'] === '' || $this->boxForm['cut_size'] === null) && $this->calculatedCutSize > 0) {
                $this->boxForm['cut_size'] = $this->calculatedCutSize;
            }
        } else {
            $this->calculatedReelSize = 0;
            $this->calculatedCutSize = 0;
        }
    }

    private function convertToInches(float $dimension, string $unit): float
    {
        \Log::info('convertToInches called', [
            'dimension' => $dimension,
            'unit_received' => $unit,
            'unit_type' => gettype($unit),
            'unit_length' => strlen($unit)
        ]);

        switch ($unit) {
            case 'MM':
                $result = $dimension / 25.4;
                \Log::info('MM conversion', ['result' => $result]);
                return $result;
            case 'CM':
                $result = $dimension / 2.54;
                \Log::info('CM conversion', ['result' => $result]);
                return $result;
            case 'INCHES':
            default:
                \Log::info('INCHES - no conversion', ['result' => $dimension]);
                return $dimension; // Already in inches
        }
    }

    private function roundToNextReelSize(float $size, $supplierId = null): float
    {
        // Get supplier-specific reel sizes if supplier is provided
        if ($supplierId) {
            $supplier = \App\Models\Supplier::find($supplierId);
            if ($supplier) {
                $reelSizes = $supplier->getReelSizesArray();
                if (!empty($reelSizes)) {
                    // Find the next available reel size from supplier
                    foreach ($reelSizes as $reelSize) {
                        if ($size <= $reelSize) {
                            return (float) $reelSize;
                        }
                    }
                    // If size is larger than all supplier sizes, return the largest
                    return (float) end($reelSizes);
                }
            }
        }

        // Fallback to standard rounding if no supplier or no supplier reel sizes
        if ($size <= 13.50) {
            return 13.50;
        } elseif ($size <= 15.00) {
            return 15.00;
        } elseif ($size <= 17.00) {
            return 17.00;
        } elseif ($size <= 19.00) {
            return 19.00;
        } elseif ($size <= 21.00) {
            return 21.00;
        } elseif ($size <= 23.00) {
            return 23.00;
        } elseif ($size <= 25.00) {
            return 25.00;
        } elseif ($size <= 27.00) {
            return 27.00;
        } elseif ($size <= 29.00) {
            return 29.00;
        } elseif ($size <= 31.00) {
            return 31.00;
        } elseif ($size <= 33.00) {
            return 33.00;
        } elseif ($size <= 35.00) {
            return 35.00;
        } elseif ($size <= 37.00) {
            return 37.00;
        } elseif ($size <= 39.00) {
            return 39.00;
        } elseif ($size <= 41.00) {
            return 41.00;
        } elseif ($size <= 43.00) {
            return 43.00;
        } elseif ($size <= 45.00) {
            return 45.00;
        } elseif ($size <= 47.00) {
            return 47.00;
        } elseif ($size <= 49.00) {
            return 49.00;
        } else {
            // For sizes above 49, round to next 2-inch increment
            return ceil($size / 2) * 2;
        }
    }

    public function forceCalculation()
    {
        $this->calculateDimensions();
        $this->dispatch('$refresh');
        session()->flash('success', 'Calculation forced');
    }





    public function calculateBoardQty()
    {
        if ($this->boxForm['order_qty'] && $this->boxForm['no_of_ups'] && $this->boxForm['no_of_ups'] > 0) {
            $this->calculatedBoardQty = ceil($this->boxForm['order_qty'] / $this->boxForm['no_of_ups']);
            // Auto-populate form field with calculated value if it's empty (don't overwrite manual edits)
            // Check if value is null, empty string, or 0 (but not if user entered 0 explicitly)
            if ((!isset($this->boxForm['board_qty']) || $this->boxForm['board_qty'] === '' || $this->boxForm['board_qty'] === null) && $this->calculatedBoardQty > 0) {
                $this->boxForm['board_qty'] = $this->calculatedBoardQty;
            }
        } else {
            $this->calculatedBoardQty = 0;
        }
    }

    public function saveCalculatedFields()
    {
        // Recalculate dimensions if needed
        if ($this->boxForm['length'] && $this->boxForm['width'] && $this->boxForm['height']) {
            $this->calculateDimensions();
        }

        // Recalculate board qty if needed
        if ($this->boxForm['order_qty'] && $this->boxForm['no_of_ups']) {
            $this->calculateBoardQty();
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

    public function calculateBoardQtyForEdit()
    {
        if (isset($this->editingBoxData['order_qty']) && isset($this->editingBoxData['no_of_ups']) &&
            $this->editingBoxData['order_qty'] && $this->editingBoxData['no_of_ups'] && $this->editingBoxData['no_of_ups'] > 0) {
            $calculated = ceil($this->editingBoxData['order_qty'] / $this->editingBoxData['no_of_ups']);
            // Auto-populate board_qty with calculated value (formatted to 2 decimal places)
            $this->editingBoxData['board_qty'] = number_format($calculated, 2, '.', '');
        }
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

            $length = (float) $this->editingBoxData['length'];
            $width = (float) $this->editingBoxData['width'];
            $height = (float) $this->editingBoxData['height'];
            $unit = $this->editingBoxData['unit'] ?? 'CM';
            $type = $this->editingBoxData['dimension_type'] ?? 'INTERNAL';

            // Convert to inches
            $lengthInches = $this->convertToInches($length, $unit);
            $widthInches = $this->convertToInches($width, $unit);
            $heightInches = $this->convertToInches($height, $unit);

            // Calculate reel size: (W + H) + 0.75
            $reelSize = $widthInches + $heightInches + 0.75;
            $calculatedReelSize = $this->roundToNextReelSize($reelSize, $this->form['supplier_id'] ?? null);

            // Calculate cut size: ((L + W) * 2) + addition
            $cutSize = ($lengthInches + $widthInches) * 2;
            if ($type === 'EXTERNAL') {
                $cutSize += 2; // EXTERNAL adds 2 inches
            } else {
                $cutSize += 2.5; // INTERNAL adds 2.5 inches
            }

            // Auto-populate form fields with calculated values (formatted to 2 decimal places)
            $this->editingBoxData['reel_size'] = number_format($calculatedReelSize, 2, '.', '');
            $this->editingBoxData['cut_size'] = number_format($cutSize, 2, '.', '');
        }
    }

    public function addBox()
    {
        \Log::info('Attempting to add box', [
            'boxForm' => $this->boxForm,
            'boxForm_keys' => array_keys($this->boxForm),
            'boxForm_values' => array_values($this->boxForm)
        ]);

        // Check if required fields are empty
        $requiredFields = ['order_qty', 'selling_price', 'length', 'width', 'height', 'ply'];
        $emptyFields = [];
        foreach ($requiredFields as $field) {
            if (empty($this->boxForm[$field])) {
                $emptyFields[] = $field;
            }
        }

        if (!empty($emptyFields)) {
            \Log::error('Required fields are empty', ['empty_fields' => $emptyFields]);
            $this->addError('boxForm', 'Please fill in all required fields: ' . implode(', ', $emptyFields));
            return;
        }

        try {
            $this->validate([
                'boxForm.order_qty' => 'required|integer|min:1',
                'boxForm.selling_price' => 'required|numeric|min:0',
                'boxForm.length' => 'required|numeric|min:0.01',
                'boxForm.width' => 'required|numeric|min:0.01',
                'boxForm.height' => 'required|numeric|min:0.01',
                'boxForm.unit' => 'required',
                'boxForm.dimension_type' => 'required',
                'boxForm.top_liner' => 'required',
                'boxForm.ply' => 'required',
                'boxForm.flute' => 'required',
                'boxForm.fsc_claim' => 'required',
                'boxForm.no_of_colours' => 'required|integer|min:0',
                'boxForm.reel_size' => 'required|numeric|min:0',
                'boxForm.cut_size' => 'required|numeric|min:0',
                'boxForm.board_qty' => 'required|numeric|min:0',
            ], [
                'boxForm.reel_size.required' => 'Reel Size is required. Please enter a value or click "Save Changes" to use calculated value.',
                'boxForm.reel_size.numeric' => 'Reel Size must be a number',
                'boxForm.cut_size.required' => 'Cut Size is required. Please enter a value or click "Save Changes" to use calculated value.',
                'boxForm.cut_size.numeric' => 'Cut Size must be a number',
                'boxForm.board_qty.required' => 'Board Qty is required. Please enter a value or click "Save Changes" to use calculated value.',
                'boxForm.board_qty.numeric' => 'Board Qty must be a number',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Box validation failed', ['errors' => $e->errors()]);
            throw $e;
        }

        $this->boxes[] = [
            'id' => 'temp_' . count($this->boxes),
            'order_qty' => $this->boxForm['order_qty'],
            'selling_price' => $this->boxForm['selling_price'],
            'activity' => $this->boxForm['activity'],
            'printing_instruction' => $this->boxForm['printing_instruction'],
            'no_of_colours' => $this->boxForm['no_of_colours'],
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
            'combination_1' => $this->boxForm['combination_1'],
            'combination_2' => $this->boxForm['combination_2'],
            'combination_3' => $this->boxForm['combination_3'],
            'combination_4' => $this->boxForm['combination_4'],
            'combination_5' => $this->boxForm['combination_5'],
            'flute' => $this->boxForm['flute'],
            'fsc_claim' => $this->boxForm['fsc_claim'],
            'no_of_ups' => $this->boxForm['no_of_ups'],
            'supplier_price' => $this->boxForm['supplier_price'],
            'notes' => !empty($this->boxForm['notes']) ? $this->boxForm['notes'] : null,
            // Use manual values if entered (check for null/empty, not just falsy), otherwise use calculated values
            'reel_size' => (isset($this->boxForm['reel_size']) && $this->boxForm['reel_size'] !== '' && $this->boxForm['reel_size'] !== null) ? (float) $this->boxForm['reel_size'] : (($this->calculatedReelSize > 0) ? $this->calculatedReelSize : 0),
            'cut_size' => (isset($this->boxForm['cut_size']) && $this->boxForm['cut_size'] !== '' && $this->boxForm['cut_size'] !== null) ? (float) $this->boxForm['cut_size'] : (($this->calculatedCutSize > 0) ? $this->calculatedCutSize : 0),
            'board_qty' => (isset($this->boxForm['board_qty']) && $this->boxForm['board_qty'] !== '' && $this->boxForm['board_qty'] !== null) ? (float) $this->boxForm['board_qty'] : (($this->calculatedBoardQty > 0) ? $this->calculatedBoardQty : 0),
        ];

        $this->resetBoxForm();
        $this->activeTab = 'boxes';

        \Log::info('Box added successfully', ['total_boxes' => count($this->boxes)]);

        // Modal stays open to allow adding more boxes/dividers

    }

    public function addDivider()
    {
        $this->validate([
            'dividerForm.quantity' => 'required|integer|min:1',
            'dividerForm.unit' => 'required',
            'dividerForm.ply' => 'required',
            'dividerForm.fsc_claim' => 'required',
        ]);

        $this->dividers[] = [
            'id' => 'temp_' . count($this->dividers),
            'combination_1' => $this->dividerForm['combination_1'],
            'combination_2' => $this->dividerForm['combination_2'],
            'combination_3' => $this->dividerForm['combination_3'],
            'combination_4' => $this->dividerForm['combination_4'],
            'combination_5' => $this->dividerForm['combination_5'],
            'combination_6' => $this->dividerForm['combination_6'],
            'combination_7' => $this->dividerForm['combination_7'],
            'ply' => $this->dividerForm['ply'],
            'quantity' => $this->dividerForm['quantity'],
            'unit' => $this->dividerForm['unit'],
            'fsc_claim' => $this->dividerForm['fsc_claim'],
            'supplier_price' => $this->dividerForm['supplier_price'],
        ];

        $this->resetDividerForm();
        $this->activeTab = 'dividers';

        // Modal stays open to allow adding more boxes/dividers

    }

    public function removeBox($index)
    {
        unset($this->boxes[$index]);
        $this->boxes = array_values($this->boxes);
        // Reset editing if the removed box was being edited
        if ($this->editingBoxIndex === $index) {
            $this->editingBoxIndex = null;
            $this->editingBoxData = ['reel_size' => '', 'cut_size' => '', 'board_qty' => ''];
        } elseif ($this->editingBoxIndex !== null && $this->editingBoxIndex > $index) {
            // Adjust editing index if a box before the editing one was removed
            $this->editingBoxIndex--;
        }
    }

    public function editBox($index)
    {
        if (isset($this->boxes[$index])) {
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
                'sample_available' => $box['sample_available'] ?? false,
                'sample_attached' => $box['sample_attached'] ?? false,
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

    public function saveBox($index)
    {
        if (isset($this->boxes[$index])) {
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

            // Update all box data
            $this->boxes[$index] = array_merge($this->boxes[$index], $this->editingBoxData);

            // Convert boolean fields
            $this->boxes[$index]['sample_available'] = $this->editingBoxData['sample_available'] === true || $this->editingBoxData['sample_available'] === 'Yes';
            $this->boxes[$index]['sample_attached'] = $this->editingBoxData['sample_attached'] === true || $this->editingBoxData['sample_attached'] === 'Yes';

            // Convert stitched_glued to lowercase to match database enum, or null if None
            if (isset($this->editingBoxData['stitched_glued']) && $this->editingBoxData['stitched_glued'] !== '' && $this->editingBoxData['stitched_glued'] !== 'None') {
                $this->boxes[$index]['stitched_glued'] = strtolower($this->editingBoxData['stitched_glued']);
            } else {
                $this->boxes[$index]['stitched_glued'] = null;
            }

            // Ensure numeric fields are floats
            $this->boxes[$index]['reel_size'] = (float) $this->editingBoxData['reel_size'];
            $this->boxes[$index]['cut_size'] = (float) $this->editingBoxData['cut_size'];
            $this->boxes[$index]['board_qty'] = (float) $this->editingBoxData['board_qty'];

            // Close modal and reset
            $this->closeEditBoxModal();

            session()->flash('success', 'Box updated successfully.');
        }
    }

    public function editDivider($index)
    {
        if (isset($this->dividers[$index])) {
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

    public function closeEditDividerModal()
    {
        $this->showEditDividerModal = false;
        $this->editingDividerIndex = null;
        $this->editingDividerData = [];
    }

    public function saveDivider($index)
    {
        if (isset($this->dividers[$index])) {
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

            // Update all divider data
            $this->dividers[$index] = array_merge($this->dividers[$index], $this->editingDividerData);

            // Ensure numeric fields are properly formatted
            $this->dividers[$index]['quantity'] = (int) $this->editingDividerData['quantity'];
            if (!empty($this->editingDividerData['supplier_price'])) {
                $this->dividers[$index]['supplier_price'] = (float) $this->editingDividerData['supplier_price'];
            }

            // Close modal and reset
            $this->closeEditDividerModal();

            session()->flash('success', 'Divider updated successfully.');
        }
    }

    public function removeDivider($index)
    {
        // Reset editing if the removed divider was being edited
        if ($this->editingDividerIndex === $index) {
            $this->closeEditDividerModal();
        } elseif ($this->editingDividerIndex !== null && $this->editingDividerIndex > $index) {
            // Adjust editing index if a divider before the editing one was removed
            $this->editingDividerIndex--;
        }

        unset($this->dividers[$index]);
        $this->dividers = array_values($this->dividers);
        $this->activeTab = 'dividers';
    }

    public function saveJobOrder()
    {
        try {
            \Log::info('Starting job order save process', [
                'editingJobOrder' => $this->editingJobOrder,
                'currentJobOrderId' => $this->currentJobOrderId,
                'form' => $this->form,
                'boxes_count' => count($this->boxes),
                'dividers_count' => count($this->dividers)
            ]);

            $this->validate([
                'form.job_number' => 'required|string',
                'form.date' => 'required|date',
                'form.supplier_id' => 'required|exists:suppliers,id',
                'form.customer_id' => 'required|exists:customers,id',
                'form.customer_address' => 'required|string',
                'form.po_date' => 'required|date',
                'form.status' => 'required|in:pending,draft,confirmed,in_production,completed,cancelled',
            ]);

            if ($this->editingJobOrder && $this->currentJobOrderId) {
                // UPDATE EXISTING JOB ORDER
                \Log::info('Updating existing job order', ['job_order_id' => $this->currentJobOrderId]);

                $jobOrder = JobOrder::findOrFail($this->currentJobOrderId);
                $jobOrder->update($this->form);

                // Delete existing boxes and dividers
                $jobOrder->boxes()->delete();
                $jobOrder->dividers()->delete();

                \Log::info('Updated existing job order with ID: ' . $jobOrder->id);
                $successMessage = 'Job Order updated successfully!';
            } else {
                // CREATE NEW JOB ORDER
                \Log::info('Creating new job order');

                // Generate fresh job number to avoid duplicates
                $this->form['job_number'] = JobOrder::generateJobNumber($this->form['supplier_id']);
                $this->form['supplier_po_number'] = $this->form['job_number'];

                $jobOrder = JobOrder::create($this->form);
                \Log::info('Created new job order with ID: ' . $jobOrder->id);
                $successMessage = 'Job Order created successfully!';
            }

            // Create/Update boxes
            foreach ($this->boxes as $boxData) {
                unset($boxData['id']); // Remove temporary ID
                $boxData['job_order_id'] = $jobOrder->id;
                \Log::info('Creating box with data:', $boxData);
                JobOrderBox::create($boxData);
            }

            // Create/Update dividers
            foreach ($this->dividers as $dividerData) {
                unset($dividerData['id']); // Remove temporary ID
                $dividerData['job_order_id'] = $jobOrder->id;
                \Log::info('Creating divider with data:', $dividerData);
                JobOrderDivider::create($dividerData);
            }

            $this->closeModal();
            session()->flash('success', $successMessage);
            \Log::info('Job order save completed successfully');

        } catch (\Exception $e) {
            \Log::error('Job order save failed:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Failed to create job order: ' . $e->getMessage());
        }
    }

    public function editJobOrder($id)
    {
        return redirect('/job-order-detail/' . $id . '?edit=true');
    }

    public function openDeleteConfirmModal($id)
    {
        $this->jobOrderToDelete = $id;
        $this->showDeleteConfirmModal = true;
    }

    public function closeDeleteConfirmModal()
    {
        $this->showDeleteConfirmModal = false;
        $this->jobOrderToDelete = null;
    }

    public function deleteJobOrder($id)
    {
        $jobOrder = JobOrder::findOrFail($id);
        $jobOrder->delete();

        // Close modal
        $this->closeDeleteConfirmModal();

        session()->flash('success', 'Job Order deleted successfully.');
    }

    public function resetForm()
    {
        $this->form = [
            'job_number' => '',
            'date' => now()->format('Y-m-d'),
            'supplier_id' => '',
            'supplier_po_number' => '',
            'supplier_address' => '',
            'customer_id' => '',
            'customer_address' => '',
            'purchase_order_no' => '',
            'po_date' => '',
            'notes' => '',
            'status' => 'pending'
        ];

        $this->resetBoxForm();
        $this->resetDividerForm();

        $this->boxes = [];
        $this->dividers = [];
        $this->currentJobOrderId = null;

        $this->resetErrorBag();
    }

    public function resetBoxForm()
    {
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
            'unit' => 'INCHES',
            'dimension_type' => 'INTERNAL',
            'top_liner' => 'WHITE',
            'ply' => '',
            'combination_1' => '',
            'combination_2' => '',
            'combination_3' => '',
            'combination_4' => '',
            'combination_5' => '',
            'flute' => 'B',
            'fsc_claim' => '100%',
            'no_of_ups' => '',
            'supplier_price' => '',
            'reel_size' => '',
            'cut_size' => '',
            'board_qty' => '',
            'notes' => ''
        ];

        $this->calculatedReelSize = 0;
        $this->calculatedCutSize = 0;
        $this->calculatedBoardQty = 0;
    }

    public function resetDividerForm()
    {
        $this->dividerForm = [
            'combination_1' => '',
            'combination_2' => '',
            'combination_3' => '',
            'combination_4' => '',
            'combination_5' => '',
            'combination_6' => '',
            'combination_7' => '',
            'ply' => '',
            'quantity' => '',
            'unit' => 'CM',
            'fsc_claim' => '100%',
            'supplier_price' => ''
        ];
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        $query = JobOrder::with(['supplier', 'customer', 'boxes', 'dividers']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('job_number', 'like', '%' . $this->search . '%')
                  ->orWhere('supplier_po_number', 'like', '%' . $this->search . '%')
                  ->orWhere('purchase_order_no', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterSupplier) {
            $query->where('supplier_id', $this->filterSupplier);
        }

        if ($this->filterCustomer) {
            $query->where('customer_id', $this->filterCustomer);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterDateFrom) {
            $query->where('date', '>=', $this->filterDateFrom);
        }

        if ($this->filterDateTo) {
            $query->where('date', '<=', $this->filterDateTo);
        }

        $jobOrders = $query->orderBy('date', 'desc')
                          ->orderBy('created_at', 'desc')
                          ->paginate(15);

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        
        // Check if status column exists, otherwise get all customers
        $customers = Customer::query();
        if (Schema::hasColumn('customers', 'status')) {
            $customers->where('status', 'active');
        }
        $customers = $customers->orderBy('name')->get();

        return view('livewire.job-order-management', [
            'jobOrders' => $jobOrders,
            'suppliers' => $suppliers,
            'customers' => $customers,
        ]);
    }
}
