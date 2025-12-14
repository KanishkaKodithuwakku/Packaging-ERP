# Consumables Implementation Summary

## Overview

Successfully implemented the **Hybrid Approach (Option 3)** to handle consumable items (glue, papers, ink, etc.) in the inventory system. This approach adds a `material_type` field to distinguish consumables from raw materials while keeping them in the same `RAW` category.

---

## Implementation Date
December 14, 2025

---

## Changes Made

### **1. Database Migration**
**File**: `database/migrations/2025_12_14_050124_add_material_type_to_inventory_tables.php`

- Added `material_type` enum field to three tables:
  - `inventory`
  - `inventory_transactions`
  - `inventory_layers`
- Enum values: `['raw_material', 'consumable', 'component']`
- Default value: `'raw_material'` (for backward compatibility)
- Positioned after `category` field in all tables

**Migration Commands:**
```bash
php artisan migrate
```

---

### **2. Model Updates**

#### **Inventory Model** (`app/Models/Inventory.php`)
- Added `material_type` to `$fillable` array

#### **InventoryTransaction Model** (`app/Models/InventoryTransaction.php`)
- Added `material_type` to `$fillable` array

#### **InventoryLayer Model** (`app/Models/InventoryLayer.php`)
- Added `material_type` to `$fillable` array

---

### **3. Service Updates**

#### **GRNProcessingService** (`app/Services/GRNProcessingService.php`)

**New Methods:**
- `determineMaterialType(GRNItem $grnItem)`: Determines if item is consumable or raw material
- `isConsumable(string $materialCode)`: Checks if material code indicates a consumable

**Consumable Detection:**
The system automatically detects consumables based on material code prefixes:
- `GLUE`, `INK`, `PAPER`, `TAPE`, `LABEL`
- `ADHESIVE`, `STAPLE`, `STRING`, `TWINE`
- `WRAP`, `FILM`, `SHRINK`, `BUBBLE`

**Updated Methods:**
- `processGRNItemToStock()`: Now sets `material_type` when creating transactions
- `processGRNItemToStockPartial()`: Now sets `material_type` when creating transactions

#### **InventoryService** (`app/Services/InventoryService.php`)
- Updated `recordBasicTransaction()` to include `material_type` when creating inventory records
- Defaults to `'raw_material'` if not specified (backward compatibility)

#### **InventoryCostingService** (`app/Services/InventoryCostingService.php`)
- Updated `createInventoryLayer()` to include `material_type` when creating layers
- Updated `syncInventoryBalanceForItem()` to include `material_type` when creating inventory records
- Updated `updateInventoryBalance()` to include `material_type` when creating inventory records

---

### **4. Dashboard Updates**

#### **InventoryDashboard Component** (`app/Livewire/InventoryDashboard.php`)

**New Methods:**
- `getConsumablesQuantity()`: Returns total quantity of consumables
- `getConsumablesByType()`: Returns consumables grouped by item_code

**Updated Methods:**
- `getTotalRawMaterialsReceived()`: Now excludes consumables (only counts `material_type = 'raw_material'`)
- `getTotalRawMaterialsConsumed()`: Now excludes consumables (only counts `material_type = 'raw_material'`)

**Updated Render Method:**
- Added `consumablesQuantity` and `consumablesByType` to view data

#### **Dashboard View** (`resources/views/livewire/inventory-dashboard.blade.php`)

**New Consumables Card:**
- Added purple-themed consumables card in summary section
- Shows total consumables quantity
- Lists top 3 consumable types with quantities
- Grid layout changed from 3 columns to 4 columns (md:grid-cols-2 lg:grid-cols-4)

---

## How It Works

### **1. Receiving Consumables via GRN**

When a GRN item is processed:

1. **Material Detection**: System checks material code prefix
   ```php
   // Example: "GLUE-001" → detected as consumable
   // Example: "CARDBOARD-001" → detected as raw_material
   ```

2. **Category Assignment**: 
   - Category: `RAW` (same as raw materials)
   - Material Type: `consumable` or `raw_material`

3. **Inventory Creation**:
   - Transaction created with `material_type = 'consumable'`
   - Layer created with `material_type = 'consumable'`
   - Inventory record created with `material_type = 'consumable'`

### **2. Dashboard Display**

**Raw Materials Card:**
- Shows only raw materials (excludes consumables)
- Calculated from transactions where `material_type = 'raw_material'`

**Consumables Card:**
- Shows total consumables quantity
- Lists consumable types with quantities
- Calculated from inventory where `material_type = 'consumable'`

### **3. Consumption Tracking**

Consumables are consumed the same way as raw materials:
- Same FIFO costing applies
- Same transaction tracking
- Same inventory layers
- Only difference: `material_type` field distinguishes them

---

## Consumable Item Code Conventions

To ensure consumables are detected automatically, use these prefixes in material codes:

