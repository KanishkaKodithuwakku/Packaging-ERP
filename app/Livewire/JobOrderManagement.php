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

class JobOrderManagement extends Component
{
    use WithPagination;

    // Modal states
    public $showModal = false;
    public $showFilterModal = false;
    public $showDispatchModal = false;
    public $selectedJobOrderForDispatch = null;
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
        'supplier_price' => ''
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
        } else {
            $this->calculatedBoardQty = 0;
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
            'stitched_glued' => $this->boxForm['stitched_glued'],
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
            'reel_size' => $this->calculatedReelSize,
            'cut_size' => $this->calculatedCutSize,
            'board_qty' => $this->calculatedBoardQty,
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
    }

    public function removeDivider($index)
    {
        unset($this->dividers[$index]);
        $this->dividers = array_values($this->dividers);
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

    public function deleteJobOrder($id)
    {
        $jobOrder = JobOrder::findOrFail($id);
        $jobOrder->delete();
        
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
            'supplier_price' => ''
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
        $customers = Customer::where('is_active', true)->orderBy('name')->get();

        return view('livewire.job-order-management', [
            'jobOrders' => $jobOrders,
            'suppliers' => $suppliers,
            'customers' => $customers,
        ]);
    }
}