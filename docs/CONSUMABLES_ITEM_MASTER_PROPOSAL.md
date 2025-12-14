# Consumables Item Master Table Proposal

## Overview

Create an **Item Master table specifically for Consumables** only. Raw materials and finished goods are job-order specific (boxes, dividers with varying dimensions) and don't need a master table.

---

## Why Only Consumables?

### **Consumables (Fixed Items)**
- ✅ **Fixed items**: Glue, ink, paper, tape, labels, etc.
- ✅ **Standardized**: Same items used across all job orders
- ✅ **Reusable**: Can be used for multiple productions
- ✅ **Need master data**: Item code, name, UOM, default cost, etc.

### **Raw Materials (Job-Order Specific)**
- ❌ **Dynamic**: Boxes and dividers with specific dimensions per job
- ❌ **Job-specific**: Each job order has unique box/divider specifications
- ❌ **No standardization**: Dimensions vary by customer requirements
- ❌ **Don't need master**: Generated from job order specifications

### **Finished Goods (Job-Order Specific)**
- ❌ **Dynamic**: Finished products match job order specifications
- ❌ **Job-specific**: Each job produces unique finished goods
- ❌ **No standardization**: Products vary by customer requirements
- ❌ **Don't need master**: Generated from production orders

---

## Database Schema

### **Table: `consumable_items`**

```sql
CREATE TABLE consumable_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_code VARCHAR(100) UNIQUE NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    
    -- Classification
    material_type ENUM('consumable') NOT NULL DEFAULT 'consumable',
    
    -- Defaults
    default_uom VARCHAR(20) NOT NULL DEFAULT 'KG',
    default_unit_cost DECIMAL(15, 4) DEFAULT 0,
    default_warehouse VARCHAR(100) DEFAULT 'MAIN',
    
    -- Inventory Management
    min_stock_level DECIMAL(10, 2) DEFAULT 0,
    max_stock_level DECIMAL(10, 2) NULL,
    reorder_point DECIMAL(10, 2) DEFAULT 0,
    
    -- Supplier Info (Optional)
    preferred_supplier_id BIGINT UNSIGNED NULL,
    
    -- Status
    is_active BOOLEAN DEFAULT TRUE,
    
    -- Metadata
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    -- Indexes
    INDEX idx_item_code (item_code),
    INDEX idx_is_active (is_active),
    INDEX idx_preferred_supplier (preferred_supplier_id),
    
    -- Foreign Keys
    FOREIGN KEY (preferred_supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
);
```

---

## Key Fields Explanation

### **1. Item Identification**
- `item_code`: Unique identifier (e.g., "GLUE-001", "INK-BLACK")
- `item_name`: Human-readable name (e.g., "White Glue", "Black Ink")
- `description`: Detailed description

### **2. Classification**
- `material_type`: Always 'consumable' (fixed for this table)

### **3. Defaults**
- `default_uom`: Default unit of measure (KG, LTR, ROLL, etc.)
- `default_unit_cost`: Default cost per unit
- `default_warehouse`: Default warehouse location

### **4. Inventory Management**
- `min_stock_level`: Minimum stock threshold
- `max_stock_level`: Maximum stock capacity
- `reorder_point`: Reorder when stock reaches this level

### **5. Supplier**
- `preferred_supplier_id`: Default supplier for this consumable

### **6. Status**
- `is_active`: Enable/disable items

---

## Benefits

### **1. Standardization**
✅ All consumables defined in one place  
✅ Consistent item codes  
✅ No typos or variations  

### **2. User Experience**
✅ Dropdown/autocomplete in GRN forms  
✅ Auto-fill default UOM and cost  
✅ Search and filter capabilities  

### **3. Data Integrity**
✅ Validation: Can't create GRN with non-existent consumable  
✅ Foreign key constraints  
✅ Referential integrity  

### **4. Reporting**
✅ Consumables inventory reports  
✅ Consumables consumption analysis  
✅ Cost tracking per consumable  

---

## Implementation Plan

### **Phase 1: Database & Model**

