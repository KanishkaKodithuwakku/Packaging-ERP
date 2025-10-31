# Inventory System Data Flow

## Overview

The inventory system uses three interconnected tables to track stock movements, costs, and current balances:

- **`inventory_transactions`**: Immutable ledger of every transaction
- **`inventory_layers`**: FIFO/LIFO costing layers for receipts
- **`inventory`**: Current balance by lot code (fast dashboard totals)

---

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                        PURCHASE ORDER FLOW                          │
└─────────────────────────────────────────────────────────────────────┘

PO Confirmed
    │
    ├─> Create GRN
    │   │
    │   ├─> Record transaction in inventory_transactions (receipt)
    │   │   └─> qty: +qty, txn_type: 'receipt', category: 'RAW'
    │   │
    │   ├─> Create layer in inventory_layers
    │   │   └─> qty_available: qty, unit_cost, receipt_date
    │   │
    │   └─> Create/Update inventory record
    │       └─> qty_available: qty, total_value
    │
    └─> Dashboard shows RAW stock


┌─────────────────────────────────────────────────────────────────────┐
│                      PRODUCTION FLOW                                │
└─────────────────────────────────────────────────────────────────────┘

Start Production Order
    │
    ├─> Consume RAW → Produce WIP
    │   │
    │   ├─> Record transactions in inventory_transactions
    │   │   ├─> Consume: qty: -qty, txn_type: 'consume', category: 'RAW'
    │   │   └─> Produce: qty: +qty, txn_type: 'produce', category: 'WIP'
    │   │
    │   ├─> Reduce layers in inventory_layers (FIFO)
    │   │   └─> Deduct from oldest layers, qty_available decreases
    │   │
    │   └─> Update inventory records
    │       ├─> RAW: qty_available decreases
    │       └─> WIP: qty_available increases
    │
    ├─> Complete Production Items
    │   │
    │   ├─> Record transactions in inventory_transactions
    │   │   ├─> Consume: qty: -qty, txn_type: 'consume', category: 'WIP'
    │   │   └─> Produce: qty: +qty, txn_type: 'receipt', category: 'FG'
    │   │
    │   ├─> Reduce/create layers in inventory_layers
    │   │   ├─> Reduce WIP layers (FIFO)
    │   │   └─> Create FG layer with cost
    │   │
    │   └─> Update inventory records
    │       ├─> WIP: qty_available decreases
    │       └─> FG: qty_available increases
    │
    └─> Dashboard shows updated RAW/WIP/FG balances


┌─────────────────────────────────────────────────────────────────────┐
│                      DELIVERY FLOW                                  │
└─────────────────────────────────────────────────────────────────────┘

Ship Finished Goods to Customer
    │
    ├─> Record transaction in inventory_transactions (delivery)
    │   └─> qty: -qty, txn_type: 'delivery', category: 'FG'
    │
    ├─> Reduce layers in inventory_layers (FIFO)
    │   └─> Deduct from oldest FG layers
    │
    └─> Update inventory record
        └─> FG: qty_available decreases
