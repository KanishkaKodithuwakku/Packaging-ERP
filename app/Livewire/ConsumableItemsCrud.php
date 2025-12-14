<?php

namespace App\Livewire;

use App\Models\ConsumableItem;
use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;

class ConsumableItemsCrud extends Component
{
    use WithPagination;

    protected $layout = 'components.layouts.app';

    public $showModal = false;
    public $editing = false;
    public $itemId;

    // Delete confirmation modal
    public $showDeleteConfirmModal = false;
    public $itemToDelete = null;

    // Search and filters
    public $search = '';
    public $filterActive = 'all'; // all, active, inactive

    // Form fields
    public $item_code;
    public $item_name;
    public $description;
    public $default_uom = 'KG';
    public $default_unit_cost = 0;
    public $default_warehouse = 'MAIN';
    public $min_stock_level = 0;
    public $max_stock_level;
    public $reorder_point = 0;
    public $preferred_supplier_id;
    public $is_active = true;

    protected $rules = [
        'item_code' => 'required|string|max:100|unique:consumable_items,item_code',
        'item_name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'default_uom' => 'required|string|max:20',
        'default_unit_cost' => 'required|numeric|min:0',
        'default_warehouse' => 'required|string|max:100',
        'min_stock_level' => 'required|numeric|min:0',
        'max_stock_level' => 'nullable|numeric|min:0',
        'reorder_point' => 'required|numeric|min:0',
        'preferred_supplier_id' => 'nullable|exists:suppliers,id',
        'is_active' => 'boolean',
    ];

    protected $messages = [
        'item_code.required' => 'Item code is required.',
        'item_code.unique' => 'This item code already exists.',
        'item_name.required' => 'Item name is required.',
        'default_uom.required' => 'Default UOM is required.',
        'default_unit_cost.required' => 'Default unit cost is required.',
        'default_unit_cost.numeric' => 'Default unit cost must be a number.',
        'default_unit_cost.min' => 'Default unit cost must be greater than or equal to 0.',
    ];

    public function mount()
    {
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->item_code = '';
        $this->item_name = '';
        $this->description = '';
        $this->default_uom = 'KG';
        $this->default_unit_cost = 0;
        $this->default_warehouse = 'MAIN';
        $this->min_stock_level = 0;
        $this->max_stock_level = null;
        $this->reorder_point = 0;
        $this->preferred_supplier_id = null;
        $this->is_active = true;
        $this->editing = false;
        $this->itemId = null;
    }

    public function create()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit($id)
    {
        $item = ConsumableItem::findOrFail($id);

        $this->itemId = $id;
        $this->item_code = $item->item_code;
        $this->item_name = $item->item_name;
        $this->description = $item->description;
        $this->default_uom = $item->default_uom;
        $this->default_unit_cost = $item->default_unit_cost;
        $this->default_warehouse = $item->default_warehouse;
        $this->min_stock_level = $item->min_stock_level;
        $this->max_stock_level = $item->max_stock_level;
        $this->reorder_point = $item->reorder_point;
        $this->preferred_supplier_id = $item->preferred_supplier_id;
        $this->is_active = $item->is_active;
        $this->editing = true;
        $this->showModal = true;
    }

    public function save()
    {
        // Update unique rule for editing
        if ($this->editing) {
            $this->rules['item_code'] = 'required|string|max:100|unique:consumable_items,item_code,' . $this->itemId;
        }

        $this->validate();

        $data = [
            'item_code' => $this->item_code,
            'item_name' => $this->item_name,
            'description' => $this->description,
            'material_type' => 'consumable',
            'default_uom' => $this->default_uom,
            'default_unit_cost' => $this->default_unit_cost,
            'default_warehouse' => $this->default_warehouse,
            'min_stock_level' => $this->min_stock_level,
            'max_stock_level' => $this->max_stock_level,
            'reorder_point' => $this->reorder_point,
            'preferred_supplier_id' => $this->preferred_supplier_id,
            'is_active' => $this->is_active,
        ];

        if ($this->editing) {
            ConsumableItem::findOrFail($this->itemId)->update($data);
            session()->flash('message', 'Consumable item updated successfully!');
        } else {
            ConsumableItem::create($data);
            session()->flash('message', 'Consumable item created successfully!');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function openDeleteConfirmModal($id)
    {
        $this->itemToDelete = $id;
        $this->showDeleteConfirmModal = true;
    }

    public function closeDeleteConfirmModal()
    {
        $this->showDeleteConfirmModal = false;
        $this->itemToDelete = null;
    }

    public function delete($id)
    {
        $item = ConsumableItem::findOrFail($id);
        
        // Check if item is used in inventory
        if ($item->inventory()->count() > 0) {
            session()->flash('error', 'Cannot delete consumable item that has inventory records. Please deactivate it instead.');
            $this->closeDeleteConfirmModal();
            return;
        }

        $item->delete();
        session()->flash('message', 'Consumable item deleted successfully!');
        $this->closeDeleteConfirmModal();
    }

    public function toggleActive($id)
    {
        $item = ConsumableItem::findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);
        session()->flash('message', 'Consumable item status updated successfully!');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function render()
    {
        $query = ConsumableItem::with('preferredSupplier');

        // Search filter
        if ($this->search) {
            $query->where(function($q) {
                $q->where('item_code', 'like', '%' . $this->search . '%')
                  ->orWhere('item_name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        // Active filter
        if ($this->filterActive === 'active') {
            $query->where('is_active', true);
        } elseif ($this->filterActive === 'inactive') {
            $query->where('is_active', false);
        }

        $items = $query->orderBy('item_code')->paginate(15);

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('livewire.consumable-items-crud', [
            'items' => $items,
            'suppliers' => $suppliers,
        ]);
    }
}