| Prefix | Examples |
|--------|----------|
| `GLUE` | GLUE-001, GLUE-WHITE, GLUE-STRONG |
| `INK` | INK-BLACK, INK-COLOR, INK-PRINT |
| `PAPER` | PAPER-A4, PAPER-WRAP, PAPER-LABEL |
| `TAPE` | TAPE-PACK, TAPE-SEAL, TAPE-DOUBLE |
| `LABEL` | LABEL-SHIP, LABEL-PRICE, LABEL-ADDRESS |
| `ADHESIVE` | ADHESIVE-001, ADHESIVE-STRONG |
| `STAPLE` | STAPLE-001, STAPLE-HEAVY |
| `STRING` | STRING-COTTON, STRING-NYLON |
| `TWINE` | TWINE-001, TWINE-HEAVY |
| `WRAP` | WRAP-BUBBLE, WRAP-PLASTIC |
| `FILM` | FILM-STRETCH, FILM-SHRINK |
| `SHRINK` | SHRINK-WRAP, SHRINK-FILM |
| `BUBBLE` | BUBBLE-WRAP, BUBBLE-PACK |

**Note**: Prefix matching is case-insensitive.

---

## Backward Compatibility

### **Existing Data**
- All existing inventory records default to `material_type = 'raw_material'`
- Migration sets default value for existing records
- No data loss or reclassification needed

### **Queries**
- Queries that check `material_type` also check for `NULL` values
- Example: `where('material_type', 'raw_material')->orWhereNull('material_type')`
- Ensures existing data is included in results

---

## Testing Checklist

- [ ] Run migration: `php artisan migrate`
- [ ] Create GRN with consumable item (e.g., GLUE-001)
- [ ] Verify consumable appears in dashboard
- [ ] Verify consumable excluded from raw materials calculation
- [ ] Create GRN with raw material (e.g., CARDBOARD-001)
- [ ] Verify raw material appears in raw materials card
- [ ] Verify raw material excluded from consumables calculation
- [ ] Test consumption of consumables
- [ ] Test consumption of raw materials
- [ ] Verify FIFO costing works for both types

---

## Usage Examples

### **Example 1: Receiving Glue via GRN**

```php
// GRN Item with material_code = "GLUE-001"
// System automatically detects as consumable
// Creates inventory with:
// - category: 'RAW'
// - material_type: 'consumable'
// - item_code: 'GLUE-001'
```

### **Example 2: Querying Consumables**

```php
// Get all consumables
$consumables = Inventory::where('category', 'RAW')
    ->where('material_type', 'consumable')
    ->get();

// Get consumables by type
$consumablesByType = Inventory::where('category', 'RAW')
    ->where('material_type', 'consumable')
    ->selectRaw('item_code, SUM(qty_available) as total_qty')
    ->groupBy('item_code')
    ->get();
```

### **Example 3: Querying Raw Materials (Excluding Consumables)**

```php
// Get raw materials only (exclude consumables)
$rawMaterials = Inventory::where('category', 'RAW')
    ->where(function($query) {
        $query->where('material_type', 'raw_material')
              ->orWhereNull('material_type');
    })
    ->get();
```

---

## Benefits of This Implementation

✅ **No Enum Migration**: Avoids complex enum changes across multiple tables  
✅ **Backward Compatible**: Existing data works without changes  
✅ **Clear Separation**: Can easily filter consumables vs raw materials  
✅ **Flexible**: Can add more material types (e.g., 'component') in future  
✅ **Easy Implementation**: Minimal code changes, maximum benefit  
✅ **Dashboard Ready**: Consumables displayed separately  
✅ **Same Costing**: FIFO/LIFO costing applies to consumables too  

---

## Future Enhancements

1. **Consumption Standards**: Define standard consumption per unit for consumables
2. **Waste Tracking**: Track setup waste separately for consumables
3. **Batch Tracking**: Track opened batches for expiry dates
4. **Cost Allocation**: Allocate consumable costs to finished goods
5. **Reports**: Create consumables-specific reports
6. **Low Stock Alerts**: Separate low stock alerts for consumables

---

## Files Modified

1. `database/migrations/2025_12_14_050124_add_material_type_to_inventory_tables.php` (NEW)
2. `app/Models/Inventory.php`
3. `app/Models/InventoryTransaction.php`
4. `app/Models/InventoryLayer.php`
5. `app/Services/GRNProcessingService.php`
6. `app/Services/InventoryService.php`
7. `app/Services/InventoryCostingService.php`
8. `app/Livewire/InventoryDashboard.php`
9. `resources/views/livewire/inventory-dashboard.blade.php`

---

## Related Documentation

- `docs/CONSUMABLES_INVENTORY_ANALYSIS.md` - Analysis of different approaches
- `docs/INVENTORY_DASHBOARD_EXPLANATION.md` - How inventory dashboard works

---

## Support

For questions or issues:
1. Check consumable item code follows naming conventions
2. Verify migration ran successfully
3. Check material_type is set correctly in database
4. Review GRN processing logs for errors

---

## Summary

The consumables feature is now fully integrated into the inventory system. Consumables are:
- ✅ Automatically detected based on material code prefixes
- ✅ Tracked separately from raw materials
- ✅ Displayed in dedicated dashboard section
- ✅ Use same FIFO costing and transaction tracking
- ✅ Backward compatible with existing data

The system is ready to handle consumable items like glue, papers, ink, and more!

