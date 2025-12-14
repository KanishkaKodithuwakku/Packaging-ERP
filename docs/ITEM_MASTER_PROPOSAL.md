# Item Master Table Proposal

> **Note**: This proposal has been updated. See `CONSUMABLES_ITEM_MASTER_PROPOSAL.md` for the final implementation approach focusing only on consumables.

## Problem Statement

Currently, the system requires manual entry of `material_code` when creating GRNs, which leads to:
- ❌ **Inconsistent naming**: Users may type "GLUE-001", "Glue-001", "GLUE001" differently
- ❌ **No validation**: Can't verify if an item exists
- ❌ **No standardization**: No centralized place to define items
- ❌ **No item details**: Can't store descriptions, default UOMs, costs, etc.
- ❌ **Difficult GRN creation**: Users must remember exact material codes
- ❌ **No autocomplete/dropdown**: Can't provide user-friendly selection

## Solution: Item Master Table

Create a centralized `items` (or `item_master`) table that stores all inventory items including:
- Raw Materials
- Consumables
- Components
- Finished Goods (optional)

---

## Database Schema

### **Table: `items`**

```sql
CREATE TABLE items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_code VARCHAR(100) UNIQUE NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    
    -- Classification
    category ENUM('RAW', 'WIP', 'FG') NOT NULL DEFAULT 'RAW',
    material_type ENUM('raw_material', 'consumable', 'component') NOT NULL DEFAULT 'raw_material',
    
    -- Defaults
    default_uom VARCHAR(20) NOT NULL DEFAULT 'PCS',
    default_unit_cost DECIMAL(15, 4) DEFAULT 0,
    default_warehouse VARCHAR(100) DEFAULT 'MAIN',
    
    -- Additional Info
    supplier_id BIGINT UNSIGNED NULL,
    min_stock_level DECIMAL(10, 2) DEFAULT 0,
    max_stock_level DECIMAL(10, 2) NULL,
    reorder_point DECIMAL(10, 2) DEFAULT 0,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Metadata
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    -- Indexes
    INDEX idx_item_code (item_code),
    INDEX idx_category_material_type (category, material_type),
    INDEX idx_is_active (is_active),
    INDEX idx_supplier (supplier_id),
    
    -- Foreign Keys
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
);
```

---

## Key Fields Explanation

### **1. Item Identification**
- `item_code`: Unique identifier (e.g., "GLUE-001", "INK-BLACK")
- `item_name`: Human-readable name (e.g., "White Glue", "Black Ink")
- `description`: Detailed description

### **2. Classification**
- `category`: RAW, WIP, or FG
- `material_type`: raw_material, consumable, or component
- Allows filtering and grouping

### **3. Defaults**
- `default_uom`: Default unit of measure (KG, PCS, MTR, etc.)
- `default_unit_cost`: Default cost per unit
- `default_warehouse`: Default warehouse location

### **4. Inventory Management**
- `min_stock_level`: Minimum stock threshold
- `max_stock_level`: Maximum stock capacity
- `reorder_point`: Reorder when stock reaches this level

### **5. Status**
- `is_active`: Enable/disable items (soft delete alternative)

---

## Benefits

### **1. Standardization**
✅ All items defined in one place  
✅ Consistent item codes across the system  
✅ No typos or variations  

### **2. User Experience**
✅ Dropdown/autocomplete in GRN forms  
✅ Auto-fill default UOM and cost  
✅ Search and filter capabilities  

### **3. Data Integrity**
✅ Validation: Can't create GRN with non-existent item  
✅ Foreign key constraints  
✅ Referential integrity  

### **4. Reporting & Analytics**
✅ Item-wise reports  
✅ Category-wise analysis  
✅ Consumables vs Raw Materials reports  

### **5. Maintenance**
✅ Easy to add new items  
✅ Update item details in one place  
✅ Deactivate obsolete items  

---

## Implementation Plan

### **Phase 1: Database & Model**

1. **Create Migration**
   ```bash
   php artisan make:migration create_items_table
   ```

2. **Create Model**
   ```bash
   php artisan make:model Item
   ```

3. **Create Relationships**
   - Item → Supplier (belongsTo)
   - Item → Inventory (hasMany via item_code)
   - Item → InventoryTransactions (hasMany via item_code)

### **Phase 2: Item Master CRUD**

1. **Create Livewire Component**
   ```bash
   php artisan make:livewire ItemsCrud
   ```

2. **Features**:
   - List all items with filters (category, material_type, active)
   - Create/Edit/Delete items
   - Bulk import (optional)
   - Search functionality

### **Phase 3: Update GRN Forms**

1. **Update GRN Item Creation**
   - Replace text input with dropdown/autocomplete
   - Auto-fill UOM, cost, warehouse from item master
   - Validate item exists

2. **Update Purchase Order Forms**
   - Use item master for material selection
   - Auto-populate item details

### **Phase 4: Update Services**

1. **GRNProcessingService**
   - Lookup item from item master
   - Use item defaults if not specified
   - Validate item exists before processing

2. **InventoryService**
   - Reference item master for item details
   - Use default UOM if not provided

### **Phase 5: Dashboard Updates**

1. **Item Master Dashboard**
   - Show item count by category/material_type
   - Low stock alerts based on reorder_point
   - Recently added items

---

## Example Usage

