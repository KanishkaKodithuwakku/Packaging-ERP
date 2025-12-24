<?php

namespace App\Livewire\GRN;

use App\Models\SupplierOrder;
use App\Models\GRN;
use App\Services\OrderService;
use Livewire\Component;
use Livewire\WithPagination;

class GRNsCrud extends Component
{
    use WithPagination;

    protected $layout = 'components.layouts.app';

    public $showModal = false;
    public $editing = false;
    public $grnId;

    // Delete confirmation modal
    public $showDeleteConfirmModal = false;
    public $grnToDelete = null;

    // Filter modal and filters
    public $showFilterModal = false;
    public $filterSupplier = '';
    public $filterReceivingProgress = 'partial_and_not_received'; // Default: show partial and not_received
    public $filterDateFrom = '';
    public $filterDateTo = '';
    public $search = '';

    // Form fields
    public $supplier_id;
    public $po_reference;
    public $grn_no;
    public $lot_code;
    public $received_date;

    protected $rules = [
        'supplier_id' => 'required|exists:suppliers,id',
        'po_reference' => 'nullable|string|max:255',
        'grn_no' => 'required|string|max:255|unique:grns,grn_no',
        'lot_code' => 'nullable|string|max:255',
        'received_date' => 'required|date',
    ];

    public function getRules()
    {
        $rules = [
            'supplier_id' => 'required|exists:suppliers,id',
            'po_reference' => 'nullable|string|max:255',
            'grn_no' => 'required|string|max:255|unique:grns,grn_no',
            'lot_code' => 'nullable|string|max:255',
            'received_date' => 'required|date',
        ];

        // If editing, update unique rule
        if ($this->editing) {
            $rules['grn_no'] = 'required|string|max:255|unique:grns,grn_no,' . $this->grnId;
        }

        return $rules;
    }

    protected $messages = [
        'supplier_id.required' => 'Please select a supplier.',
        'supplier_id.exists' => 'Selected supplier does not exist.',
        'grn_no.required' => 'GRN number is required.',
        'grn_no.unique' => 'This GRN number already exists.',
        'received_date.required' => 'Received date is required.',
    ];

    public function mount()
    {
        $this->resetForm();

        // Check if we're creating from a supplier order
        if (request()->has('create_from')) {
            $this->createFromSupplierOrder(request()->get('create_from'));
        }
    }

    public function resetForm()
    {
        $this->supplier_id = '';
        $this->po_reference = '';
        $this->grn_no = '';
        $this->lot_code = '';
        $this->received_date = now()->format('Y-m-d');
        $this->editing = false;
        $this->grnId = null;
    }


    public function create()
    {
        $this->resetForm();
        $this->grn_no = app(OrderService::class)->generateGRNNumber();
        $this->showModal = true;
    }

    public function createFromSupplierOrder($supplierOrderId)
    {
        $supplierOrder = SupplierOrder::findOrFail($supplierOrderId);

        $this->supplier_id = $supplierOrder->supplier_id;
        $this->po_reference = $supplierOrder->po_no;
        $this->grn_no = app(OrderService::class)->generateGRNNumber();
        $this->received_date = now()->format('Y-m-d');
        $this->editing = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $grn = GRN::findOrFail($id);

        $this->grnId = $id;
        // Get supplier from various sources
        if ($grn->supplier_id) {
            $this->supplier_id = $grn->supplier_id;
        } elseif ($grn->supplierOrder) {
            $this->supplier_id = $grn->supplierOrder->supplier_id;
        } elseif ($grn->purchaseOrder) {
            $this->supplier_id = $grn->purchaseOrder->supplier_id;
        } elseif ($grn->productionOrder) {
            $this->supplier_id = $grn->productionOrder->supplier_id;
        }
        $this->po_reference = $grn->po_reference;
        $this->grn_no = $grn->grn_no;
        $this->lot_code = $grn->lot_code;
        $this->received_date = $grn->received_date->format('Y-m-d');
        $this->editing = true;
        $this->showModal = true;
    }

    public function save()
    {
        // Use dynamic rules
        $this->rules = $this->getRules();
        $this->validate();

        // Auto-generate lot code if empty
        if (empty($this->lot_code)) {
            $this->lot_code = 'GRN-' . now()->format('Ymd') . '-' . str_pad(GRN::count() + 1, 4, '0', STR_PAD_LEFT);
        }

        $data = [
            'po_reference' => $this->po_reference,
            'grn_no' => $this->grn_no,
            'lot_code' => $this->lot_code,
            'received_date' => $this->received_date,
        ];

        if ($this->editing) {
            $data['supplier_id'] = $this->supplier_id;
            GRN::findOrFail($this->grnId)->update($data);
            session()->flash('message', 'GRN updated successfully!');
            $this->showModal = false;
            $this->resetForm();
        } else {
            // Create GRN with basic info only
            $grn = GRN::create([
                'supplier_po_id' => null,
                'production_order_id' => null,
                'purchase_order_id' => null,
                'supplier_id' => $this->supplier_id,
                'po_reference' => $this->po_reference,
                'grn_no' => $this->grn_no,
                'lot_code' => $this->lot_code,
                'received_date' => $this->received_date,
                'status' => 'pending',
                'notes' => $this->po_reference ? "PO Reference: {$this->po_reference}" : null,
            ]);

            session()->flash('message', 'GRN created successfully! You can now add consumable items.');
            $this->showModal = false;
            $this->resetForm();

            // Redirect to GRN detail page to add items
            return $this->redirect(route('grn-detail', $grn->id), navigate: true);
        }
    }

