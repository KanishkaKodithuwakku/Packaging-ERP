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
        $this->calculateDimensions();
        
        \Log::info('Component mounted', [
            'boxForm' => $this->boxForm,
            'form' => $this->form
        ]);
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showModal = true;
        $this->editingJobOrder = false;
        
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




    public function updatedFormSupplierId($value)
    {
        if ($value) {
            $supplier = Supplier::find($value);
            if ($supplier) {
                $this->form['job_number'] = JobOrder::generateJobNumber($supplier->id);
                $this->form['supplier_po_number'] = $this->form['job_number'];
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
        $this->calculateDimensions();
    }

    public function updatedBoxFormDimensionType($value)
    {
        $this->calculateDimensions();
    }

    public function updatedBoxFormLength($value)
    {
        $this->calculateDimensions();
    }

    public function updatedBoxFormWidth($value)
    {
        $this->calculateDimensions();
    }

    public function updatedBoxFormHeight($value)
    {
        $this->calculateDimensions();
    }

    public function updatedBoxForm($value, $field)
    {
        if (in_array($field, ['length', 'width', 'height', 'ply', 'unit', 'dimension_type'])) {
            $this->calculateDimensions();
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
            // Create a temporary box model to use calculation methods
            $tempBox = new JobOrderBox($this->boxForm);
            
            // Calculate reel size with supplier if available, otherwise use default calculation
            $supplierId = $this->form['supplier_id'] ?? null;
            if ($supplierId) {
                $this->calculatedReelSize = $tempBox->calculateReelSize($supplierId);
            } else {
                // Calculate without supplier-specific reel size rounding
                $dimensions = $tempBox->applyPlyAdjustments();
                $reelSize = ($dimensions['width'] + $dimensions['height']) / 2.54;
                $reelSize += 0.75; // Add waste
                $this->calculatedReelSize = $reelSize;
            }
            
            $this->calculatedCutSize = $tempBox->calculateCutSize();
            
            // Debug: Log the calculations
            \Log::info('Calculations:', [
                'supplier_id' => $supplierId,
                'length' => $this->boxForm['length'],
                'width' => $this->boxForm['width'], 
                'height' => $this->boxForm['height'],
                'ply' => $this->boxForm['ply'],
                'dimension_type' => $this->boxForm['dimension_type'],
                'reel_size' => $this->calculatedReelSize,
                'cut_size' => $this->calculatedCutSize
            ]);
        } else {
            $this->calculatedReelSize = 0;
            $this->calculatedCutSize = 0;
        }
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
            'sample_available' => $this->boxForm['sample_available'],
            'sample_attached' => $this->boxForm['sample_attached'],
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
        
        // If we're in the box/divider modal, close it after adding
        if ($this->showBoxDividerModal) {
            $this->closeBoxDividerModal();
        }
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
        
        // If we're in the box/divider modal, close it after adding
        if ($this->showBoxDividerModal) {
            $this->closeBoxDividerModal();
        }
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