### **Before (Current)**
```php
// User manually types material code
$grnItem = [
    'material_code' => 'GLUE-001',  // Manual entry, prone to errors
    'uom' => 'KG',                  // Manual entry
    'unit_cost' => 10.00,           // Manual entry
];
```

### **After (With Item Master)**
```php
// User selects from dropdown
$item = Item::where('item_code', 'GLUE-001')->first();

$grnItem = [
    'item_id' => $item->id,              // Selected from dropdown
    'material_code' => $item->item_code, // Auto-filled
    'uom' => $item->default_uom,          // Auto-filled
    'unit_cost' => $item->default_unit_cost, // Auto-filled (can override)
];
```

---

## Migration Strategy

### **Option 1: Create New Items from Existing Inventory**

Extract unique item codes from existing inventory and create item master records:

```php
// Migration script
$existingItems = DB::table('inventory')
    ->select('item_code', 'category', 'material_type', 'uom')
    ->distinct()
    ->get();

foreach ($existingItems as $existing) {
    Item::firstOrCreate(
        ['item_code' => $existing->item_code],
        [
            'item_name' => $existing->item_code, // Default name
            'category' => $existing->category,
            'material_type' => $existing->material_type ?? 'raw_material',
            'default_uom' => $existing->uom,
            'is_active' => true,
        ]
    );
}
```

### **Option 2: Start Fresh**

Create item master first, then require items to exist before creating GRNs.

---

## Sample Data

```php
// Consumables
Item::create([
    'item_code' => 'GLUE-001',
    'item_name' => 'White Glue',
    'description' => 'Industrial white adhesive glue',
    'category' => 'RAW',
    'material_type' => 'consumable',
    'default_uom' => 'KG',
    'default_unit_cost' => 15.00,
    'default_warehouse' => 'MAIN',
    'min_stock_level' => 10,
    'reorder_point' => 20,
]);

Item::create([
    'item_code' => 'INK-BLACK',
    'item_name' => 'Black Printing Ink',
    'description' => 'High-quality black ink for printing',
    'category' => 'RAW',
    'material_type' => 'consumable',
    'default_uom' => 'LTR',
    'default_unit_cost' => 25.00,
    'default_warehouse' => 'MAIN',
    'min_stock_level' => 5,
    'reorder_point' => 10,
]);

// Raw Materials
Item::create([
    'item_code' => 'CARDBOARD-001',
    'item_name' => 'Cardboard Sheet',
    'description' => 'Standard cardboard for boxes',
    'category' => 'RAW',
    'material_type' => 'raw_material',
    'default_uom' => 'PCS',
    'default_unit_cost' => 5.00,
    'default_warehouse' => 'MAIN',
    'min_stock_level' => 100,
    'reorder_point' => 200,
]);
```

---

## Updated GRN Form Example

```blade
<!-- Before: Text Input -->
<input type="text" wire:model="material_code" 
       placeholder="Enter material code">

<!-- After: Dropdown with Search -->
<div>
    <label>Item</label>
    <select wire:model="item_id" 
            wire:change="loadItemDetails"
            class="w-full">
        <option value="">Select Item</option>
        @foreach($items as $item)
            <option value="{{ $item->id }}">
                {{ $item->item_code }} - {{ $item->item_name }}
            </option>
        @endforeach
    </select>
    
    <!-- Or use autocomplete -->
    <input type="text" 
           wire:model="item_search"
           wire:keyup="searchItems"
           placeholder="Search items..."
           class="w-full">
    <ul>
        @foreach($searchResults as $item)
            <li wire:click="selectItem({{ $item->id }})">
                {{ $item->item_code }} - {{ $item->item_name }}
            </li>
        @endforeach
    </ul>
</div>

<!-- Auto-filled fields (editable) -->
<input type="text" wire:model="uom" 
       value="{{ $selectedItem->default_uom ?? '' }}">
<input type="number" wire:model="unit_cost" 
       value="{{ $selectedItem->default_unit_cost ?? 0 }}">
```

---

## Integration Points

### **1. GRN Processing**
- Validate item exists in item master
- Use item defaults
- Link GRN item to item master

### **2. Inventory Dashboard**
- Show item names instead of just codes
- Filter by item master categories
- Low stock alerts based on reorder_point

### **3. Purchase Orders**
- Select items from item master
- Auto-fill item details
- Validate items exist

### **4. Reports**
- Item-wise inventory reports
- Item consumption reports
- Item cost analysis

---

## Additional Features (Future)

1. **Item Variants**: Different sizes/colors of same item
2. **Item Images**: Upload item photos
3. **Item Specifications**: Technical specifications
4. **Item Categories**: Hierarchical categories
5. **Item Tags**: Tagging system for better search
6. **Barcode Support**: Barcode scanning
7. **Item History**: Track item changes over time

---

## Conclusion

An Item Master table is **essential** for:
- ✅ Proper inventory management
- ✅ Data consistency
- ✅ User-friendly GRN creation
- ✅ Better reporting
- ✅ Scalability

**Recommendation**: Implement Item Master table as the next priority after consumables implementation.

---

## Next Steps

1. Review and approve this proposal
2. Create database migration
3. Create Item model and relationships
4. Create Item Master CRUD interface
5. Update GRN forms to use item master
6. Migrate existing data
7. Test and deploy

