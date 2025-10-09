<?php

namespace App\Livewire\Accounts;

use App\Models\EntryItem;
use App\Models\Group;
use App\Models\Ledger;
use Livewire\Component;
use Illuminate\Support\Collection;

class ChartOfAccounts extends Component
{
    protected $layout = 'components.layouts.app';

    public $accountTree = [];
    public $opdiff = [];

    public function mount()
    {
        $this->accountTree = $this->buildTree(null);
        //dd($this->accountTree);
        $this->opdiff = Ledger::getOpeningDifference();
    }

    private function buildTree($parentId): array
    {
        $groups = Group::where('parent_id', $parentId)->orderBy('name')->get();
        $tree = [];

        foreach ($groups as $group) {
            $ledgerNodes = [];
            $totalBalance = 0;

            // Ledgers under this group
            $ledgers = Ledger::where('group_id', $group->id)->orderBy('name')->get();
            foreach ($ledgers as $ledger) {
                $amounts = EntryItem::where('ledger_id', $ledger->id)
                    ->selectRaw("SUM(CASE WHEN dc = 'D' THEN amount ELSE 0 END) as dr,
                             SUM(CASE WHEN dc = 'C' THEN amount ELSE 0 END) as cr")
                    ->first();

                $openingBalance = $ledger->op_balance ?? 0;
                $openingDC = $ledger->op_balance_dc === 'D' ? 1 : -1;

                $opSigned = $openingBalance * $openingDC;
                $netMovement = ($amounts->dr ?? 0) - ($amounts->cr ?? 0);
                $closingBalance = $opSigned + $netMovement;

                $totalBalance += $closingBalance;



                $ledgerNodes[] = [
                    'id' => $ledger->id,
                    'name' => $ledger->name,
                    'code' => $ledger->code,
                    'type' => 'ledger',
                    'op_balance' => $openingBalance,
                    'op_balance_dc' => $ledger->op_balance_dc,
                    'dr_total' => $amounts->dr ?? 0,
                    'cr_total' => $amounts->cr ?? 0,
                    'cl_balance' => abs($closingBalance),
                    'cl_balance_dc' => $closingBalance >= 0 ? 'Dr' : 'Cr',
                ];
            }

            // Recursively handle children groups
            $children = $this->buildTree($group->id);
            foreach ($children as $child) {
                if (isset($child['cl_balance'])) {
                    $totalBalance += ($child['cl_balance_dc'] === 'Dr' ? 1 : -1) * $child['cl_balance'];
                }
            }

            // Compute group's closing balance
            $group_cl_dc = $totalBalance >= 0 ? 'Dr' : 'Cr';
            $group_cl_balance = abs($totalBalance);

            $tree[] = [
                'id' => $group->id,
                'name' => $group->name,
                'type' => 'group',
                'code' => $group->code,
                'affects_gross' => $group->affects_gross,
                'cl_balance' => $group_cl_balance,
                'cl_balance_dc' => $group_cl_dc,
                'ledgers' => $ledgerNodes,
                'children' => $children,
            ];
        }

        return $tree;
    }

    public function confirmDeleteLedger($ledgerId)
    {
        $ledger = Ledger::find($ledgerId);
        if ($ledger) {
            $ledger->delete();
            session()->flash('success', 'Ledger deleted successfully.');
            $this->accountTree = $this->buildTree(null);
        }
    }



    public function render()
    {
        return view('livewire.accounts.chart-of-accounts');
    }
}
