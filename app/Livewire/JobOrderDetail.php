<?php

namespace App\Livewire;

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
    public $isEditMode = false;
    public $hasPurchaseOrder = false;
    public $showPrintPreviewModal = false;
    public $printDisplayFormat = 'dimensions';
    
    // Form data
    public $form = [];
    public $boxes = [];
    public $dividers = [];
    
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

    public function mount($id, $edit = false)
    {
        $this->jobOrderId = $id;
        
        // Check for edit parameter in URL query string
        $editFromQuery = request()->query('edit');
        
        $this->isEditMode = $edit === 'edit' || $edit === 'true' || $edit === true || $editFromQuery === 'true';
        $this->loadJobOrder();
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
        $this->hasPurchaseOrder = \App\Models\PurchaseOrder::where('job_order_id', $this->jobOrderId)->exists();
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
    }

    public function updatedBoxFormNoOfUps()
    {
        $this->calculateDimensions();
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
                'board_qty' => $this->calculatedBoardQty
            ]);
        }
    }

    public function addBox()
    {
        $this->validate([
            'boxForm.order_qty' => 'required|numeric|min:1',
            'boxForm.selling_price' => 'required|numeric|min:0',
            'boxForm.length' => 'required|numeric|min:0.01',
            'boxForm.width' => 'required|numeric|min:0.01',
            'boxForm.height' => 'required|numeric|min:0.01',
            'boxForm.ply' => 'required|in:3,5,7',
        ]);

        try {
            // Create the box directly in the database
            $boxData = [
                'job_order_id' => $this->jobOrderId,
                'order_qty' => $this->boxForm['order_qty'],
                'selling_price' => $this->boxForm['selling_price'],
                'activity' => $this->boxForm['activity'] ?: null,
                'printing_instruction' => $this->boxForm['printing_instruction'] ?: null,
                'no_of_colours' => $this->boxForm['no_of_colours'] ? (int)$this->boxForm['no_of_colours'] : null,
                'stitched_glued' => $this->boxForm['stitched_glued'] ?: null,
                'sample_available' => $this->boxForm['sample_available'],
                'sample_attached' => $this->boxForm['sample_attached'],
                'length' => $this->boxForm['length'],
                'width' => $this->boxForm['width'],
                'height' => $this->boxForm['height'],
                'unit' => $this->boxForm['unit'],
                'dimension_type' => $this->boxForm['dimension_type'],
                'top_liner' => $this->boxForm['top_liner'],
                'ply' => $this->boxForm['ply'],
                'combination_1' => $this->boxForm['combination_1'] ?: null,
                'combination_2' => $this->boxForm['combination_2'] ?: null,
                'combination_3' => $this->boxForm['combination_3'] ?: null,
                'combination_4' => $this->boxForm['combination_4'] ?: null,
                'combination_5' => $this->boxForm['combination_5'] ?: null,
                'combination_6' => $this->boxForm['combination_6'] ?: null,
                'combination_7' => $this->boxForm['combination_7'] ?: null,
                'flute' => $this->boxForm['flute'],
                'fsc_claim' => $this->boxForm['fsc_claim'],
                'no_of_ups' => $this->boxForm['no_of_ups'] ? (int)$this->boxForm['no_of_ups'] : null,
                'supplier_price' => $this->boxForm['supplier_price'] ?: null,
                'reel_size' => $this->calculatedReelSize,
                'cut_size' => $this->calculatedCutSize,
                'board_qty' => $this->calculatedBoardQty,
            ];

            $this->jobOrder->boxes()->create($boxData);
            
            // Reload the job order to get updated data
            $this->loadJobOrder();
            
            $this->resetBoxForm();
            $this->activeTab = 'boxes';
            
            session()->flash('success', 'Box added successfully!');
            
            // Close modal after adding
            $this->closeBoxDividerModal();
            
        } catch (\Exception $e) {
            \Log::error('Error adding box: ' . $e->getMessage());
            session()->flash('error', 'Error adding box: ' . $e->getMessage());
        }
    }

    public function addDivider()
    {
        $this->validate([
            'dividerForm.quantity' => 'required|numeric|min:1',
            'dividerForm.ply' => 'required|in:3,5,7',
        ]);

        try {
            // Create the divider directly in the database
            $dividerData = [
                'job_order_id' => $this->jobOrderId,
                'quantity' => $this->dividerForm['quantity'],
                'ply' => $this->dividerForm['ply'],
                'combination_1' => $this->dividerForm['combination_1'],
                'combination_2' => $this->dividerForm['combination_2'],
                'combination_3' => $this->dividerForm['combination_3'],
                'combination_4' => $this->dividerForm['combination_4'],
                'combination_5' => $this->dividerForm['combination_5'],
                'combination_6' => $this->dividerForm['combination_6'],
                'combination_7' => $this->dividerForm['combination_7'],
                'unit' => $this->dividerForm['unit'],
                'fsc_claim' => $this->dividerForm['fsc_claim'],
                'supplier_price' => $this->dividerForm['supplier_price'],
            ];

            $this->jobOrder->dividers()->create($dividerData);
            
            // Reload the job order to get updated data
            $this->loadJobOrder();
            
            $this->resetDividerForm();
            $this->activeTab = 'dividers';
            
            session()->flash('success', 'Divider added successfully!');
            
            // Close modal after adding
            $this->closeBoxDividerModal();
            
        } catch (\Exception $e) {
            \Log::error('Error adding divider: ' . $e->getMessage());
            session()->flash('error', 'Error adding divider: ' . $e->getMessage());
        }
    }

    public function removeBox($index)
    {
        try {
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

    public function removeDivider($index)
    {
        try {
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
            $existingPO = \App\Models\PurchaseOrder::where('job_order_id', $this->jobOrderId)->first();
            if ($existingPO) {
                session()->flash('error', 'Purchase order already exists for this job order.');
                return redirect()->route('purchase-order-management', ['purchase_order' => $existingPO->id]);
            }

            // Create purchase order
            $purchaseOrder = \App\Models\PurchaseOrder::create([
                'po_number' => \App\Models\PurchaseOrder::generatePONumber(),
                'date' => now()->format('Y-m-d'),
                'supplier_id' => $this->jobOrder->supplier_id,
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
            
            // Reload the job order to get updated data
            $this->loadJobOrder();
            
            session()->flash('success', 'Job order has been confirmed successfully!');
            
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

    public function render()
    {
        return view('livewire.job-order-detail', [
            'suppliers' => Supplier::all(),
            'customers' => Customer::all(),
        ]);
    }
}
