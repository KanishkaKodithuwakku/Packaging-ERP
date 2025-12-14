# Consumables Inventory Analysis

## Overview

This document analyzes whether the current inventory system can handle consumable items (glue, papers, ink) and what modifications might be needed.

---

## Current System Structure

### **Current Category Enum**
The system currently uses three categories:
- **RAW** - Raw Materials (e.g., cardboard, paperboard)
- **WIP** - Work in Progress (items being manufactured)
- **FG** - Finished Goods (completed products)

### **Current Inventory Flow**
1. **Receipt**: Items received via GRN → stored in `inventory` with category
2. **Consumption**: Items consumed for production → tracked in `inventory_transactions`
3. **Costing**: FIFO/LIFO costing via `inventory_layers`
4. **Tracking**: All movements recorded in `inventory_transactions`

---

## Consumables vs Raw Materials

### **Key Differences**

| Aspect | Raw Materials | Consumables |
|--------|--------------|-------------|
| **Purpose** | Become part of finished product | Used in production but don't become part of product |
| **Examples** | Cardboard, paperboard, dividers | Glue, ink, paper, tape, labels |
| **Consumption Pattern** | Directly linked to production quantity | May have indirect consumption (e.g., setup waste, spoilage) |
| **Cost Allocation** | Direct cost to finished goods | Indirect cost (overhead or direct if tracked) |
| **Tracking Need** | Track lot codes, FIFO costing | May need batch tracking, expiry dates |
| **Reusability** | Single use per production | May be used across multiple productions |

---

## Option 1: Use RAW Category for Consumables

### **Approach**
Treat consumables as a subset of RAW materials using the existing `RAW` category.

### **Implementation**
- No database schema changes needed
- Use `item_code` to distinguish consumables (e.g., `GLUE-001`, `INK-001`)
- Same FIFO costing applies
- Same transaction flow (receipt → consume)

### **Pros**
✅ **No Migration Required**: Works with existing structure  
✅ **Simple Implementation**: Minimal code changes  
✅ **Unified Tracking**: All materials in one category  
✅ **Same Costing Logic**: FIFO/LIFO works for consumables too  
✅ **Dashboard Ready**: Will appear in RAW materials section  

### **Cons**
❌ **No Distinction**: Can't easily separate consumables from raw materials in reports  
❌ **Mixed Reporting**: Consumables mixed with actual raw materials  
❌ **Different Consumption Logic**: May need special handling for consumption patterns  
❌ **Dashboard Confusion**: Consumables appear as "Raw Materials"  

### **Code Changes Needed**
1. **GRN Processing**: Identify consumables and set category to 'RAW'
   ```php
   // In GRNProcessingService
   private function determineItemCategory(GRNItem $grnItem): string
   {
       // Check if item is consumable
       if ($this->isConsumable($grnItem->material_code)) {
           return 'RAW'; // Treat as RAW for now
       }
       // ... existing logic
   }
   ```

2. **Dashboard Filtering**: Add filter to separate consumables
   ```php
   // In InventoryDashboard
   public function getConsumablesInventory()
   {
       return Inventory::where('category', 'RAW')
           ->whereIn('item_code', $this->getConsumableCodes())
           ->get();
   }
   ```

3. **Item Code Convention**: Establish naming convention
   - Consumables: `GLUE-*`, `INK-*`, `PAPER-*`, `TAPE-*`
   - Raw Materials: `BOX-*`, `DIVIDER-*`, `CARDBOARD-*`

---

## Option 2: Add CONSUMABLE Category

### **Approach**
Add a new `CONSUMABLE` category to the enum and treat consumables separately.

### **Implementation**
- **Database Migration**: Modify enum to include 'CONSUMABLE'
- **Update All Tables**: `inventory`, `inventory_transactions`, `inventory_layers`
- **Update Services**: Modify category determination logic
- **Update Dashboard**: Add consumables section

### **Pros**
✅ **Clear Separation**: Consumables tracked separately from raw materials  
✅ **Better Reporting**: Can generate separate reports for consumables  
✅ **Flexible Logic**: Can implement different consumption patterns  
✅ **Dashboard Clarity**: Dedicated consumables section  
✅ **Future-Proof**: Easy to add consumable-specific features  

### **Cons**
❌ **Migration Required**: Need to alter enum in multiple tables  
❌ **More Code Changes**: Update all category-related logic  
❌ **Testing Required**: Ensure all queries handle new category  
❌ **Data Migration**: If existing data needs reclassification  

### **Code Changes Needed**

#### **1. Database Migration**
```php
// Create new migration
Schema::table('inventory', function (Blueprint $table) {
    $table->enum('category', ['RAW', 'WIP', 'FG', 'CONSUMABLE'])
        ->change();
});

// Same for inventory_transactions and inventory_layers
```

