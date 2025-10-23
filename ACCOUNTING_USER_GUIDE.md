# 📊 Accounting Module - Complete User Guide (A to Z)

## 📋 Table of Contents
1. [Introduction](#introduction)
2. [Getting Started](#getting-started)
3. [Understanding Double-Entry Bookkeeping](#understanding-double-entry-bookkeeping)
4. [Accounting Dashboard](#accounting-dashboard)
5. [Chart of Accounts](#chart-of-accounts)
6. [Managing Entries (Vouchers)](#managing-entries-vouchers)
7. [Entry Types](#entry-types)
8. [Advanced Search](#advanced-search)
9. [Financial Reports](#financial-reports)
10. [Bank Reconciliation](#bank-reconciliation)
11. [Multi-Currency Management](#multi-currency-management)
12. [Best Practices](#best-practices)
13. [Common Scenarios](#common-scenarios)
14. [Troubleshooting](#troubleshooting)

---

## 🎯 Introduction

The Accounting Module is a comprehensive double-entry bookkeeping system that helps you manage your company's financial transactions, generate reports, and maintain accurate financial records.

### Key Features:
- ✅ **Double-Entry Bookkeeping** - Every transaction maintains debit = credit
- ✅ **Hierarchical Chart of Accounts** - Organize accounts in parent-child structure
- ✅ **Multiple Entry Types** - Journal, Receipt, Payment, Contra, Sales, Purchase vouchers
- ✅ **Multi-Currency Support** - Handle transactions in multiple currencies
- ✅ **Financial Reports** - Balance Sheet, P&L, Trial Balance, and more
- ✅ **Bank Reconciliation** - Match bank statements with your records
- ✅ **Advanced Search** - Find entries with multiple filter criteria
- ✅ **Audit Trail** - Complete logging of all accounting activities

---

## 🚀 Getting Started

### Accessing the Accounting Module

#### Step 1: Login to the System
1. Navigate to your ERP system URL
2. Enter your username and password
3. Click **"Login"**

#### Step 2: Navigate to Accounting Section
From the main dashboard, locate the **"Accounting"** section in the left sidebar.

```
📂 Accounting
├── Dashboard          (Overview and quick stats)
├── Chart of Accounts  (Manage groups and ledgers)
├── Entries           (Record transactions)
├── Search            (Find specific entries)
├── Reports           (Financial statements)
│   ├── Balance Sheet
│   ├── Profit & Loss
│   ├── Trial Balance
│   ├── Ledger Statement
│   ├── Ledger Entries
│   └── Reconciliation
├── Currencies        (Multi-currency setup)
├── Exchange Rates    (Currency conversion rates)
└── Entry Types       (Voucher type configuration)
```

### Initial Setup Checklist

Before you start recording transactions, ensure:

- [ ] **Chart of Accounts is set up** (Default structure is pre-configured)
- [ ] **Ledgers are created** for all your banks, suppliers, customers, expenses
- [ ] **Entry Types are configured** (6 default types are pre-configured)
- [ ] **Financial Year is set** (Check in account settings)
- [ ] **Opening Balances are entered** (For all ledgers with previous balances)
- [ ] **Currencies and Exchange Rates** (If using multi-currency)

---

## 📚 Understanding Double-Entry Bookkeeping

### The Golden Rules

Every financial transaction affects at least two accounts:
- **One account is debited (Dr)** - Receives value
- **One account is credited (Cr)** - Gives value
- **Total Debits = Total Credits** (Always balanced)

### Account Types and Rules

| Account Type | Increase | Decrease | Normal Balance |
|--------------|----------|----------|----------------|
| **Assets** | Debit | Credit | Debit |
| **Liabilities** | Credit | Debit | Credit |
| **Income** | Credit | Debit | Credit |
| **Expenses** | Debit | Credit | Debit |

### Practical Examples

#### Example 1: Paying Rent (Expenses)
```
Transaction: Paid Rs. 50,000 rent by cash
Entry:
├── Rent Expense (Debit)     Rs. 50,000  [Expense increases]
└── Cash in Hand (Credit)    Rs. 50,000  [Asset decreases]
```

#### Example 2: Receiving Payment from Customer (Income)
```
Transaction: Received Rs. 100,000 from customer
Entry:
├── Bank Account (Debit)     Rs. 100,000  [Asset increases]
└── Sales Income (Credit)    Rs. 100,000  [Income increases]
```

#### Example 3: Purchase on Credit (Liability)
```
Transaction: Purchased goods worth Rs. 75,000 on credit
Entry:
├── Purchase Expense (Debit)      Rs. 75,000  [Expense increases]
└── Accounts Payable (Credit)     Rs. 75,000  [Liability increases]
```

---

## 🏠 Accounting Dashboard

### Overview
The Accounting Dashboard provides a quick snapshot of your financial position.

### Accessing the Dashboard
**Navigation**: Accounting → Dashboard

### Dashboard Components

#### 1. Account Details
Displays basic company and account information:
- **Company Name** - Your organization name
- **Financial Year** - Current accounting period
- **Base Currency** - Primary currency (e.g., LKR)
- **Current User** - Logged-in user name
- **User Role** - Your access level

#### 2. Bank & Cash Summary
Shows current balances of all bank and cash accounts:
```
┌─────────────────────────────────┐
│ Bank & Cash Summary             │
├─────────────────────────────────┤
│ Cash in Hand        Rs. 50,000  │
│ Bank - Commercial   Rs. 250,000 │
│ Bank - Savings      Rs. 100,000 │
└─────────────────────────────────┘
Total: Rs. 400,000
```

#### 3. Account Summary
Overview of main account categories:
- **Assets** - Total value of what you own
- **Liabilities** - Total value of what you owe
- **Income** - Total earnings
- **Expenses** - Total costs

#### 4. Recent Activity
Last 10 transactions recorded in the system:
- Entry date and number
- Entry type (JV, RV, PV, etc.)
- Amount
- Narration/description

#### 5. Visual Charts
- **Monthly Income vs Expenses** - Line chart showing trends
- **Assets vs Liabilities** - Doughnut chart showing distribution

### Interpreting the Dashboard

**Healthy Indicators:**
- ✅ Assets > Liabilities (Positive net worth)
- ✅ Income > Expenses (Profitable operations)
- ✅ Adequate cash/bank balance
- ✅ Consistent upward income trend

**Warning Signs:**
- ⚠️ Declining cash balances
- ⚠️ Expenses exceeding income
- ⚠️ Liabilities approaching assets
- ⚠️ Negative trends in income chart

---

## 📊 Chart of Accounts

### What is Chart of Accounts?
The Chart of Accounts is a complete listing of all accounts used in your accounting system, organized hierarchically.

### Default Account Structure

```
📂 Assets (AST)
├── 📂 Current Assets (CA)
│   ├── Cash in Hand
│   ├── Bank Accounts
│   ├── Accounts Receivable
│   └── Inventory
├── 📂 Fixed Assets (FA)
│   ├── Land
│   ├── Buildings
│   ├── Machinery
│   └── Vehicles
└── 📂 Investments (INV)
    └── Long-term Investments

📂 Liabilities (LIA)
├── 📂 Current Liabilities (CL)
│   ├── Accounts Payable
│   ├── Short-term Loans
│   └── Tax Payable
├── 📂 Long Term Liabilities (LTL)
│   └── Long-term Loans
└── 📂 Capital Account (CAP)
    ├── Owner's Capital
    └── Retained Earnings

📂 Income (INC)
├── 📂 Direct Income (DI)
│   ├── Sales Revenue
│   └── Service Revenue
└── 📂 Indirect Income (II)
    ├── Interest Income
    └── Other Income

📂 Expenses (EXP)
├── 📂 Direct Expenses (DE)
│   ├── Cost of Goods Sold
│   └── Purchase Expenses
└── 📂 Indirect Expenses (IE)
    ├── Rent Expense
    ├── Salary Expense
    ├── Utility Expense
    └── Office Supplies
```

### Managing Account Groups

#### Creating a New Account Group

**Step 1: Access Chart of Accounts**
**Navigation**: Accounting → Chart of Accounts

**Step 2: Click "Add Group"**
Located in the top-right corner of the page.

**Step 3: Fill Group Details**
```
Required Fields:
├── Parent Group: Select parent (e.g., "Current Assets")
├── Group Name: "Petty Cash" (descriptive name)
├── Group Code: "PC" (2-4 character code)
└── Affects Gross Profit/Loss: [✓] Yes / [ ] No
```

**Field Explanations:**
- **Parent Group**: Where this group belongs in the hierarchy
- **Group Name**: Display name of the group
- **Group Code**: Short code for easy reference
- **Affects Gross Profit/Loss**: 
  - ✅ Check if it's a direct income/expense (COGS, Sales)
  - ❌ Uncheck if it's an indirect income/expense (Rent, Interest)

**Step 4: Save Group**
Click **"Submit"** to create the group.

#### Editing an Account Group

1. Find the group in the Chart of Accounts list
2. Click the **"Edit"** button (pencil icon)
3. Modify the necessary fields
4. Click **"Update"** to save changes

⚠️ **Warning**: Editing a group affects all its child groups and ledgers!

#### Deleting an Account Group

1. Find the group in the list
2. Click the **"Delete"** button (trash icon)
3. Confirm deletion

⚠️ **Important**: You cannot delete a group that:
- Contains ledgers
- Has child groups
- Has transactions recorded

**Solution**: Move all ledgers and child groups first, then delete.

### Managing Ledgers

#### Creating a New Ledger

**Step 1: Click "Add Ledger"**
Located in the top-right corner, next to "Add Group".

**Step 2: Fill Ledger Details**

```
┌─────────────────────────────────────────┐
│ Add Ledger                              │
├─────────────────────────────────────────┤
│ Group: [Select Account Group ▼]        │
│ Ledger Name: "ABC Supplier"            │
│ Ledger Code: "ABC001"                  │
│                                         │
│ Opening Balance: Rs. 50,000.00         │
│ Type: [○] Debit  [●] Credit            │
│ Date: 01-04-2025                        │
│                                         │
│ [✓] Bank or Cash Account               │
│ [✓] Enable Reconciliation              │
│                                         │
│ Notes:                                  │
│ ┌───────────────────────────────────┐  │
│ │ Main supplier for raw materials  │  │
│ └───────────────────────────────────┘  │
│                                         │
│ [Cancel]              [Submit]          │
└─────────────────────────────────────────┘
```

**Required Fields:**
- **Group**: Parent group (e.g., "Current Assets")
- **Ledger Name**: Full name of the account
- **Ledger Code**: Unique identifier (e.g., "CASH001")

**Optional Fields:**
- **Opening Balance**: Starting balance (if any)
- **Type**: Dr (for assets/expenses) or Cr (for liabilities/income)
- **Date**: Opening balance date
- **Bank or Cash Account**: Check if this is a bank/cash account
- **Reconciliation**: Enable for bank accounts that need reconciliation
- **Notes**: Additional information

**Helpful Notes in the Form:**

**Opening Balance:**
> If you don't have opening balance, leave it as 0.00. Opening balance is used when you are migrating from a previous accounting system.

**Bank or Cash Account:**
> Check this if this ledger account is a bank or a cash account. This is used in Receipt (RV) and Payment (PV) vouchers.

**Reconciliation:**
> Check this if this ledger needs to be reconciled. Generally this is used for bank accounts only.

**Step 3: Save Ledger**
Click **"Submit"** to create the ledger.

#### Common Ledgers to Create

**Bank Accounts:**
```
Name: "Bank - Commercial Bank"
Code: "BANK001"
Group: Current Assets
Type: Bank Account ✓
Reconciliation: Enabled ✓
```

**Cash Accounts:**
```
Name: "Cash in Hand"
Code: "CASH001"
Group: Current Assets
Type: Cash Account ✓
```

**Supplier Accounts:**
```
Name: "ABC Supplier Ltd."
Code: "SUP001"
Group: Current Liabilities → Accounts Payable
Opening Balance: Rs. 50,000 Cr (if you owe money)
```

**Customer Accounts:**
```
Name: "XYZ Customer Pvt Ltd"
Code: "CUST001"
Group: Current Assets → Accounts Receivable
Opening Balance: Rs. 75,000 Dr (if they owe you)
```

**Expense Accounts:**
```
Name: "Rent Expense"
Code: "EXP001"
Group: Indirect Expenses
```

**Income Accounts:**
```
Name: "Sales Revenue"
Code: "INC001"
Group: Direct Income
Affects Gross: Yes ✓
```

#### Editing a Ledger

1. Find the ledger in the Chart of Accounts
2. Click **"Edit"** (pencil icon)
3. **Modify fields** as needed
4. Click **"Update Ledger"** to save

⚠️ **Note**: Opening balance should only be changed if it was entered incorrectly initially!

#### Viewing Ledger Balances

The Chart of Accounts displays:
- **O/P Balance (Rs)** - Opening balance at start of year
- **C/L Balance (Rs)** - Closing balance (current)
- **Dr** or **Cr** indicator for balance type

**Example Display:**
```
┌──────────────────────┬──────────┬─────────────┬─────────────┐
│ Account Name         │ Type     │ O/P Balance │ C/L Balance │
├──────────────────────┼──────────┼─────────────┼─────────────┤
│ Cash in Hand         │ Ledger   │ Dr 50,000   │ Dr 45,000   │
│ Bank - Commercial    │ Ledger   │ Dr 100,000  │ Dr 250,000  │
│ ABC Supplier         │ Ledger   │ Cr 25,000   │ Cr 30,000   │
└──────────────────────┴──────────┴─────────────┴─────────────┘
```

### Best Practices for Chart of Accounts

#### ✅ Do's:
1. **Use descriptive names** - "Office Rent Expense" instead of just "Rent"
2. **Follow naming conventions** - Consistent format across all accounts
3. **Use meaningful codes** - "BANK001", "SUP001" instead of random numbers
4. **Keep hierarchy logical** - Group related accounts together
5. **Document special accounts** - Use notes field for complex accounts
6. **Regular review** - Check for unused or duplicate accounts

#### ❌ Don'ts:
1. **Don't create duplicate accounts** - Check existing accounts first
2. **Don't use generic names** - Avoid "Miscellaneous" or "Other"
3. **Don't delete accounts with history** - Mark as inactive instead
4. **Don't change codes** - Keep codes stable for consistency
5. **Don't overcomplicate** - Keep structure simple and maintainable

---

## 📝 Managing Entries (Vouchers)

### What are Entries?
Entries (also called vouchers) are the records of financial transactions. Each entry contains:
- **Header**: Date, entry type, number, tag, narration
- **Line Items**: Multiple debit and credit entries
- **Balance**: Total debits must equal total credits

### Accessing Entries
**Navigation**: Accounting → Entries

### Entry List View

The entries page shows all recorded transactions:

```
┌─────────────────────────────────────────────────────────────────────┐
│ Entries                                        [Add Entry ▼]         │
├─────────────────────────────────────────────────────────────────────┤
│ 🔍 Search: [         ]  Type: [All ▼]  Tag: [All ▼]               │
│    From: [01-04-2025]  To: [31-03-2026]  [✓] Show All              │
├──────┬───────────┬──────┬─────┬───────────┬────────┬────────┬──────┤
│ Date │ Number    │ Ledg │ Type│ Tag       │ Debit  │ Credit │ Act  │
├──────┼───────────┼──────┼─────┼───────────┼────────┼────────┼──────┤
│ 10th │ JV0001    │ Cash │ JV  │ Expenses  │ 50,000 │        │ ⋮    │
│ Apr  │           │ Rent │     │           │        │ 50,000 │      │
├──────┼───────────┼──────┼─────┼───────────┼────────┼────────┼──────┤
│ 12th │ RV0001    │ Bank │ RV  │ Sales     │ 100,00 │        │ ⋮    │
│ Apr  │           │ Sale │     │           │        │ 100,00 │      │
└──────┴───────────┴──────┴─────┴───────────┴────────┴────────┴──────┘
Total Debit: Rs. 150,000.00    Total Credit: Rs. 150,000.00
```

### Filter Options

**Search Bar**: Find entries by entry number or narration
**Entry Type Filter**: JV, RV, PV, CV, INV, PINV, or All
**Tag Filter**: Filter by tags (if assigned)
**Date Range**: From and To date pickers
**Show All**: Check to include reconciled and older entries

### Creating a New Entry

#### Step 1: Click "Add Entry"
In the top-right corner, click the dropdown **"Add Entry"**.

#### Step 2: Select Entry Type
Choose from the dropdown:
- **Journal Voucher (JV)** - General entries
- **Receipt Voucher (RV)** - Cash/bank receipts
- **Payment Voucher (PV)** - Cash/bank payments
- **Contra Voucher (CV)** - Cash/bank transfers
- **Sales Voucher (INV)** - Sales transactions
- **Purchase Voucher (PINV)** - Purchase transactions

#### Step 3: Fill Entry Form

```
┌───────────────────────────────────────────────────────────┐
│ Add Journal Voucher (JV)                                  │
├───────────────────────────────────────────────────────────┤
│ Entry Number: JV0005 (Auto-generated)                    │
│ Entry Date: [15-04-2025]                                  │
│ Tag: [Select Tag ▼] (Optional)                           │
│                                                            │
│ Entry Items:                                              │
│ ┌─────────────────────┬──────────┬────────────┬─────────┐ │
│ │ Ledger              │ Dr/Cr    │ Amount     │ Remove  │ │
│ ├─────────────────────┼──────────┼────────────┼─────────┤ │
│ │ [Rent Expense ▼]    │ [Dr ▼]   │ 50,000.00  │ [  X  ] │ │
│ │ [Cash in Hand ▼]    │ [Cr ▼]   │ 50,000.00  │ [  X  ] │ │
│ └─────────────────────┴──────────┴────────────┴─────────┘ │
│ [+ Add Item]                                              │
│                                                            │
│ Total Debit: Rs. 50,000.00   Total Credit: Rs. 50,000.00 │
│ Difference: Rs. 0.00 ✓                                   │
│                                                            │
│ Narration:                                                │
│ ┌─────────────────────────────────────────────────────┐  │
│ │ Paid office rent for April 2025                     │  │
│ └─────────────────────────────────────────────────────┘  │
│                                                            │
│ [Cancel]                            [Submit Entry]         │
└───────────────────────────────────────────────────────────┘
```

**Required Fields:**
- **Entry Date**: Transaction date (defaults to today)
- **Ledger**: Select from chart of accounts
- **Dr/Cr**: Debit or Credit
- **Amount**: Transaction amount
- **Narration**: Description of transaction

**Important:**
- ✅ **Total Debit must equal Total Credit**
- ✅ **At least 2 line items required**
- ✅ **All amounts must be positive numbers**

#### Step 4: Add Multiple Line Items

Click **"+ Add Item"** to add more ledgers to the entry.

**Example: Sales Entry with Multiple Items**
```
Date: 15-Apr-2025
Type: Sales Voucher (INV)

Line Items:
1. Bank Account           Dr    Rs. 100,000  (Money received)
2. Sales Revenue          Cr    Rs.  85,000  (Income earned)
3. VAT Payable            Cr    Rs.  15,000  (Tax collected)
                        ─────  ───────────
Total:                  Dr/Cr  Rs. 100,000  ✓ Balanced

Narration: Sale of goods to XYZ Customer, Invoice #INV-001
```

#### Step 5: Save Entry

Click **"Submit Entry"** to record the transaction.

**Validation Checks:**
- ✅ Entry must be balanced (Dr = Cr)
- ✅ All fields must be filled
- ✅ Amount must be greater than 0
- ✅ No duplicate ledgers in same Dr/Cr side

### Editing an Entry

1. Find the entry in the entries list
2. Click the **actions menu** (⋮) → **"Edit"**
3. Modify the fields
4. Click **"Update Entry"**

⚠️ **Note**: Editing entries affects ledger balances immediately!

### Deleting an Entry

1. Find the entry in the entries list
2. Click **actions menu** (⋮) → **"Delete"**
3. Confirm deletion

⚠️ **Warning**: 
- Deletion is permanent!
- Affects all ledger balances
- Cannot be undone
- Consider adding a reversing entry instead

### Viewing Entry Details

Click on any entry row to view:
- Full entry details
- All line items
- Entry history
- User who created it
- Creation and modification dates

---

## 📋 Entry Types

### What are Entry Types?
Entry types are templates that define voucher numbering, naming, and behavior. Each type has unique settings for auto-numbering.

### Default Entry Types

| Code | Name | Prefix | Description | Use Case |
|------|------|--------|-------------|----------|
| **JV** | Journal Voucher | JV | General accounting entries | Adjustments, depreciation, provisions |
| **RV** | Receipt Voucher | RV | Money received | Customer payments, income receipts |
| **PV** | Payment Voucher | PV | Money paid out | Supplier payments, expense payments |
| **CV** | Contra Voucher | CV | Internal transfers | Bank to cash, cash to bank |
| **INV** | Sales Voucher | INV | Sales transactions | Sales invoices |
| **PINV** | Purchase Voucher | PINV | Purchase transactions | Purchase invoices |

### Auto-Numbering System

Each entry type has:
- **Prefix**: Text before number (e.g., "JV")
- **Suffix**: Text after number (e.g., "-2025")
- **Zero Padding**: Number of digits (e.g., "0001")

**Example Entry Numbers:**
```
JV0001, JV0002, JV0003...
RV-2025-0001, RV-2025-0002...
INV/2025/0001, INV/2025/0002...
```

### Managing Entry Types

**Navigation**: Accounting → Entry Types

#### Viewing Entry Types

The entry types page shows:
- Type name and code
- Current numbering format
- Last used number
- Active/inactive status

#### Editing Entry Type Settings

**Step 1: Click "Edit"**
Find the entry type and click the edit button.

**Step 2: Modify Settings**

```
┌─────────────────────────────────────┐
│ Edit Entry Type: Journal Voucher    │
├─────────────────────────────────────┤
│ Name: Journal Voucher               │
│ Code: JV                            │
│                                      │
│ Numbering:                          │
│ Prefix: "JV"                        │
│ Suffix: ""                          │
│ Zero Padding: 4 (digits)            │
│                                      │
│ Next Number: 0023                   │
│                                      │
│ Status: [✓] Active                  │
│                                      │
│ [Cancel]         [Update]           │
└─────────────────────────────────────┘
```

**Step 3: Save Changes**
Click **"Update"** to apply the new settings.

⚠️ **Warning**: Changing numbering affects future entries, not existing ones!

### Best Practices for Entry Types

#### ✅ Do's:
1. **Keep prefixes short** - 2-4 characters (JV, RV, PV)
2. **Use consistent format** - Same pattern across all types
3. **Include year in suffix** - For yearly reset (e.g., "-2025")
4. **Set adequate padding** - 4 digits handles 9,999 entries
5. **Document changes** - Note why numbering was changed

#### ❌ Don'ts:
1. **Don't change existing numbers** - Breaks audit trail
2. **Don't use special characters** - Stick to letters, numbers, hyphens
3. **Don't duplicate codes** - Each type must have unique code
4. **Don't deactivate used types** - Marks as inactive, not delete

---

## 🔍 Advanced Search

### What is Advanced Search?
The search feature allows you to find specific entries using multiple filter criteria, making it easy to locate transactions across your entire accounting history.

### Accessing Search
**Navigation**: Accounting → Search

### Search Form

```
┌──────────────────────────────────────────────────────────────┐
│ Advanced Entry Search                                        │
├──────────────────────────────────────────────────────────────┤
│ Ledgers:           [All Ledgers ▼] (Multi-select)           │
│ Entry Types:       [All Types ▼] (Multi-select)             │
│                                                               │
│ Entry Number:      [Equal to ▼]  [      ]                   │
│                                                               │
│ Amount:            [Any ▼]  [Equal to ▼]  [      ]          │
│                                                               │
│ Date Range:        From: [01-04-2025]  To: [31-03-2026]     │
│                                                               │
│ Tags:              [All Tags ▼] (Multi-select)              │
│                                                               │
│ Narration:         [                                    ]    │
│                                                               │
│ [Clear]                                   [Search]           │
└──────────────────────────────────────────────────────────────┘
```

### Search Criteria

#### 1. Ledger Filter
Select one or multiple ledgers:
- **All Ledgers** - Search all accounts
- **Specific Ledgers** - Select from dropdown
- **Multiple Selection** - Hold Ctrl/Cmd to select multiple

**Use Case**: Find all entries involving "Cash in Hand"

#### 2. Entry Type Filter
Filter by voucher type:
- **All Types** - Include all entry types
- **Specific Types** - JV, RV, PV, CV, INV, PINV
- **Multiple Selection** - Can select multiple types

**Use Case**: Find all Receipt Vouchers (RV) for a specific period

#### 3. Entry Number Search
Find entries by number with operators:
- **Equal to** - Exact match (e.g., JV0001)
- **Less than or equal** - Up to number (e.g., ≤ JV0050)
- **Greater than or equal** - From number onwards (e.g., ≥ JV0100)
- **In between** - Range of numbers (e.g., JV0010 to JV0020)

**Use Case**: Find entries JV0050 to JV0100

#### 4. Amount Filter
Search by transaction amount:

**Dr/Cr Filter:**
- **Any** - Both debit and credit
- **Debit** - Only debit entries
- **Credit** - Only credit entries

**Amount Operator:**
- **Equal to** - Exact amount
- **Less than or equal** - Up to amount
- **Greater than or equal** - From amount onwards
- **In between** - Amount range

**Use Case**: Find all payments (Credit) greater than Rs. 50,000

#### 5. Date Range
Filter by transaction date:
- **From Date** - Start date
- **To Date** - End date
- Leave blank for all dates

**Use Case**: Find all entries in April 2025

#### 6. Tag Filter
Filter by transaction tags:
- **All Tags** - Include all tags
- **Specific Tags** - Select from dropdown
- **Multiple Selection** - Can select multiple

**Use Case**: Find all entries tagged as "Salary"

#### 7. Narration Search
Free text search in entry descriptions:
- Searches within narration field
- Partial match supported
- Case-insensitive

**Use Case**: Find all entries mentioning "rent"

### Search Results

Results are displayed in a table:

```
┌──────┬────────┬───────────┬──────┬─────┬────────┬────────┬──────┐
│ Date │ Number │ Ledger    │ Type │ Tag │ Debit  │ Credit │ Act. │
├──────┼────────┼───────────┼──────┼─────┼────────┼────────┼──────┤
│ 10th │ JV0001 │ Rent Exp. │ JV   │ Exp │ 50,000 │        │ View │
│ Apr  │        │ Cash      │      │     │        │ 50,000 │      │
├──────┼────────┼───────────┼──────┼─────┼────────┼────────┼──────┤
│ 15th │ JV0002 │ Salary    │ JV   │ Exp │ 75,000 │        │ View │
│ Apr  │        │ Bank      │      │     │        │ 75,000 │      │
└──────┴────────┴───────────┴──────┴─────┴────────┴────────┴──────┘

Showing 1 to 2 of 2 results
```

### Common Search Scenarios

#### Scenario 1: Find All Rent Payments
```
Ledgers: [Rent Expense]
Entry Types: [All]
Date Range: [Last Financial Year]
Amount: [Any]
Narration: "rent"
```

#### Scenario 2: Find Large Transactions
```
Ledgers: [All]
Entry Types: [All]
Amount: [Greater than or equal] [100,000]
Date Range: [Current Month]
```

#### Scenario 3: Find Untagged Entries
```
Ledgers: [All]
Entry Types: [All]
Tags: [Untagged]
Date Range: [All]
```

#### Scenario 4: Audit Specific Account
```
Ledgers: [Cash in Hand]
Entry Types: [Payment Voucher, Receipt Voucher]
Date Range: [01-04-2025 to 30-04-2025]
```

### Exporting Search Results

Click **"Export"** buttons to download results:
- **CSV** - Comma-separated values (for Excel)
- **XLS** - Excel format
- **PDF** - Printable format

---

## 📊 Financial Reports

### Report Overview

The system provides 6 comprehensive financial reports:

| Report | Purpose | Frequency | Key Users |
|--------|---------|-----------|-----------|
| **Balance Sheet** | Financial position | Monthly/Quarterly | Management, Investors |
| **Profit & Loss** | Business performance | Monthly | Management, Accountants |
| **Trial Balance** | Account verification | Monthly | Accountants |
| **Ledger Statement** | Account transactions | As needed | All users |
| **Ledger Entries** | Transaction listing | As needed | All users |
| **Reconciliation** | Bank matching | Monthly | Accountants |

### 1. Balance Sheet Report

**What it shows**: Snapshot of financial position at a specific date
**Formula**: Assets = Liabilities + Owner's Equity

#### Accessing Balance Sheet
**Navigation**: Accounting → Reports → Balance Sheet

#### Report Options

```
┌────────────────────────────────────────┐
│ Balance Sheet Report Options           │
├────────────────────────────────────────┤
│ [✓] Show Opening Balance Sheet         │
│ [ ] Show Closing Balance Sheet         │
│                                         │
│ From Date: [01-04-2025]                │
│ To Date:   [30-04-2025]                │
│                                         │
│ [Generate Report]                      │
└────────────────────────────────────────┘
```

**Opening Balance Sheet**: Shows balances at start of period
**Closing Balance Sheet**: Shows balances at end of period (default)

#### Reading the Balance Sheet

```
════════════════════════════════════════════════════════════
                 COMPANY NAME
              BALANCE SHEET
        As at 30th April, 2025
════════════════════════════════════════════════════════════

ASSETS                              LIABILITIES
────────────────────────────────   ────────────────────────
Current Assets                      Current Liabilities
  Cash in Hand      Dr   50,000      Accounts Payable Cr 75,000
  Bank Account      Dr  250,000      Tax Payable      Cr 15,000
  Inventory         Dr  100,000      
                    ──────────                        ──────────
Total Current          400,000     Total Current         90,000

Fixed Assets                        Long-term Liabilities
  Land              Dr  500,000      Bank Loan        Cr 200,000
  Building          Dr  300,000                       ──────────
  Machinery         Dr  150,000     Total Long-term      200,000
                    ──────────      
Total Fixed            950,000     Owner's Equity
                                     Capital          Cr 800,000
                                     Profit/Loss      Cr 260,000
                                                      ──────────
                                   Total Equity       1,060,000

────────────────────────────────   ────────────────────────
TOTAL ASSETS        1,350,000     TOTAL LIABILITIES  1,350,000
════════════════════════════════════════════════════════════
                    ✓ Balanced
```

#### Key Indicators

**Healthy Balance Sheet:**
- ✅ Assets > Liabilities (Positive equity)
- ✅ Current Assets > Current Liabilities (Good liquidity)
- ✅ Reasonable debt-to-equity ratio
- ✅ Sheet is balanced (always should be!)

**Warning Signs:**
- ⚠️ Current Liabilities > Current Assets (Liquidity crisis)
- ⚠️ Negative owner's equity (Insolvent)
- ⚠️ Large accumulated losses
- ⚠️ Excessive debt

### 2. Profit & Loss Report

**What it shows**: Business profitability over a period
**Formula**: Net Profit = Income - Expenses

#### Accessing Profit & Loss
**Navigation**: Accounting → Reports → Profit & Loss

#### Report Options

```
┌────────────────────────────────────────┐
│ Profit & Loss Report Options           │
├────────────────────────────────────────┤
│ [✓] Show Opening P&L Statement         │
│ [ ] Show Closing P&L Statement         │
│                                         │
│ From Date: [01-04-2025]                │
│ To Date:   [30-04-2025]                │
│                                         │
│ [Generate Report]                      │
└────────────────────────────────────────┘
```

#### Reading the P&L Statement

```
════════════════════════════════════════════════════════════
                 COMPANY NAME
            PROFIT & LOSS STATEMENT
        For the period 01-Apr-2025 to 30-Apr-2025
════════════════════════════════════════════════════════════

EXPENSES                            INCOME
────────────────────────────────   ────────────────────────
Gross Expenses (Direct)             Gross Income (Direct)
  Cost of Goods Sold    400,000       Sales Revenue     850,000
  Purchase Expense       50,000       Service Income     50,000
                     ──────────                       ──────────
Total Gross Expenses     450,000    Total Gross Income   900,000

                                    Gross Profit         450,000

Net Expenses (Indirect)             Net Income (Indirect)
  Rent Expense           50,000       Interest Income     5,000
  Salary Expense         75,000       Other Income        2,000
  Utility Expense        10,000                        ──────────
  Office Supplies         5,000     Total Net Income      7,000
  Depreciation           10,000
                     ──────────      
Total Net Expenses       150,000    

────────────────────────────────   ────────────────────────
TOTAL EXPENSES           600,000    TOTAL INCOME        907,000

════════════════════════════════════════════════════════════
                   NET PROFIT: Rs. 307,000
════════════════════════════════════════════════════════════
```

#### Key Metrics

**Profitability Ratios:**
- **Gross Profit Margin** = (Gross Profit / Sales) × 100
  - Example: (450,000 / 850,000) × 100 = 52.9%
  
- **Net Profit Margin** = (Net Profit / Sales) × 100
  - Example: (307,000 / 850,000) × 100 = 36.1%

**Healthy P&L:**
- ✅ Positive gross profit (Sales > COGS)
- ✅ Positive net profit (Income > Total Expenses)
- ✅ Gross margin > 30% (industry dependent)
- ✅ Consistent or growing profitability

**Warning Signs:**
- ⚠️ Negative gross profit (Selling below cost)
- ⚠️ Net loss (Expenses exceed income)
- ⚠️ Declining profit margins
- ⚠️ Expenses growing faster than income

### 3. Trial Balance Report

**What it shows**: List of all accounts with their balances
**Purpose**: Verify that debits equal credits (accounting accuracy)

#### Accessing Trial Balance
**Navigation**: Accounting → Reports → Trial Balance

#### Report Options

```
┌────────────────────────────────────────┐
│ Trial Balance Report Options           │
├────────────────────────────────────────┤
│ From Date: [01-04-2025]                │
│ To Date:   [30-04-2025]                │
│                                         │
│ [Generate Report]                      │
└────────────────────────────────────────┘
```

#### Reading the Trial Balance

```
════════════════════════════════════════════════════════════════════════
                         COMPANY NAME
                       TRIAL BALANCE
              For the period 01-Apr-2025 to 30-Apr-2025
════════════════════════════════════════════════════════════════════════

Account Name          Type    O/P Balance  Debit Total  Credit Total  C/L Balance
─────────────────────────────────────────────────────────────────────────────────
Assets
  Current Assets
    Cash in Hand      Ledger  Dr  50,000    100,000      50,000      Dr  100,000
    Bank Account      Ledger  Dr 100,000    200,000      50,000      Dr  250,000
    Inventory         Ledger  Dr  75,000     50,000      25,000      Dr  100,000
  Fixed Assets
    Land              Ledger  Dr 500,000          0           0      Dr  500,000
    Buildings         Ledger  Dr 300,000          0           0      Dr  300,000

Liabilities
  Current Liabilities
    Accounts Payable  Ledger  Cr  50,000     25,000     100,000      Cr  125,000
    Tax Payable       Ledger  Cr  10,000          0       5,000      Cr   15,000

Income
  Direct Income
    Sales Revenue     Ledger  Cr       0          0     850,000      Cr  850,000

Expenses
  Direct Expenses
    COGS              Ledger  Dr       0    400,000           0      Dr  400,000
  Indirect Expenses
    Rent Expense      Ledger  Dr       0     50,000           0      Dr   50,000
    Salary Expense    Ledger  Dr       0     75,000           0      Dr   75,000

─────────────────────────────────────────────────────────────────────────────────
TOTAL                            1,085,000  1,900,000   1,900,000     1,085,000
═════════════════════════════════════════════════════════════════════════════════
                               ✓ Trial Balance is Balanced
```

#### Interpreting Results

**Balanced Trial Balance** (✓):
- Total Debits = Total Credits
- Accounts are correctly recorded
- System is mathematically accurate

**Unbalanced Trial Balance** (✗):
- **Error present** - Investigation needed
- Check for:
  - Incorrectly entered amounts
  - Missing journal entries
  - Wrong Dr/Cr classification
  - Data entry mistakes

**Note**: A balanced trial balance doesn't guarantee zero errors. It only confirms mathematical accuracy, not correct account classification.

### 4. Ledger Statement Report

**What it shows**: Detailed transaction history for a specific ledger with running balance
**Purpose**: Track all movements in a particular account

#### Accessing Ledger Statement
**Navigation**: Accounting → Reports → Ledger Statement

#### Report Options

```
┌────────────────────────────────────────┐
│ Ledger Statement Options               │
├────────────────────────────────────────┤
│ Select Ledger: [Cash in Hand ▼]       │
│                                         │
│ From Date: [01-04-2025]                │
│ To Date:   [30-04-2025]                │
│                                         │
│ [Generate Report]                      │
└────────────────────────────────────────┘
```

#### Reading the Statement

```
════════════════════════════════════════════════════════════════════
                      COMPANY NAME
                   LEDGER STATEMENT
         Ledger: Cash in Hand (CASH001)
      Period: 01-Apr-2025 to 30-Apr-2025
════════════════════════════════════════════════════════════════════

Opening Balance: Dr Rs. 50,000.00

────────────────────────────────────────────────────────────────────
Date    Number  Ledger          Type Tag  Debit    Credit   Balance
────────────────────────────────────────────────────────────────────
        Current Opening Balance                            50,000 Dr

01-Apr  JV0001  Rent Expense    JV   Exp           50,000  0 Dr
05-Apr  RV0001  Sales Revenue   RV   Inc  100,000          100,000 Dr
10-Apr  PV0001  Salary Expense  PV   Exp           75,000  25,000 Dr
15-Apr  RV0002  Customer Pay    RV   Inc   50,000          75,000 Dr
20-Apr  PV0002  Supplier Pay    PV   Exp           25,000  50,000 Dr

────────────────────────────────────────────────────────────────────
        Current Closing Balance                            50,000 Dr
════════════════════════════════════════════════════════════════════

Closing Balance: Dr Rs. 50,000.00
Total Debit: Rs. 150,000.00
Total Credit: Rs. 150,000.00
Total Entries: 5
```

#### Use Cases

1. **Bank Reconciliation** - Match with bank statement
2. **Customer Account Review** - Check payment history
3. **Expense Analysis** - Track specific expense categories
4. **Audit Trail** - Detailed transaction verification

### 5. Ledger Entries Report

**What it shows**: List of all transactions for a ledger (without running balance)
**Purpose**: Quick overview of ledger activity

#### Accessing Ledger Entries
**Navigation**: Accounting → Reports → Ledger Entries

#### Key Difference from Ledger Statement

| Feature | Ledger Statement | Ledger Entries |
|---------|------------------|----------------|
| **Running Balance** | ✅ Yes | ❌ No |
| **Sort Order** | Date ascending | Date descending |
| **Opening/Closing** | ✅ Shown | ✅ Shown |
| **Use Case** | Reconciliation | Quick review |

### 6. Reconciliation Report

**What it shows**: Unreconciled transactions for bank/cash accounts
**Purpose**: Match accounting records with bank statements

#### Accessing Reconciliation
**Navigation**: Accounting → Reports → Reconciliation

#### Report Options

```
┌────────────────────────────────────────┐
│ Reconciliation Options                 │
├────────────────────────────────────────┤
│ Select Ledger: [Bank - Commercial ▼]  │
│ (Only bank/cash accounts shown)        │
│                                         │
│ From Date: [01-04-2025]                │
│ To Date:   [30-04-2025]                │
│                                         │
│ [✓] Show All Entries                   │
│ [ ] Show Unreconciled Only             │
│                                         │
│ [Generate Report]                      │
└────────────────────────────────────────┘
```

#### Reconciliation Process

**Step 1: Generate Report**
Select bank account and date range.

**Step 2: Match Transactions**
Compare with bank statement:

```
════════════════════════════════════════════════════════════════════
                Bank - Commercial (BANK001)
                  RECONCILIATION REPORT
              Period: 01-Apr-2025 to 30-Apr-2025
════════════════════════════════════════════════════════════════════

Opening Balance: Dr Rs. 100,000.00
Closing Balance: Dr Rs. 250,000.00
Unreconciled Debit: Rs. 25,000.00
Unreconciled Credit: Rs. 10,000.00

────────────────────────────────────────────────────────────────────
Date    Number  Ledger      Type  Debit    Credit   Reconcile Date
────────────────────────────────────────────────────────────────────
01-Apr  RV0001  Customer A  RV    100,000           [05-Apr-2025]
05-Apr  PV0001  Supplier B  PV             50,000   [08-Apr-2025]
10-Apr  RV0002  Customer C  RV     25,000           [          ]  ⚠️
15-Apr  PV0002  Utility Co  PV             10,000   [          ]  ⚠️
────────────────────────────────────────────────────────────────────

⚠️ 2 Unreconciled Transactions
```

**Step 3: Enter Reconciliation Dates**
- Check your bank statement
- Find matching transactions
- Enter the **date shown on bank statement** in "Reconcile Date" field

**Step 4: Submit Reconciliation**
Click **"Reconcile"** button to save reconciliation dates.

#### Tips for Successful Reconciliation

**✅ Best Practices:**
1. **Reconcile monthly** - Don't let backlog build up
2. **Match dates carefully** - Use bank statement dates
3. **Investigate discrepancies** - Find missing/duplicate entries
4. **Document differences** - Note outstanding cheques, deposits in transit
5. **Keep records** - Save bank statements for reference

**Common Discrepancies:**
- **Outstanding Cheques** - Issued but not yet cleared
- **Deposits in Transit** - Deposited but not yet credited
- **Bank Charges** - Not yet recorded in books
- **Interest Earned** - Not yet recorded in books
- **Errors** - Data entry mistakes

#### Recording Bank Charges

If you find unrecorded bank charges:

```
Entry Type: Payment Voucher (PV)
Date: [As per bank statement]

Line Items:
1. Bank Charges Expense    Dr    Rs. 500
2. Bank Account            Cr    Rs. 500

Narration: Bank charges for April 2025 as per statement
```

---

## 🏦 Bank Reconciliation

### Step-by-Step Reconciliation Process

#### Step 1: Gather Documents
Collect:
- [ ] Physical bank statement (or online statement)
- [ ] List of outstanding cheques
- [ ] List of deposits in transit
- [ ] Previous reconciliation statement

#### Step 2: Run Ledger Statement
**Navigation**: Reports → Ledger Statement
- Select bank account
- Enter date range matching bank statement
- Generate report

#### Step 3: Compare Opening Balances

```
Your Books: Dr Rs. 100,000
Bank Statement: Rs. 100,000
Difference: Rs. 0 ✓
```

If different, investigate previous period.

#### Step 4: Tick Off Matching Transactions

Create a checklist:

```
✓ 01-Apr RV0001 Deposit    Rs. 100,000  (Cleared 02-Apr)
✓ 05-Apr PV0001 Cheque #001 Rs.  50,000  (Cleared 08-Apr)
⚠️ 10-Apr RV0002 Deposit    Rs.  25,000  (Not on statement)
✓ 15-Apr PV0002 Cheque #002 Rs.  10,000  (Cleared 18-Apr)
```

#### Step 5: Identify Discrepancies

**Outstanding Items:**
```
Deposits in Transit:
- 10-Apr RV0002  Rs. 25,000  (Will clear next month)

Outstanding Cheques:
- None

Bank Items Not in Books:
- Bank Charges  Rs. 500
- Interest Earned  Rs. 1,200
```

#### Step 6: Record Missing Transactions

**Bank Charges:**
```
PV Entry:
Dr Bank Charges Expense  Rs. 500
Cr Bank Account         Rs. 500
```

**Interest Earned:**
```
RV Entry:
Dr Bank Account        Rs. 1,200
Cr Interest Income     Rs. 1,200
```

#### Step 7: Prepare Reconciliation Statement

```
════════════════════════════════════════════════════════════
          BANK RECONCILIATION STATEMENT
          Bank - Commercial (BANK001)
              As at 30-Apr-2025
════════════════════════════════════════════════════════════

Balance as per Bank Statement              Rs. 176,200

Add: Deposits in Transit
  10-Apr RV0002                             Rs.  25,000
                                           ──────────
                                            Rs. 201,200

Less: Outstanding Cheques
  None                                      Rs.       0
                                           ──────────
Adjusted Bank Balance                      Rs. 201,200

════════════════════════════════════════════════════════════

Balance as per Our Books                   Rs. 175,500

Add: Interest Earned (not recorded)        Rs.   1,200
                                           ──────────
                                            Rs. 176,700

Less: Bank Charges (not recorded)          Rs.     500
                                           ──────────
Adjusted Book Balance                      Rs. 176,200

════════════════════════════════════════════════════════════
After recording missing transactions:
Book Balance                               Rs. 176,200
Bank Balance (adjusted)                    Rs. 176,200
Difference                                 Rs.       0 ✓
════════════════════════════════════════════════════════════
                    ✓ Reconciled
```

#### Step 8: Mark as Reconciled

In the system:
1. Go to **Reports → Reconciliation**
2. Select bank account
3. Enter reconciliation dates for cleared items
4. Click **"Reconcile"**

---

## 💱 Multi-Currency Management

### Overview
The system supports transactions in multiple currencies with automatic conversion to base currency.

### Default Currencies

| Code | Name | Symbol | Base |
|------|------|--------|------|
| **LKR** | Sri Lankan Rupee | Rs. | ✅ Yes |
| **USD** | US Dollar | $ | ❌ No |
| **EUR** | Euro | € | ❌ No |
| **GBP** | British Pound | £ | ❌ No |
| **INR** | Indian Rupee | ₹ | ❌ No |

### Managing Currencies

**Navigation**: Accounting → Currencies

#### Adding a New Currency

**Step 1: Click "Add Currency"**

**Step 2: Fill Currency Details**

```
┌─────────────────────────────────────┐
│ Add Currency                        │
├─────────────────────────────────────┤
│ Currency Name: Japanese Yen         │
│ Currency Code: JPY                  │
│ Symbol: ¥                           │
│                                      │
│ Decimal Places: 0                   │
│                                      │
│ [ ] Set as Base Currency            │
│ [✓] Active                          │
│                                      │
│ [Cancel]         [Save]             │
└─────────────────────────────────────┘
```

**Step 3: Save Currency**

⚠️ **Note**: Only one base currency allowed!

### Managing Exchange Rates

**Navigation**: Accounting → Exchange Rates

#### Adding Exchange Rate

**Step 1: Click "Add Exchange Rate"**

**Step 2: Enter Rate Details**

```
┌─────────────────────────────────────┐
│ Add Exchange Rate                   │
├─────────────────────────────────────┤
│ From Currency: [USD ▼]             │
│ To Currency: [LKR ▼]               │
│                                      │
│ Exchange Rate: 330.00               │
│ Effective Date: 01-04-2025         │
│                                      │
│ [Cancel]         [Save]             │
└─────────────────────────────────────┘
```

**Step 3: Save Rate**

**Example Rates:**
```
1 USD = 330.00 LKR
1 EUR = 380.00 LKR
1 GBP = 430.00 LKR
1 INR = 4.00 LKR
```

### Multi-Currency Transactions

#### Recording Foreign Currency Entry

**Scenario**: Received $1,000 from customer

**Step 1: Create Receipt Voucher**
```
Entry Type: Receipt Voucher (RV)
Date: 15-Apr-2025

Line Items:
1. Bank Account (USD)      Dr    $1,000
   (Equivalent LKR: Rs. 330,000)
2. Sales Revenue (LKR)     Cr    Rs. 330,000

Narration: Received payment from US customer
```

**System Behavior:**
- Automatically converts using latest exchange rate
- Stores both original and converted amounts
- Reports show in base currency (LKR)

---

## ✅ Best Practices

### Data Entry Best Practices

#### 1. Consistency
- **Use consistent narrations** - Standardize descriptions
- **Date format** - Always use DD-MM-YYYY
- **Naming conventions** - Follow established patterns
- **Regular posting** - Enter transactions daily, not monthly

#### 2. Accuracy
- **Double-check amounts** - Verify before submitting
- **Verify ledger selection** - Ensure correct accounts
- **Match Dr/Cr** - Always balanced entries
- **Attach documentation** - Keep supporting documents

#### 3. Organization
- **Use tags** - Categorize entries logically
- **Meaningful narrations** - Detailed descriptions
- **Sequential numbering** - Don't skip entry numbers
- **Regular backups** - Export data regularly

### Month-End Procedures

#### Closing Checklist

- [ ] **Reconcile all bank accounts**
- [ ] **Record all outstanding invoices**
- [ ] **Enter all expense bills**
- [ ] **Record depreciation**
- [ ] **Review trial balance**
- [ ] **Generate financial reports**
- [ ] **Archive bank statements**
- [ ] **Backup accounting data**

#### Month-End Entries

**Depreciation:**
```
JV Entry:
Dr Depreciation Expense    Rs. 10,000
Cr Accumulated Depreciation Rs. 10,000
```

**Accruals:**
```
JV Entry:
Dr Salary Expense          Rs. 50,000
Cr Salary Payable         Rs. 50,000
```

### Year-End Procedures

#### Year-End Checklist

- [ ] **Complete all month-end procedures**
- [ ] **Reconcile all accounts**
- [ ] **Physical inventory count**
- [ ] **Review all ledgers for errors**
- [ ] **Adjust for provisions and accruals**
- [ ] **Generate annual reports**
- [ ] **Close books for the year**
- [ ] **Setup new financial year**

#### Closing Entries

Transfer profit/loss to retained earnings:

```
If Profit:
Dr P&L Account           Rs. 500,000
Cr Retained Earnings     Rs. 500,000

If Loss:
Dr Retained Earnings     Rs. 100,000
Cr P&L Account          Rs. 100,000
```

---

## 📖 Common Scenarios

### Scenario 1: Recording a Sale

**Situation**: Sold goods worth Rs. 100,000, received by cheque

**Entry:**
```
Entry Type: Sales Voucher (INV)
Date: [Transaction Date]

Line Items:
1. Bank Account           Dr    Rs. 100,000
2. Sales Revenue          Cr    Rs. 100,000

Narration: Sale of goods to [Customer Name], Invoice #INV-001
Tag: Sales
```

### Scenario 2: Recording a Purchase

**Situation**: Purchased raw materials worth Rs. 50,000 on credit

**Entry:**
```
Entry Type: Purchase Voucher (PINV)
Date: [Transaction Date]

Line Items:
1. Purchase Expense       Dr    Rs. 50,000
2. Accounts Payable       Cr    Rs. 50,000

Narration: Purchase from [Supplier Name], Bill #BILL-001
Tag: Purchases
```

### Scenario 3: Paying Salary

**Situation**: Paid employee salaries Rs. 75,000 by bank transfer

**Entry:**
```
Entry Type: Payment Voucher (PV)
Date: [Transaction Date]

Line Items:
1. Salary Expense         Dr    Rs. 75,000
2. Bank Account           Cr    Rs. 75,000

Narration: Salary payment for April 2025
Tag: Expenses
```

### Scenario 4: Receiving Customer Payment

**Situation**: Customer paid Rs. 80,000 for outstanding invoice

**Entry:**
```
Entry Type: Receipt Voucher (RV)
Date: [Transaction Date]

Line Items:
1. Bank Account           Dr    Rs. 80,000
2. Accounts Receivable    Cr    Rs. 80,000

Narration: Payment received from [Customer Name] against Invoice #INV-001
Tag: Collections
```

### Scenario 5: Transferring Cash to Bank

**Situation**: Deposited Rs. 50,000 cash into bank account

**Entry:**
```
Entry Type: Contra Voucher (CV)
Date: [Transaction Date]

Line Items:
1. Bank Account           Dr    Rs. 50,000
2. Cash in Hand           Cr    Rs. 50,000

Narration: Cash deposited to bank
Tag: Transfers
```

### Scenario 6: Recording Depreciation

**Situation**: Monthly depreciation on machinery Rs. 10,000

**Entry:**
```
Entry Type: Journal Voucher (JV)
Date: [Month End Date]

Line Items:
1. Depreciation Expense   Dr    Rs. 10,000
2. Accumulated Depreciation Cr  Rs. 10,000

Narration: Depreciation for April 2025
Tag: Adjustments
```

### Scenario 7: Correcting an Error

**Situation**: Entry recorded wrong amount, need to reverse and re-record

**Step 1: Reverse Original Entry**
```
Entry Type: Journal Voucher (JV)
Date: [Original Date]

Line Items:
1. [Original Credit Account]  Dr    Rs. [Amount]
2. [Original Debit Account]   Cr    Rs. [Amount]

Narration: Reversal of [Original Entry Number] - incorrect amount
Tag: Corrections
```

**Step 2: Record Correct Entry**
```
Entry Type: [Original Entry Type]
Date: [Original Date]

Line Items:
1. [Correct Debit Account]    Dr    Rs. [Correct Amount]
2. [Correct Credit Account]   Cr    Rs. [Correct Amount]

Narration: [Correct description]
Tag: [Original Tag]
```

---

## 🔧 Troubleshooting

### Common Issues

#### Issue 1: Entry Won't Save - "Unbalanced Entry"

**Symptoms:**
- Error message: "Entry is not balanced"
- Cannot submit entry

**Causes:**
- Total Debits ≠ Total Credits
- Missing amounts
- Calculation error

**Solutions:**
1. **Verify totals** at bottom of form
2. **Recalculate manually** with calculator
3. **Check for typos** in amounts
4. **Ensure all line items have amounts**

**Example Fix:**
```
Before (Unbalanced):
Dr Cash         Rs. 50,000
Cr Rent         Rs. 50,500  ✗ Not balanced!

After (Balanced):
Dr Cash         Rs. 50,000
Cr Rent         Rs. 50,000  ✓ Balanced!
```

#### Issue 2: Cannot Find Ledger in Dropdown

**Symptoms:**
- Ledger not appearing in dropdown
- Search returns no results

**Causes:**
- Ledger hasn't been created yet
- Ledger is inactive
- Wrong account group

**Solutions:**
1. **Create the ledger first**
   - Go to Chart of Accounts
   - Click "Add Ledger"
   - Create with proper group

2. **Check if inactive**
   - Edit the ledger
   - Ensure it's marked as active

3. **Verify account group**
   - Ensure ledger is in correct group
   - Move to appropriate group if needed

#### Issue 3: Wrong Balance Showing in Reports

**Symptoms:**
- Balance doesn't match expectations
- Negative balance when should be positive
- Zero balance when should have value

**Causes:**
- Incorrect Dr/Cr classification
- Wrong date range selected
- Entry posted to wrong account
- Opening balance incorrect

**Solutions:**
1. **Run Ledger Statement**
   - Check all transactions
   - Verify Dr/Cr for each entry
   - Look for unusual amounts

2. **Check date range**
   - Ensure report includes all relevant dates
   - Verify financial year settings

3. **Verify opening balance**
   - Go to Chart of Accounts
   - Edit ledger
   - Check opening balance amount and type (Dr/Cr)

4. **Review recent entries**
   - Check last 10-20 entries
   - Look for errors or duplicates

#### Issue 4: Trial Balance Not Balanced

**Symptoms:**
- Trial balance shows "Not Balanced"
- Debit total ≠ Credit total

**Causes:**
- Data entry error in one or more entries
- Unbalanced entry somehow got saved (rare)
- Opening balances incorrectly entered
- System calculation error (very rare)

**Solutions:**
1. **Run entry validation**
   - Go to Search
   - Search all entries
   - Look for unbalanced entries

2. **Check opening balances**
   - Total all opening debit balances
   - Total all opening credit balances
   - Should be equal

3. **Verify recent entries**
   - Check last 50 entries
   - Verify each is balanced
   - Look for unusual patterns

4. **Contact administrator**
   - If issue persists
   - Provide screenshots
   - Note date when issue started

#### Issue 5: Reconciliation Date Won't Save

**Symptoms:**
- Enter reconciliation date but won't save
- Error message appears

**Causes:**
- Date format incorrect
- Date outside valid range
- Entry already reconciled

**Solutions:**
1. **Check date format**
   - Use DD-MM-YYYY format
   - Ensure valid date

2. **Verify date range**
   - Date should be between transaction date and today
   - Cannot be future date

3. **Check if already reconciled**
   - Look for existing reconciliation date
   - Clear existing date first if changing

#### Issue 6: Cannot Delete Entry

**Symptoms:**
- Delete button doesn't work
- Error message about permissions

**Causes:**
- Entry is reconciled
- Insufficient permissions
- Entry is locked (year-end closed)

**Solutions:**
1. **Check if reconciled**
   - Reconciled entries cannot be deleted
   - Unreconcile first

2. **Verify permissions**
   - Check user role
   - Contact administrator for permission

3. **Use reversing entry instead**
   - Create reverse entry
   - Maintains audit trail
   - Safer than deletion

### Getting Help

#### Information to Provide When Reporting Issues:

1. **User details**
   - Username
   - Role/permissions

2. **Issue description**
   - What were you trying to do?
   - What happened instead?
   - Any error messages?

3. **Steps to reproduce**
   - Detailed step-by-step
   - Screenshots if possible

4. **System information**
   - Date and time of issue
   - Browser being used
   - Any recent changes

#### Support Contacts:

**Accounting Manager**: accounting@company.com
**IT Support**: support@company.com  
**System Administrator**: admin@company.com

---

## 📚 Keyboard Shortcuts

### Global Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl + S` | Save entry/form |
| `Ctrl + N` | New entry |
| `Ctrl + F` | Focus search |
| `Esc` | Close modal/cancel |
| `Tab` | Next field |
| `Shift + Tab` | Previous field |

### Entry Form Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl + +` | Add new line item |
| `Ctrl + -` | Remove current line item |
| `F9` | Calculate totals |
| `Ctrl + Enter` | Submit entry |

---

## 📖 Glossary

**Account**: A record in the chart of accounts (group or ledger)

**Balance Sheet**: Financial statement showing assets, liabilities, and equity

**Chart of Accounts**: Organized list of all accounts used in the accounting system

**Contra Voucher (CV)**: Transfer between bank and cash accounts

**Credit (Cr)**: Right side of accounting entry; increases liabilities and income

**Debit (Dr)**: Left side of accounting entry; increases assets and expenses

**Double-Entry**: Every transaction affects at least two accounts

**Entry**: A complete accounting transaction (journal entry/voucher)

**Entry Type**: Category of voucher (JV, RV, PV, CV, INV, PINV)

**Financial Year**: 12-month accounting period

**Journal Voucher (JV)**: General accounting entry

**Ledger**: Individual account within the chart of accounts

**Narration**: Description/explanation of a transaction

**Opening Balance**: Starting balance at beginning of period

**Payment Voucher (PV)**: Record of money paid out

**Profit & Loss (P&L)**: Statement showing income, expenses, and profit

**Purchase Voucher (PINV)**: Record of purchases

**Receipt Voucher (RV)**: Record of money received

**Reconciliation**: Process of matching accounting records with bank statements

**Sales Voucher (INV)**: Record of sales transactions

**Tag**: Category label for entries

**Trial Balance**: List of all accounts with their balances

**Voucher**: Another term for accounting entry

---

## 📅 Maintenance Schedule

### Daily Tasks
- [ ] Enter all transactions
- [ ] Verify entry balances
- [ ] Check cash/bank balances

### Weekly Tasks
- [ ] Review outstanding items
- [ ] Check accounts receivable
- [ ] Check accounts payable
- [ ] Update exchange rates (if applicable)

### Monthly Tasks
- [ ] Bank reconciliation
- [ ] Generate financial reports
- [ ] Review trial balance
- [ ] Record depreciation
- [ ] Close month

### Quarterly Tasks
- [ ] Comprehensive account review
- [ ] Physical inventory verification
- [ ] Aging analysis (AR/AP)
- [ ] Tax preparations

### Annual Tasks
- [ ] Year-end closing
- [ ] Annual financial statements
- [ ] Tax filings
- [ ] Audit preparations
- [ ] Setup new financial year

---

*This guide is comprehensive but not exhaustive. For specific accounting regulations or complex scenarios, consult with a qualified accountant or your company's financial policies.*

**Document Version**: 1.0  
**Last Updated**: [Current Date]  
**Next Review**: [Date + 6 months]

---

**For additional support or questions, contact:**
- **Accounting Department**: accounting@company.com
- **Technical Support**: support@company.com
- **Training Requests**: training@company.com

