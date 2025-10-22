# 📋 GRN & Material Request Transaction Examples

## Overview
This document provides detailed examples of how GRN (Goods Receipt Note) and Material Request (MR) transactions work in the Packaging ERP system, including database records, inventory movements, and costing methods.

---

## 🔄 GRN (Goods Receipt Note) Example Transactions

### Scenario: Receiving Raw Materials from Supplier

#### 1. GRN Creation
```php
// GRN Record
GRN::create([
    'grn_no' => 'GRN-2025-001',
    'lot_code' => 'LOT-20251020-000001',
    'received_date' => '2025-01-20',
    'supplier_order_id' => 1,
    'status' => 'pending',
    'notes' => 'Raw materials received from ABC Supplier'
]);
```

#### 2. GRN Items
```php
// GRN Item 1: Cardboard Sheets
GRNItem::create([
    'grn_id' => 1,
    'material_code' => 'CB-001',
    'description' => 'Cardboard Sheets 300GSM',
    'qty_received' => 1000.00,
    'uom' => 'PCS',
    'unit_cost' => 2.50,
    'total_cost' => 2500.00
]);

// GRN Item 2: Adhesive
GRNItem::create([
    'grn_id' => 1,
    'material_code' => 'AD-001',
    'description' => 'Industrial Adhesive',
    'qty_received' => 50.00,
    'uom' => 'KG',
    'unit_cost' => 15.00,
    'total_cost' => 750.00
]);
```

#### 3. Inventory Transactions (When GRN is Processed to Stock)
```php
// Transaction 1: Cardboard Sheets Receipt
InventoryTransaction::create([
    'lot_code' => 'LOT-20251020-000001',
    'item_code' => 'CB-001',
    'category' => 'RAW',
    'txn_type' => 'receipt',
    'qty' => 1000.00,
    'unit_cost' => 2.50,
    'total_cost' => 2500.00,
    'uom' => 'PCS',
    'warehouse' => 'RAW_WAREHOUSE',
    'related_doc_type' => 'GRN',
    'related_doc_id' => 1,
    'txn_date' => '2025-01-20',
    'costing_method' => 'FIFO',
    'remarks' => 'GRN Item: Cardboard Sheets 300GSM'
]);

// Transaction 2: Adhesive Receipt
InventoryTransaction::create([
    'lot_code' => 'LOT-20251020-000001',
    'item_code' => 'AD-001',
    'category' => 'RAW',
    'txn_type' => 'receipt',
    'qty' => 50.00,
    'unit_cost' => 15.00,
    'total_cost' => 750.00,
    'uom' => 'KG',
    'warehouse' => 'RAW_WAREHOUSE',
    'related_doc_type' => 'GRN',
    'related_doc_id' => 1,
    'txn_date' => '2025-01-20',
    'costing_method' => 'FIFO',
    'remarks' => 'GRN Item: Industrial Adhesive'
]);
```

#### 4. Inventory Records (Stock Levels)
```php
// Inventory Record 1: Cardboard Sheets
Inventory::create([
    'lot_code' => 'LOT-20251020-000001',
    'item_code' => 'CB-001',
    'category' => 'RAW',
    'qty_available' => 1000.00,
    'uom' => 'PCS',
    'warehouse' => 'RAW_WAREHOUSE',
    'source' => 'GRN',
    'ref_doc' => 1
]);

// Inventory Record 2: Adhesive
Inventory::create([
    'lot_code' => 'LOT-20251020-000001',
    'item_code' => 'AD-001',
    'category' => 'RAW',
    'qty_available' => 50.00,
    'uom' => 'KG',
    'warehouse' => 'RAW_WAREHOUSE',
    'source' => 'GRN',
    'ref_doc' => 1
]);
```

#### 5. Inventory Layers (FIFO/LIFO Tracking)
```php
// Layer 1: Cardboard Sheets
InventoryLayer::create([
    'lot_code' => 'LOT-20251020-000001',
    'item_code' => 'CB-001',
    'category' => 'RAW',
    'qty_available' => 1000.00,
    'unit_cost' => 2.50,
    'total_cost' => 2500.00,
    'receipt_date' => '2025-01-20',
    'warehouse' => 'RAW_WAREHOUSE',
    'source_doc_type' => 'GRN',
    'source_doc_id' => 1
]);

// Layer 2: Adhesive
InventoryLayer::create([
    'lot_code' => 'LOT-20251020-000001',
    'item_code' => 'AD-001',
    'category' => 'RAW',
    'qty_available' => 50.00,
    'unit_cost' => 15.00,
    'total_cost' => 750.00,
    'receipt_date' => '2025-01-20',
    'warehouse' => 'RAW_WAREHOUSE',
    'source_doc_type' => 'GRN',
    'source_doc_id' => 1
]);
```

---

## 📝 Material Request (MR) Example Transactions

### Scenario: Requesting Materials for Production

#### 1. Material Request Creation
```php
// Material Request Record
MaterialRequest::create([
    'mr_no' => 'MR-2025-001',
    'job_order_id' => 1,
    'material_code' => 'CB-001',
    'description' => 'Cardboard Sheets for Box Production',
    'qty_requested' => 500.00,
    'uom' => 'PCS',
    'status' => 'pending',
    'requested_by' => 'Production Manager',
    'requested_date' => '2025-01-21'
]);
```