#### **2. Update GRN Processing**
```php
// In GRNProcessingService
private function determineItemCategory(GRNItem $grnItem): string
{
    // Check if consumable
    if ($this->isConsumable($grnItem->material_code)) {
        return 'CONSUMABLE';
    }
    
    // Existing logic for RAW/WIP/FG
    if ($grnItem->grn->isFromProductionOrder()) {
        return 'FG';
    }
    return 'RAW';
}

private function isConsumable(string $materialCode): bool
{
    $consumablePrefixes = ['GLUE', 'INK', 'PAPER', 'TAPE', 'LABEL'];
    foreach ($consumablePrefixes as $prefix) {
        if (str_starts_with($materialCode, $prefix)) {
            return true;
        }
    }
    return false;
}
```

#### **3. Update Dashboard**
```php
// In InventoryDashboard
public function getConsumablesQuantity()
{
    return Inventory::where('category', 'CONSUMABLE')
        ->sum('qty_available');
}

// Add to render()
$consumablesQuantity = $this->getConsumablesQuantity();
```

#### **4. Update Warehouse Mapping**
```php
// In GRNProcessingService
private function getWarehouseForCategory(string $category): string
{
    return match($category) {
        'RAW' => 'MAIN',
        'WIP' => 'PRODUCTION',
        'FG' => 'FINISHED_GOODS',
        'CONSUMABLE' => 'MAIN', // or 'CONSUMABLES' warehouse
        default => 'MAIN'
    };
}
```

---

## Option 3: Hybrid Approach (Recommended)

### **Approach**
Use `RAW` category but add a `item_type` or `material_type` field to distinguish consumables.

### **Implementation**
- Add `material_type` field: `'raw_material'`, `'consumable'`, `'component'`
- Keep category as `RAW` for both
- Use `material_type` for filtering and reporting

### **Pros**
✅ **Flexible**: Can filter by material type without category changes  
✅ **No Migration**: Add new field, no enum changes  
✅ **Backward Compatible**: Existing data works as-is  
✅ **Clear Separation**: Can distinguish in queries  
✅ **Dashboard Ready**: Easy to add consumables section  

### **Cons**
❌ **Additional Field**: Need to maintain material_type  
❌ **Query Complexity**: Need to filter by both category and material_type  
❌ **Still Mixed**: Technically still in RAW category  

### **Code Changes Needed**

#### **1. Database Migration**
```php
// Add material_type field
Schema::table('inventory', function (Blueprint $table) {
    $table->enum('material_type', ['raw_material', 'consumable', 'component'])
        ->default('raw_material')
        ->after('category');
});

// Same for inventory_transactions and inventory_layers
```

#### **2. Update Models**
```php
// In Inventory model
protected $fillable = [
    // ... existing fields
    'material_type',
];

// In InventoryTransaction model
protected $fillable = [
    // ... existing fields
    'material_type',
];
```

#### **3. Update GRN Processing**
```php
// In GRNProcessingService
private function determineItemCategory(GRNItem $grnItem): string
{
    return 'RAW'; // Always RAW for now
}

private function determineMaterialType(GRNItem $grnItem): string
{
    if ($this->isConsumable($grnItem->material_code)) {
        return 'consumable';
    }
    return 'raw_material';
}

// When creating inventory
Inventory::create([
    // ... existing fields
    'category' => 'RAW',
    'material_type' => $this->determineMaterialType($grnItem),
]);
```

#### **4. Update Dashboard**
```php
// In InventoryDashboard
public function getConsumablesQuantity()
{
    return Inventory::where('category', 'RAW')
        ->where('material_type', 'consumable')
        ->sum('qty_available');
}

public function getRawMaterialsQuantity()
{
    return Inventory::where('category', 'RAW')
        ->where('material_type', 'raw_material')
        ->sum('qty_available');
}
```

---

## Consumption Pattern Considerations

### **Current Consumption Flow**
```
Production Order → Consume RAW → Produce WIP → Consume WIP → Produce FG
```

### **Consumables Consumption**
Consumables may need different consumption patterns:

1. **Direct Consumption**: Linked to production quantity
   ```
   Production: 100 boxes → Consume: 5kg glue (0.05kg per box)
   ```

2. **Indirect Consumption**: Setup waste, spoilage
   ```
   Production: 100 boxes → Consume: 6kg glue (5kg direct + 1kg waste)
   ```

3. **Batch Consumption**: Used across multiple productions
   ```
   Glue batch opened → Used for multiple production orders
   ```

### **Implementation Options**

#### **Option A: Same as RAW Materials**
- Use existing `consume` transaction type
- Track consumption per production order
- FIFO costing applies

#### **Option B: Separate Consumption Logic**
- Add `consume_consumable` transaction type
- Track consumption differently
- May need consumption ratios/standards

