<?php

namespace Database\Seeders;

use App\Models\Entry;
use App\Models\EntryItem;
use App\Models\EntryType;
use App\Models\Ledger;
use App\Models\AccountGroup;
use Illuminate\Database\Seeder;

class SampleJournalEntriesSeeder extends Seeder
{
    public function run(): void
    {
        // Get entry type
        $journalType = EntryType::where('label', 'journal')->first();
        if (!$journalType) {
            return;
        }

        // Get some ledgers for transactions
        $bankLedger = Ledger::where('code', '1100')->first(); // Seylan Bank
        $cashLedger = Ledger::where('code', '1121')->first(); // Cash in Hand
        $accountsReceivable = Ledger::where('code', '1120')->first(); // Accounts Receivable
        $inventoryLedger = Ledger::where('code', '1123')->first(); // Inventory
        $accountsPayable = Ledger::where('code', '2001')->first(); // Accounts Payable
        $ownersEquity = Ledger::where('code', '3000')->first(); // Owner's Equity

        if (!$bankLedger || !$ownersEquity) {
            return;
        }

        // Sample Journal Entry 1: Initial Capital Investment
        $entry1 = Entry::create([
            'date' => '2025-04-01',
            'entrytype_id' => $journalType->id,
            'number' => 1,
            'narration' => 'Initial capital investment by owner',
            'dr_total' => 500000.00,
            'cr_total' => 500000.00,
        ]);

        EntryItem::create([
            'entry_id' => $entry1->id,
            'ledger_id' => $bankLedger->id,
            'amount' => 500000.00,
            'dc' => 'D',
        ]);

        EntryItem::create([
            'entry_id' => $entry1->id,
            'ledger_id' => $ownersEquity->id,
            'amount' => 500000.00,
            'dc' => 'C',
        ]);

        // Sample Journal Entry 2: Purchase of Equipment
        if ($inventoryLedger) {
            $entry2 = Entry::create([
                'date' => '2025-04-15',
                'entrytype_id' => $journalType->id,
                'number' => 2,
                'narration' => 'Purchase of packaging equipment',
                'dr_total' => 150000.00,
                'cr_total' => 150000.00,
            ]);

            EntryItem::create([
                'entry_id' => $entry2->id,
                'ledger_id' => $inventoryLedger->id,
                'amount' => 150000.00,
                'dc' => 'D',
            ]);

            EntryItem::create([
                'entry_id' => $entry2->id,
                'ledger_id' => $bankLedger->id,
                'amount' => 150000.00,
                'dc' => 'C',
            ]);
        }

        // Sample Journal Entry 3: Sales on Credit
        if ($accountsReceivable) {
            $entry3 = Entry::create([
                'date' => '2025-04-20',
                'entrytype_id' => $journalType->id,
                'number' => 3,
                'narration' => 'Sales to customer ABC Corp on credit',
                'dr_total' => 250000.00,
                'cr_total' => 250000.00,
            ]);

            EntryItem::create([
                'entry_id' => $entry3->id,
                'ledger_id' => $accountsReceivable->id,
                'amount' => 250000.00,
                'dc' => 'D',
            ]);

            EntryItem::create([
                'entry_id' => $entry3->id,
                'ledger_id' => $bankLedger->id,
                'amount' => 250000.00,
                'dc' => 'C',
            ]);
        }

        // Sample Journal Entry 4: Purchase on Credit
        if ($accountsPayable) {
            $entry4 = Entry::create([
                'date' => '2025-04-25',
                'entrytype_id' => $journalType->id,
                'number' => 4,
                'narration' => 'Purchase of raw materials on credit from XYZ Ltd',
                'dr_total' => 75000.00,
                'cr_total' => 75000.00,
            ]);

            EntryItem::create([
                'entry_id' => $entry4->id,
                'ledger_id' => $inventoryLedger->id,
                'amount' => 75000.00,
                'dc' => 'D',
            ]);

            EntryItem::create([
                'entry_id' => $entry4->id,
                'ledger_id' => $accountsPayable->id,
                'amount' => 75000.00,
                'dc' => 'C',
            ]);
        }

        // Sample Journal Entry 5: Cash withdrawal for petty expenses
        if ($cashLedger) {
            $entry5 = Entry::create([
                'date' => '2025-04-30',
                'entrytype_id' => $journalType->id,
                'number' => 5,
                'narration' => 'Cash withdrawal for petty expenses',
                'dr_total' => 10000.00,
                'cr_total' => 10000.00,
            ]);

            EntryItem::create([
                'entry_id' => $entry5->id,
                'ledger_id' => $cashLedger->id,
                'amount' => 10000.00,
                'dc' => 'D',
            ]);

            EntryItem::create([
                'entry_id' => $entry5->id,
                'ledger_id' => $bankLedger->id,
                'amount' => 10000.00,
                'dc' => 'C',
            ]);
        }

        $this->command->info('Sample journal entries created successfully!');
    }
}
