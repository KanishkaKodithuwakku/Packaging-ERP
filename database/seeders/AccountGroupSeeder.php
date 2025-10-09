<?php

namespace Database\Seeders;

use App\Models\AccountGroup;
use Illuminate\Database\Seeder;

class AccountGroupSeeder extends Seeder
{
    public function run(): void
    {
        // Root groups (based on Webzash default structure)
        $assets = AccountGroup::create([
            'parent_id' => null,
            'name' => 'Assets',
            'code' => 'AST',
            'affects_gross' => false,
        ]);

        $liabilities = AccountGroup::create([
            'parent_id' => null,
            'name' => 'Liabilities',
            'code' => 'LIA',
            'affects_gross' => false,
        ]);

        $income = AccountGroup::create([
            'parent_id' => null,
            'name' => 'Income',
            'code' => 'INC',
            'affects_gross' => false,
        ]);

        $expenses = AccountGroup::create([
            'parent_id' => null,
            'name' => 'Expenses',
            'code' => 'EXP',
            'affects_gross' => true,
        ]);

        // Sub-groups under Assets
        AccountGroup::create([
            'parent_id' => $assets->id,
            'name' => 'Current Assets',
            'code' => 'CA',
            'affects_gross' => false,
        ]);

        AccountGroup::create([
            'parent_id' => $assets->id,
            'name' => 'Fixed Assets',
            'code' => 'FA',
            'affects_gross' => false,
        ]);

        AccountGroup::create([
            'parent_id' => $assets->id,
            'name' => 'Investments',
            'code' => 'INV',
            'affects_gross' => false,
        ]);

        // Sub-groups under Liabilities
        AccountGroup::create([
            'parent_id' => $liabilities->id,
            'name' => 'Current Liabilities',
            'code' => 'CL',
            'affects_gross' => false,
        ]);

        AccountGroup::create([
            'parent_id' => $liabilities->id,
            'name' => 'Long Term Liabilities',
            'code' => 'LTL',
            'affects_gross' => false,
        ]);

        AccountGroup::create([
            'parent_id' => $liabilities->id,
            'name' => 'Capital Account',
            'code' => 'CAP',
            'affects_gross' => false,
        ]);

        // Sub-groups under Income
        AccountGroup::create([
            'parent_id' => $income->id,
            'name' => 'Direct Income',
            'code' => 'DI',
            'affects_gross' => true,
        ]);

        AccountGroup::create([
            'parent_id' => $income->id,
            'name' => 'Indirect Income',
            'code' => 'II',
            'affects_gross' => false,
        ]);

        // Sub-groups under Expenses
        AccountGroup::create([
            'parent_id' => $expenses->id,
            'name' => 'Direct Expenses',
            'code' => 'DE',
            'affects_gross' => true,
        ]);

        AccountGroup::create([
            'parent_id' => $expenses->id,
            'name' => 'Indirect Expenses',
            'code' => 'IE',
            'affects_gross' => false,
        ]);
    }
}