---

## Recommendation: Option 3 (Hybrid Approach)

### **Why Hybrid Approach?**

1. **Minimal Disruption**: No enum changes, backward compatible
2. **Clear Separation**: Can distinguish consumables easily
3. **Flexible Reporting**: Can filter by material_type
4. **Easy Implementation**: Add one field, update logic
5. **Future-Proof**: Can add more material types later

### **Implementation Steps**

1. **Phase 1: Add Material Type Field**
   - Create migration to add `material_type` to all three tables
   - Update models to include new field
   - Set default to 'raw_material' for existing data

2. **Phase 2: Update GRN Processing**
   - Add `isConsumable()` method
   - Update `determineMaterialType()` method
   - Set material_type when creating inventory records

3. **Phase 3: Update Dashboard**
   - Add consumables quantity calculation
   - Add consumables section to dashboard view
   - Update category breakdown to show consumables separately

4. **Phase 4: Update Reports**
   - Add consumables filtering to reports
   - Create consumables-specific reports if needed

---

## Database Schema Changes (Hybrid Approach)

### **Migration Example**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add material_type to inventory table
        Schema::table('inventory', function (Blueprint $table) {
            $table->enum('material_type', ['raw_material', 'consumable', 'component'])
                ->default('raw_material')
                ->after('category');
        });

        // Add material_type to inventory_transactions table
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->enum('material_type', ['raw_material', 'consumable', 'component'])
                ->default('raw_material')
                ->after('category');
        });

        // Add material_type to inventory_layers table
        Schema::table('inventory_layers', function (Blueprint $table) {
            $table->enum('material_type', ['raw_material', 'consumable', 'component'])
                ->default('raw_material')
                ->after('category');
        });
    }

    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropColumn('material_type');
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropColumn('material_type');
        });

        Schema::table('inventory_layers', function (Blueprint $table) {
            $table->dropColumn('material_type');
        });
    }
};
```

---

## Dashboard Updates (Hybrid Approach)

### **New Dashboard Section**
```php
// In InventoryDashboard.php
public function getConsumablesQuantity()
{
    return Inventory::where('category', 'RAW')
        ->where('material_type', 'consumable')
        ->sum('qty_available');
}

public function getConsumablesByType()
{
    return Inventory::where('category', 'RAW')
        ->where('material_type', 'consumable')
        ->selectRaw('item_code, SUM(qty_available) as total_qty')
        ->groupBy('item_code')
        ->get();
}
```

### **View Updates**
```blade
<!-- Add consumables card -->
<div class="bg-purple-50 p-6 rounded-lg">
    <h3 class="text-lg font-semibold text-purple-800">Consumables</h3>
    <p class="text-3xl font-bold text-purple-600">
        {{ number_format($consumablesQuantity, 2) }}
    </p>
    <div class="mt-3 pt-3 border-t border-purple-200">
        @foreach($consumablesByType as $consumable)
            <p class="text-xs text-purple-600">
                {{ $consumable->item_code }}: 
                <span class="font-semibold">{{ $consumable->total_qty }}</span>
            </p>
        @endforeach
    </div>
</div>
```

---

## Consumption Tracking

### **Current System**
- Consumables tracked same as raw materials
- FIFO costing applies
- Consumption linked to production orders

### **Potential Enhancements**
1. **Consumption Standards**: Define standard consumption per unit
2. **Waste Tracking**: Track setup waste separately
3. **Batch Tracking**: Track opened batches for expiry
4. **Cost Allocation**: Allocate consumable costs to finished goods

---

## Summary

### **Best Approach: Hybrid (Option 3)**
- Add `material_type` field to distinguish consumables
- Keep category as `RAW` for simplicity
- Minimal code changes, maximum flexibility

### **Key Benefits**
✅ No enum migration required  
✅ Backward compatible  
✅ Clear separation for reporting  
✅ Easy to implement  
✅ Future-proof for more material types  

### **Next Steps**
1. Review and approve approach
2. Create database migration
3. Update models and services
4. Update dashboard
5. Test with sample consumable items

---

## Questions to Consider

1. **Do consumables need different costing?** (Currently FIFO applies to all)
2. **Do consumables need expiry tracking?** (Some may have shelf life)
3. **Do consumables need batch tracking?** (For quality control)
4. **How should consumable consumption be allocated?** (Direct or overhead)
5. **Do consumables need different warehouse locations?** (Currently same as RAW)

---

## Conclusion

**Yes, the current inventory system CAN handle consumables**, but we recommend the **Hybrid Approach (Option 3)** for the best balance of:
- Minimal changes
- Clear separation
- Flexible reporting
- Easy implementation

The system's FIFO costing, transaction tracking, and layer management will work perfectly for consumables, just like raw materials. The only difference is how we categorize and report them.