1. **Create Migration**
   ```bash
   php artisan make:migration create_consumable_items_table
   ```

2. **Create Model**
   ```bash
   php artisan make:model ConsumableItem
   ```

3. **Create Relationships**
   - ConsumableItem → Supplier (belongsTo)
   - ConsumableItem → Inventory (hasMany via item_code)
   - ConsumableItem → InventoryTransactions (hasMany via item_code)

### **Phase 2: Consumable Item Master CRUD**

1. **Create Livewire Component**
   ```bash
   php artisan make:livewire ConsumableItemsCrud
   ```

2. **Features**:
   - List all consumable items
   - Create/Edit/Delete consumable items
   - Filter by active/inactive
   - Search functionality
   - Bulk import (optional)

### **Phase 3: Update GRN Forms**

1. **Detect Consumable GRN Items**
   - When creating GRN item, check if it's a consumable
   - If consumable: Show dropdown from consumable_items table
   - If raw material: Keep existing manual entry (job-order specific)

2. **Auto-fill Consumable Details**
   - Auto-fill UOM from consumable_item
   - Auto-fill default cost (can override)
   - Auto-fill warehouse

### **Phase 4: Update Services**

1. **GRNProcessingService**
   - Check if item is consumable
   - If consumable: Validate against consumable_items table
   - Use consumable defaults if not specified
   - If raw material: Use existing logic (job-order specific)

2. **Consumable Detection**
   - First check consumable_items table
   - If found: It's a consumable
   - If not found: Treat as raw material (job-order specific)

---

## Example Usage

### **GRN Item Creation Flow**

```php
// Step 1: User selects item type
if ($isConsumable) {
    // Show consumable dropdown
    $consumableItems = ConsumableItem::where('is_active', true)->get();
    // User selects: "GLUE-001 - White Glue"
    
    // Step 2: Auto-fill details
    $consumable = ConsumableItem::where('item_code', 'GLUE-001')->first();
    $grnItem = [
        'item_code' => $consumable->item_code,
        'material_code' => $consumable->item_code,
        'uom' => $consumable->default_uom, // Auto-filled: "KG"
        'unit_cost' => $consumable->default_unit_cost, // Auto-filled: 15.00
        'warehouse' => $consumable->default_warehouse, // Auto-filled: "MAIN"
        'material_type' => 'consumable',
    ];
} else {
    // Raw material (job-order specific)
    // Keep existing manual entry
    $grnItem = [
        'item_code' => 'BOX-123-3PLY', // Job-order specific
        'material_code' => 'BOX-123-3PLY',
        'uom' => 'PCS',
        'unit_cost' => 10.00, // Manual entry
        'material_type' => 'raw_material',
    ];
}
```

---

## Updated GRN Form

```blade
<!-- Item Type Selection -->
<div>
    <label>Item Type</label>
    <select wire:model="item_type" wire:change="onItemTypeChange">
        <option value="">Select Type</option>
        <option value="consumable">Consumable</option>
        <option value="raw_material">Raw Material (Job Order Specific)</option>
    </select>
</div>

@if($item_type === 'consumable')
    <!-- Consumable: Dropdown from Item Master -->
    <div>
        <label>Consumable Item</label>
        <select wire:model="consumable_item_id" 
                wire:change="loadConsumableDetails"
                class="w-full">
            <option value="">Select Consumable</option>
            @foreach($consumableItems as $item)
                <option value="{{ $item->id }}">
                    {{ $item->item_code }} - {{ $item->item_name }}
                </option>
            @endforeach
        </select>
        
        <!-- Auto-filled fields (editable) -->
        <input type="text" wire:model="uom" 
               value="{{ $selectedConsumable->default_uom ?? '' }}"
               placeholder="UOM">
        <input type="number" wire:model="unit_cost" 
               value="{{ $selectedConsumable->default_unit_cost ?? 0 }}"
               placeholder="Unit Cost">
    </div>
@elseif($item_type === 'raw_material')
    <!-- Raw Material: Manual Entry (Job Order Specific) -->
    <div>
        <label>Material Code</label>
        <input type="text" wire:model="material_code" 
               placeholder="Enter material code (e.g., BOX-123-3PLY)">
        <input type="text" wire:model="uom" placeholder="UOM">
        <input type="number" wire:model="unit_cost" placeholder="Unit Cost">
    </div>
@endif
```