```

---

## Complete Example with Data

### Scenario

- Purchase 1,000 units of Raw Material at $10.00 per unit
- Receive 600 units first (GRN #1), then 400 units (GRN #2) at $12.00 each
- Start production: consume 750 RAW units → produce 750 WIP
- Complete production: convert 500 WIP → 500 FG
- Deliver 200 FG units to customer

---

### Step 1: Receive First GRN (600 units @ $10.00)

**What happens:**
- 600 units of RAW material received

**Data inserted:**

**`inventory_transactions`**
| lot_code | item_code | category | txn_type | qty | unit_cost | total_cost | warehouse | related_doc |
|----------|-----------|----------|----------|-----|-----------|------------|-----------|-------------|
| RAW-1    | RM-001    | RAW      | receipt  | 600 | 10.00     | 6,000.00   | MAIN      | GRN-001     |

**`inventory_layers`** (FIFO Layer A)
| lot_code | item_code | category | qty_available | unit_cost | total_cost | receipt_date |
|----------|-----------|----------|---------------|-----------|------------|--------------|
| RAW-1    | RM-001    | RAW      | 600           | 10.00     | 6,000.00   | 2025-01-15   |

**`inventory`**
| lot_code | item_code | category | qty_available | unit_cost | total_value |
|----------|-----------|----------|---------------|-----------|-------------|
| RAW-1    | RM-001    | RAW      | 600           | 10.00     | 6,000.00    |

**Dashboard:** RAW Materials = 600

---

### Step 2: Receive Second GRN (400 units @ $12.00)

**What happens:**
- 400 additional RAW units received at higher price

**Data inserted:**

**`inventory_transactions`**
| lot_code | item_code | category | txn_type | qty | unit_cost | total_cost | warehouse | related_doc |
|----------|-----------|----------|----------|-----|-----------|------------|-----------|-------------|
| RAW-2    | RM-001    | RAW      | receipt  | 400 | 12.00     | 4,800.00   | MAIN      | GRN-002     |

**`inventory_layers`** (FIFO Layer B added)
| lot_code | item_code | category | qty_available | unit_cost | total_cost | receipt_date |
|----------|-----------|----------|---------------|-----------|------------|--------------|
| RAW-1    | RM-001    | RAW      | 600           | 10.00     | 6,000.00   | 2025-01-15   |
| RAW-2    | RM-001    | RAW      | 400           | 12.00     | 4,800.00   | 2025-01-20   |

**`inventory`**
| lot_code | item_code | category | qty_available | unit_cost | total_value |
|----------|-----------|----------|---------------|-----------|-------------|
| RAW-1    | RM-001    | RAW      | 600           | 10.00     | 6,000.00    |
| RAW-2    | RM-001    | RAW      | 400           | 12.00     | 4,800.00    |

**Dashboard:** RAW Materials = 1,000 (600 + 400)

---

### Step 3: Start Production (Consume 750 RAW → Produce 750 WIP)

**What happens:**
- Production order consumes 750 RAW units
- Creates 750 WIP units
- FIFO costing: uses oldest layers first

**FIFO Layer Consumption:**
- Layer A (600 @ $10): fully consumed → cost = $6,000.00
- Layer B (400 @ $12): 150 units consumed → cost = $1,800.00
- **Total cost allocated:** $7,800.00

**Data inserted/updated:**

**`inventory_transactions`**
| lot_code | item_code | category | txn_type | qty  | unit_cost | total_cost | warehouse  | related_doc |
|----------|-----------|----------|----------|------|-----------|------------|------------|-------------|
| RAW-1    | RM-001    | RAW      | consume  | -600 | 10.00     | -6,000.00  | MAIN       | PROD-001    |
| RAW-2    | RM-001    | RAW      | consume  | -150 | 12.00     | -1,800.00  | MAIN       | PROD-001    |
| WIP-1    | RM-001    | WIP      | produce  | +750 | 10.40     | 7,800.00   | PRODUCTION | PROD-001    |

**`inventory_layers`** (updated)
| lot_code | item_code | category | qty_available | unit_cost | total_cost | receipt_date |
|----------|-----------|----------|---------------|-----------|------------|--------------|
| ~~RAW-1~~ | ~~RM-001~~ | ~~RAW~~  | ~~0~~          | ~~10.00~~  | ~~0.00~~    | ~~2025-01-15~~ |
| RAW-2    | RM-001    | RAW      | 250           | 12.00     | 3,000.00   | 2025-01-20   |
| WIP-1    | RM-001    | WIP      | 750           | 10.40     | 7,800.00   | 2025-01-25   |

**`inventory`** (updated)
| lot_code | item_code | category | qty_available | unit_cost | total_value |
|----------|-----------|----------|---------------|-----------|-------------|
| RAW-2    | RM-001    | RAW      | 250           | 12.00     | 3,000.00    |
| WIP-1    | RM-001    | WIP      | 750           | 10.40     | 7,800.00    |

**Dashboard:** RAW = 250 | WIP = 750 | FG = 0

---

### Step 4: Complete Production Items (500 FG)

**What happens:**
- 500 WIP units converted to 500 FG units
- FIFO costing applied to WIP consumption

**FIFO WIP Consumption:**
- From WIP-1 layer (750 @ $10.40): consume 500 → cost = $5,200.00

**Data inserted/updated:**

**`inventory_transactions`**
| lot_code | item_code | category | txn_type | qty  | unit_cost | total_cost | warehouse      | related_doc |
|----------|-----------|----------|----------|------|-----------|------------|----------------|-------------|
| WIP-1    | RM-001    | WIP      | consume  | -500 | 10.40     | -5,200.00  | PRODUCTION     | PROD-001    |
| FG-1     | BOX-001   | FG       | receipt  | +500 | 10.40     | 5,200.00   | FINISHED_GOODS | GRN-003     |

**`inventory_layers`** (updated)
| lot_code | item_code | category | qty_available | unit_cost | total_cost | receipt_date |
|----------|-----------|----------|---------------|-----------|------------|--------------|
| RAW-2    | RM-001    | RAW      | 250           | 12.00     | 3,000.00   | 2025-01-20   |
| WIP-1    | RM-001    | WIP      | 250           | 10.40     | 2,600.00   | 2025-01-25   |
| FG-1     | BOX-001   | FG       | 500           | 10.40     | 5,200.00   | 2025-01-28   |

**`inventory`** (updated)
| lot_code | item_code | category | qty_available | unit_cost | total_value |
|----------|-----------|----------|---------------|-----------|-------------|
| RAW-2    | RM-001    | RAW      | 250           | 12.00     | 3,000.00    |
| WIP-1    | RM-001    | WIP      | 250           | 10.40     | 2,600.00    |
| FG-1     | BOX-001   | FG       | 500           | 10.40     | 5,200.00    |

**Dashboard:** RAW = 250 | WIP = 250 | FG = 500

---

### Step 5: Deliver 200 FG to Customer

**What happens:**
- Ship 200 FG units to customer
- FIFO costing applied to delivery

**FIFO FG Consumption:**
- From FG-1 layer (500 @ $10.40): consume 200 → cost = $2,080.00

**Data inserted/updated:**

**`inventory_transactions`**
| lot_code | item_code | category | txn_type | qty  | unit_cost | total_cost | warehouse      | related_doc |
|----------|-----------|----------|----------|------|-----------|------------|----------------|-------------|
| FG-1     | BOX-001   | FG       | delivery | -200 | 10.40     | -2,080.00  | FINISHED_GOODS | DELIV-001   |

**`inventory_layers`** (updated)
| lot_code | item_code | category | qty_available | unit_cost | total_cost | receipt_date |
|----------|-----------|----------|---------------|-----------|------------|--------------|
| RAW-2    | RM-001    | RAW      | 250           | 12.00     | 3,000.00   | 2025-01-20   |
| WIP-1    | RM-001    | WIP      | 250           | 10.40     | 2,600.00   | 2025-01-25   |
| FG-1     | BOX-001   | FG       | 300           | 10.40     | 3,120.00   | 2025-01-28   |

**`inventory`** (updated)
| lot_code | item_code | category | qty_available | unit_cost | total_value |
|----------|-----------|----------|---------------|-----------|-------------|
| RAW-2    | RM-001    | RAW      | 250           | 12.00     | 3,000.00    |
| WIP-1    | RM-001    | WIP      | 250           | 10.40     | 2,600.00    |
| FG-1     | BOX-001   | FG       | 300           | 10.40     | 3,120.00    |

**Dashboard:** RAW = 250 | WIP = 250 | FG = 300

---

## Summary Table

### Final State Summary

| Transaction Type | RAW  | WIP  | FG   | Total Cost     |
|------------------|------|------|------|----------------|
| **Receipts**     | 1,000| -    | 500  | $15,800.00     |
| **Consumed**     | 750  | 500  | 200  | $13,080.00     |
| **Delivered**    | -    | -    | 200  | $2,080.00      |
| **Remaining**    | 250  | 250  | 300  | $8,720.00      |

---

## Key Principles

### 1. Transaction Flow
Every movement creates an entry in `inventory_transactions` that is never modified.

### 2. Costing Method (FIFO)
`inventory_layers` keeps receipts by date. Consumes oldest first; updates `qty_available`; deletes empty layers.

### 3. Current Balance
`inventory` provides per-lot totals for dashboards and reports.

### 4. Triple Entry Accounting
All three tables stay in sync:
- `inventory_transactions` = complete history
- `inventory_layers` = costing layers
- `inventory` = current balance

---

## How Dashboard Calculations Work

```sql
-- RAW Materials Total
SELECT SUM(qty_available) 
FROM inventory 
WHERE category = 'RAW';