    public function openDeleteConfirmModal($id)
    {
        $this->grnToDelete = $id;
        $this->showDeleteConfirmModal = true;
    }

    public function closeDeleteConfirmModal()
    {
        $this->showDeleteConfirmModal = false;
        $this->grnToDelete = null;
    }

    public function delete($id)
    {
        GRN::findOrFail($id)->delete();
        session()->flash('message', 'GRN deleted successfully!');

        // Close modal
        $this->closeDeleteConfirmModal();

        // Redirect to GRN list table after deletion
        return $this->redirect(route('grns'), navigate: true);
    }


    public function closeModal()
    {
        $this->showModal = false;
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
        $this->filterReceivingProgress = 'partial_and_not_received';
        $this->filterDateFrom = '';
        $this->filterDateTo = '';
        $this->search = '';
    }

    public function render()
    {
        $query = GRN::with(['supplierOrder.supplier', 'productionOrder.supplier', 'items', 'purchaseOrder.supplier']);

        // Apply search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->where('grn_no', 'like', '%' . $this->search . '%')
                  ->orWhere('lot_code', 'like', '%' . $this->search . '%')
                  ->orWhere('material_code', 'like', '%' . $this->search . '%');
            });
        }

        // Apply supplier filter
        if ($this->filterSupplier) {
            $query->where(function($q) {
                $q->whereHas('supplierOrder', function($sq) {
                    $sq->where('supplier_id', $this->filterSupplier);
                })
                ->orWhereHas('productionOrder', function($pq) {
                    $pq->whereHas('supplier', function($s) {
                        $s->where('id', $this->filterSupplier);
                    });
                })
                ->orWhereHas('purchaseOrder', function($poq) {
                    $poq->where('supplier_id', $this->filterSupplier);
                });
            });
        }

        // Apply receiving progress filter
        if ($this->filterReceivingProgress === 'all' || empty($this->filterReceivingProgress)) {
            // Show all GRNs (no filter applied)
            // No additional filtering needed
        } elseif ($this->filterReceivingProgress === 'fully_received') {
            // Filter for fully received GRNs (all items have is_fully_received = true)
            $query->whereHas('items')->whereDoesntHave('items', function($q) {
                $q->where('is_fully_received', false);
            });
        } elseif ($this->filterReceivingProgress === 'partial') {
            // Filter for partial receiving (has items with qty_received_partial > 0 but not all fully received)
            $query->whereHas('items', function($q) {
                $q->where('qty_received_partial', '>', 0);
            })->whereHas('items', function($q) {
                $q->where(function($subQ) {
                    $subQ->where('is_fully_received', false)
                         ->orWhereNull('is_fully_received');
                });
            });
        } elseif ($this->filterReceivingProgress === 'not_received') {
            // Filter for not received: has items, not fully received, and no partial receiving
            // This matches the view logic: !isFullyReceived() && !hasPartialReceiving()
            // Use whereRaw to check both conditions in one query
            $query->whereHas('items') // Must have items
                ->whereRaw('EXISTS (SELECT 1 FROM grn_items WHERE grn_items.grn_id = grns.id AND (is_fully_received = 0 OR is_fully_received IS NULL))')
                ->whereRaw('NOT EXISTS (SELECT 1 FROM grn_items WHERE grn_items.grn_id = grns.id AND qty_received_partial > 0)');
        } elseif ($this->filterReceivingProgress === 'partial_and_not_received') {
            // Show both partial and not_received (default)
            // Partial: hasPartialReceiving() && !isFullyReceived()
            // Not Received: !hasPartialReceiving() && !isFullyReceived()
            $query->where(function($q) {
                // Partial receiving: has items with qty_received_partial > 0 but not all fully received
                $q->whereHas('items', function($subQ) {
                    $subQ->where('qty_received_partial', '>', 0);
                })->whereHas('items', function($subQ) {
                    // At least one item is not fully received
                    $subQ->where(function($subSubQ) {
                        $subSubQ->where('is_fully_received', false)
                                 ->orWhereNull('is_fully_received');
                    });
                })
                // OR not received: has items, not fully received, no partial receiving
                ->orWhere(function($subQ) {
                    $subQ->whereHas('items')
                         ->whereRaw('EXISTS (SELECT 1 FROM grn_items WHERE grn_items.grn_id = grns.id AND (is_fully_received = 0 OR is_fully_received IS NULL))')
                         ->whereRaw('NOT EXISTS (SELECT 1 FROM grn_items WHERE grn_items.grn_id = grns.id AND qty_received_partial > 0)');
                });
            });
        }

        // Apply date filters
        if ($this->filterDateFrom) {
            $query->where('received_date', '>=', $this->filterDateFrom);
        }

        if ($this->filterDateTo) {
            $query->where('received_date', '<=', $this->filterDateTo);
        }

        $grns = $query->orderBy('created_at', 'desc')
            ->paginate(10);

        $supplierOrders = SupplierOrder::where('status', 'ordered')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get suppliers for filter dropdown
        $suppliers = \App\Models\Supplier::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.grn.g-r-ns-crud', [
            'grns' => $grns,
            'supplierOrders' => $supplierOrders,
            'suppliers' => $suppliers,
        ]);
    }
}