---

## Sample Data

```php
// Consumables - Fixed Items
ConsumableItem::create([
    'item_code' => 'GLUE-001',
    'item_name' => 'White Glue',
    'description' => 'Industrial white adhesive glue',
    'material_type' => 'consumable',
    'default_uom' => 'KG',
    'default_unit_cost' => 15.00,
    'default_warehouse' => 'MAIN',
    'min_stock_level' => 10,
    'reorder_point' => 20,
    'is_active' => true,
]);

ConsumableItem::create([
    'item_code' => 'INK-BLACK',
    'item_name' => 'Black Printing Ink',
    'description' => 'High-quality black ink for printing',
    'material_type' => 'consumable',
    'default_uom' => 'LTR',
    'default_unit_cost' => 25.00,
    'default_warehouse' => 'MAIN',
    'min_stock_level' => 5,
    'reorder_point' => 10,
    'is_active' => true,
]);

ConsumableItem::create([
    'item_code' => 'PAPER-WRAP',
    'item_name' => 'Wrapping Paper',
    'description' => 'Standard wrapping paper for packaging',
    'material_type' => 'consumable',
    'default_uom' => 'ROLL',
    'default_unit_cost' => 8.00,
    'default_warehouse' => 'MAIN',
    'min_stock_level' => 20,
    'reorder_point' => 50,
    'is_active' => true,
]);
```

---

## Updated GRNProcessingService Logic

```php
private function determineMaterialType(GRNItem $grnItem): string
{
    // First check if it's in consumable_items table
    $consumableItem = ConsumableItem::where('item_code', $grnItem->material_code)
        ->where('is_active', true)
        ->first();
    
    if ($consumableItem) {
        return 'consumable';
    }
    
    // If not found, it's a raw material (job-order specific)
    return 'raw_material';
}

private function getConsumableDefaults(string $itemCode): array
{
    $consumable = ConsumableItem::where('item_code', $itemCode)
        ->where('is_active', true)
        ->first();
    
    if ($consumable) {
        return [
            'uom' => $consumable->default_uom,
            'unit_cost' => $consumable->default_unit_cost,
            'warehouse' => $consumable->default_warehouse,
        ];
    }
    
    return [];
}
```

---

## Migration Strategy

### **Extract Existing Consumables from Inventory**

```php
// Migration script to populate consumable_items from existing inventory
$existingConsumables = DB::table('inventory')
    ->where('category', 'RAW')
    ->where('material_type', 'consumable')
    ->select('item_code', 'uom')
    ->distinct()
    ->get();

foreach ($existingConsumables as $existing) {
    ConsumableItem::firstOrCreate(
        ['item_code' => $existing->item_code],
        [
            'item_name' => $existing->item_code, // Default name
            'material_type' => 'consumable',
            'default_uom' => $existing->uom,
            'is_active' => true,
        ]
    );
}
```

---

## Benefits of Consumables-Only Approach

✅ **Simpler**: Only manage fixed consumable items  
✅ **Focused**: Clear separation between fixed items and dynamic items  
✅ **Flexible**: Raw materials remain job-order specific  
✅ **Scalable**: Easy to add new consumables  
✅ **User-Friendly**: Dropdown for consumables, manual entry for raw materials  

---

## Summary

- **Consumables**: Fixed items → Need Item Master table
- **Raw Materials**: Job-order specific → No Item Master needed
- **Finished Goods**: Job-order specific → No Item Master needed

This approach provides the benefits of item master for consumables while keeping raw materials and finished goods flexible for job-order variations.

---

## Next Steps

1. Create `consumable_items` table migration
2. Create `ConsumableItem` model
3. Create Consumable Items CRUD interface
4. Update GRN forms to detect consumables and show dropdown
5. Update GRNProcessingService to use consumable defaults
6. Migrate existing consumables from inventory