-- WIP Total
SELECT SUM(qty_available) 
FROM inventory 
WHERE category = 'WIP';

-- Finished Goods Total
SELECT SUM(qty_available) 
FROM inventory 
WHERE category = 'FG';

-- Total Inventory Value
SELECT SUM(total_value) 
FROM inventory;

-- By Warehouse
SELECT warehouse, SUM(qty_available), SUM(total_value)
FROM inventory
GROUP BY warehouse;
```

---

## Common Operations

### Add Stock (Receipt)
1. Insert `inventory_transactions` (receipt)
2. Insert `inventory_layers`
3. Insert/update `inventory`

### Move Stock (Consume/Produce)
1. Insert `inventory_transactions` (consume)
2. Insert `inventory_transactions` (produce)
3. Reduce layers in `inventory_layers` (FIFO)
4. Create new layers if producing
5. Update both lots in `inventory`

### Deliver Stock (Delivery)
1. Insert `inventory_transactions` (delivery)
2. Reduce layers in `inventory_layers` (FIFO)
3. Update `inventory`

---

## Error Prevention

1. Never delete from `inventory_transactions`
2. Keep layers FIFO sorted by `receipt_date`
3. Update all three tables in a transaction
4. Validate `qty_available >= 0` before consumes
5. Reconcile `inventory` against `inventory_layers`

