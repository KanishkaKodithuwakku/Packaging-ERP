<?php

namespace App\Livewire;

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
    public $filterReceivingProgress = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';
    public $search = '';

    // Form fields
    public $supplier_po_id;
    public $grn_no;
    public $lot_code;
    public $material_code;
    public $qty_received;
    public $uom = 'KG';
    public $received_date;

    protected $rules = [
        'supplier_po_id' => 'required|exists:supplier_orders,id',
        'grn_no' => 'required|string|max:255|unique:goods_receipts,grn_no',
        'lot_code' => 'required|string|max:255',
        'material_code' => 'required|string|max:255',
        'qty_received' => 'required|numeric|min:0.01',
        'uom' => 'required|string|max:10',
        'received_date' => 'required|date',
    ];

    protected $messages = [
        'supplier_po_id.required' => 'Please select a supplier order.',
        'supplier_po_id.exists' => 'Selected supplier order does not exist.',
        'grn_no.required' => 'GRN number is required.',
        'grn_no.unique' => 'This GRN number already exists.',
        'lot_code.required' => 'Lot code is required.',
        'material_code.required' => 'Material code is required.',
        'qty_received.required' => 'Quantity received is required.',
        'qty_received.numeric' => 'Quantity must be a number.',
        'qty_received.min' => 'Quantity must be greater than 0.',
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
        $this->supplier_po_id = '';
        $this->grn_no = '';
        $this->lot_code = '';
        $this->material_code = '';
        $this->qty_received = '';
        $this->uom = 'KG';
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

        $this->supplier_po_id = $supplierOrder->id;
        $this->material_code = $supplierOrder->material_code;
        $this->qty_received = $supplierOrder->qty_kg;
        $this->grn_no = app(OrderService::class)->generateGRNNumber();
        $this->received_date = now()->format('Y-m-d');
        $this->editing = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $grn = GRN::findOrFail($id);

        $this->grnId = $id;
        $this->supplier_po_id = $grn->supplier_po_id;
        $this->grn_no = $grn->grn_no;
        $this->lot_code = $grn->lot_code;
        $this->material_code = $grn->material_code;
        $this->qty_received = $grn->qty_received;
        $this->uom = $grn->uom;
        $this->received_date = $grn->received_date->format('Y-m-d');
        $this->editing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editing) {
            $this->rules['grn_no'] = 'required|string|max:255|unique:goods_receipts,grn_no,' . $this->grnId;
        }

        $data = [
            'supplier_po_id' => $this->supplier_po_id,
            'grn_no' => $this->grn_no,
            'lot_code' => $this->lot_code,
            'material_code' => $this->material_code,
            'qty_received' => $this->qty_received,
            'uom' => $this->uom,
            'received_date' => $this->received_date,
        ];

        if ($this->editing) {
            GRN::findOrFail($this->grnId)->update($data);
            session()->flash('message', 'GRN updated successfully!');
            $this->showModal = false;
            $this->resetForm();
        } else {
            app(OrderService::class)->processGoodsReceipt($data);
            session()->flash('message', 'GRN created and inventory updated successfully!');
            $this->showModal = false;
            $this->resetForm();

            // Redirect to GRNs page after creation
            return $this->redirect(route('grns'), navigate: true);
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
        $this->filterReceivingProgress = '';
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
        if ($this->filterReceivingProgress) {
            if ($this->filterReceivingProgress === 'fully_received') {
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
                // Filter for not received (no items have qty_received_partial > 0)
                $query->whereDoesntHave('items', function($q) {
                    $q->where('qty_received_partial', '>', 0);
                })->whereHas('items'); // Ensure GRN has items
            }
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

        return view('livewire.g-r-ns-crud', [
            'grns' => $grns,
            'supplierOrders' => $supplierOrders,
            'suppliers' => $suppliers,
        ]);
    }
}