#### 2. Material Request Processing (When Approved & Issued)
```php
// Find available inventory (FIFO - oldest first)
$availableInventory = Inventory::where('item_code', 'CB-001')
    ->where('category', 'RAW')
    ->where('qty_available', '>', 0)
    ->orderBy('created_at')
    ->first();

// Consume materials from inventory
InventoryTransaction::create([
    'lot_code' => 'LOT-20251020-000001', // From GRN
    'item_code' => 'CB-001',
    'category' => 'RAW',
    'txn_type' => 'consume',
    'qty' => 500.00,
    'unit_cost' => 2.50, // FIFO cost from layer
    'total_cost' => 1250.00,
    'uom' => 'PCS',
    'warehouse' => 'RAW_WAREHOUSE',
    'related_doc_type' => 'MaterialRequest',
    'related_doc_id' => 1,
    'txn_date' => '2025-01-21',
    'costing_method' => 'FIFO',
    'remarks' => 'Materials issued for production - Job Order #JO-001'
]);
```

#### 3. Update Inventory Levels
```php
// Update inventory quantity (reduce by consumed amount)
$inventory = Inventory::where('lot_code', 'LOT-20251020-000001')->first();
$inventory->update([
    'qty_available' => $inventory->qty_available - 500.00 // 1000 - 500 = 500
]);

// Update inventory layer
$layer = InventoryLayer::where('lot_code', 'LOT-20251020-000001')->first();
$layer->update([
    'qty_available' => $layer->qty_available - 500.00 // 1000 - 500 = 500
]);
```

#### 4. Update Material Request Status
```php
// Mark material request as issued
MaterialRequest::find(1)->update([
    'status' => 'issued',
    'issued_date' => '2025-01-21',
    'issued_by' => 'Warehouse Manager'
]);
```

---

## 🔄 Complete Transaction Flow Example

### Step 1: Supplier Order → GRN → Stock
```
Supplier Order: PO-2025-001
    ↓
GRN: GRN-2025-001 (Receives 1000 PCS Cardboard)
    ↓
Inventory: +1000 PCS Cardboard (RAW category)
    ↓
Layer: FIFO layer created with cost $2.50/unit
```

### Step 2: Production Order → Material Request → Consumption
```
Production Order: PROD-2025-001
    ↓
Material Request: MR-2025-001 (Requests 500 PCS Cardboard)
    ↓
Inventory Transaction: -500 PCS Cardboard (consume type)
    ↓
Inventory: 500 PCS remaining (1000 - 500)
    ↓
Layer: 500 PCS remaining in FIFO layer
```

### Step 3: Production Complete → WIP to FG
```
Production Complete
    ↓
Inventory Transaction: +100 PCS Finished Boxes (produce type)
    ↓
Inventory: +100 PCS Finished Boxes (FG category)
    ↓
WIP Inventory: -500 PCS Cardboard (consume type)
```

---

## 📊 Transaction Summary

### GRN Transactions:
- **Purpose**: Receiving materials from suppliers
- **Transaction Type**: `receipt`
- **Category**: `RAW`
- **Effect**: Increases inventory stock
- **Costing**: FIFO/LIFO layer creation

### Material Request Transactions:
- **Purpose**: Issuing materials for production
- **Transaction Type**: `consume`
- **Category**: `RAW` → `WIP`
- **Effect**: Decreases inventory stock
- **Costing**: FIFO/LIFO layer consumption

### Key Data Points:
- **Lot Tracking**: Every transaction linked to lot code
- **Cost Tracking**: Unit costs and total costs recorded
- **Document References**: Links to source documents (GRN, MR, Job Orders)
- **Warehouse Management**: Location-based inventory tracking
- **Audit Trail**: Complete transaction history with timestamps

---

## 🏗️ Database Schema Overview

### Core Tables:
1. **`grns`** - Goods Receipt Notes
2. **`grn_items`** - Individual items in GRN
3. **`material_requests`** - Material request records
4. **`inventory`** - Current stock levels
5. **`inventory_transactions`** - All stock movements
6. **`inventory_layers`** - FIFO/LIFO layer tracking

### Transaction Types:
- **`receipt`** - Stock coming in (GRN processing)
- **`consume`** - Stock going out for production
- **`produce`** - Finished goods created
- **`delivery`** - Stock shipped to customers

### Categories:
- **`RAW`** - Raw materials
- **`WIP`** - Work in progress
- **`FG`** - Finished goods

---

## 💡 Business Benefits

1. **Complete Traceability**: Track every item from receipt to delivery
2. **Accurate Costing**: FIFO/LIFO methods for precise cost calculation
3. **Real-time Visibility**: Current stock levels and movements
4. **Compliance**: Audit trail for all transactions
5. **Decision Support**: Data for inventory optimization

This system provides **enterprise-level** inventory management with advanced costing, complete traceability, and real-time reporting capabilities! 🚀
