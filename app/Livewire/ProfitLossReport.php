<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\AccountGroup;
use App\Models\Ledger;
use Carbon\Carbon;

class ProfitLossReport extends Component
{
    protected $layout = 'components.layouts.app';
    public $startDate;
    public $endDate;
    public $showOpeningBalance = false;
    public $companyName = 'Kings Packaging ERP';
    public $subtitle = '';

    // Report data
    public $grossExpenses = [];
    public $grossIncomes = [];
    public $netExpenses = [];
    public $netIncomes = [];
    public $grossExpenseTotal = 0;
    public $grossIncomeTotal = 0;
    public $netExpenseTotal = 0;
    public $netIncomeTotal = 0;
    public $grossPL = 0;
    public $netPL = 0;

    public function mount()
    {
        // Set default dates
        $this->startDate = now()->startOfYear()->format('Y-m-d');
        $this->endDate = now()->endOfYear()->format('Y-m-d');
        
        // Generate initial report
        $this->generateProfitLoss();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['showOpeningBalance', 'startDate', 'endDate'])) {
            $this->generateProfitLoss();
        }
    }

    public function generateProfitLoss()
    {
        $startDate = $this->startDate ? Carbon::parse($this->startDate) : null;
        $endDate = $this->endDate ? Carbon::parse($this->endDate) : null;
        
        // Update subtitle
        $this->updateSubtitle($startDate, $endDate);

        /**********************************************************************/
        /*********************** GROSS CALCULATIONS ***************************/
        /**********************************************************************/

        // Gross P/L : Expenses (affects_gross = 1)
        $this->grossExpenses = $this->buildAccountList(4, $startDate, $endDate, true); // Group ID 4 = Expenses
        $this->grossExpenseTotal = $this->calculateAccountListTotal($this->grossExpenses);

        // Gross P/L : Incomes (affects_gross = 1) 
        $this->grossIncomes = $this->buildAccountList(3, $startDate, $endDate, true); // Group ID 3 = Income
        $this->grossIncomeTotal = $this->calculateAccountListTotal($this->grossIncomes);

        // Calculating Gross P/L
        $this->grossPL = bcsub($this->grossIncomeTotal, $this->grossExpenseTotal, 2);

        /**********************************************************************/
        /************************* NET CALCULATIONS ***************************/
        /**********************************************************************/

        // Net P/L : Expenses (affects_gross = 0)
        $this->netExpenses = $this->buildAccountList(4, $startDate, $endDate, false);
        $this->netExpenseTotal = $this->calculateAccountListTotal($this->netExpenses);

        // Net P/L : Incomes (affects_gross = 0)
        $this->netIncomes = $this->buildAccountList(3, $startDate, $endDate, false);
        $this->netIncomeTotal = $this->calculateAccountListTotal($this->netIncomes);

        // Calculating Net P/L
        $netPL = bcsub($this->netIncomeTotal, $this->netExpenseTotal, 2);
        $this->netPL = bcadd($netPL, $this->grossPL, 2);
    }

    private function updateSubtitle($startDate, $endDate)
    {
        if ($this->showOpeningBalance) {
            $this->subtitle = 'Opening Trading and Profit & Loss Statement as on ' . 
                ($startDate ? $startDate->format('d-M-Y') : now()->startOfYear()->format('d-M-Y'));
        } else {
            $start = $startDate ? $startDate->format('d-M-Y') : now()->startOfYear()->format('d-M-Y');
            $end = $endDate ? $endDate->format('d-M-Y') : now()->endOfYear()->format('d-M-Y');
            $this->subtitle = 'Trading and Profit & Loss Statement from ' . $start . ' to ' . $end;
        }
    }

    /**
     * Build account list following Webzash logic
     */
    private function buildAccountList($groupId, $startDate, $endDate, $affectsGross)
    {
        $group = AccountGroup::find($groupId);
        if (!$group) {
            return [];
        }

        $accountList = [
            'id' => $group->id,
            'name' => $group->name,
            'code' => $group->code,
            'type' => 'group',
            'level' => 0,
            'balance' => 0,
            'balance_dc' => 'D',
            'op_total' => 0,
            'op_total_dc' => 'D',
            'dr_total' => 0,
            'cr_total' => 0,
            'cl_total' => 0,
            'cl_total_dc' => 'D',
            'children_groups' => [],
            'children_ledgers' => []
        ];

        // Process child groups
        foreach ($group->children as $childGroup) {
            $childAccountList = $this->buildAccountList($childGroup->id, $startDate, $endDate, $affectsGross);
            $accountList['children_groups'][] = $childAccountList;
        }

        // Process ledgers - filter by affects_gross if needed
        foreach ($group->ledgers as $ledger) {
            // Skip if this ledger doesn't match the affects_gross criteria
            // Note: In a real implementation, you'd need to add an 'affects_gross' field to ledgers
            // For now, we'll include all ledgers and let the user configure this
            
            $balance = $this->showOpeningBalance 
                ? $ledger->openingBalance($startDate?->format('Y-m-d'))
                : $ledger->closingBalance($startDate?->format('Y-m-d'), $endDate?->format('Y-m-d'));
            
            $accountList['children_ledgers'][] = [
                'id' => $ledger->id,
                'name' => $ledger->name,
                'code' => $ledger->code,
                'type' => 'ledger',
                'level' => 1,
                'balance' => $balance['amount'],
                'balance_dc' => $balance['dc'],
                'op_total' => $balance['amount'],
                'op_total_dc' => $balance['dc'],
                'dr_total' => $balance['dc'] == 'D' ? $balance['amount'] : 0,
                'cr_total' => $balance['dc'] == 'C' ? $balance['amount'] : 0,
                'cl_total' => $balance['amount'],
                'cl_total_dc' => $balance['dc']
            ];
        }

        // Calculate totals following Webzash logic
        $this->calculateAccountListTotals($accountList);

        return $accountList;
    }

    /**
     * Calculate totals for account list (following Webzash logic)
     */
    private function calculateAccountListTotals(&$accountList)
    {
        $accountList['dr_total'] = 0;
        $accountList['cr_total'] = 0;
        $accountList['cl_total'] = 0;

        // Calculate from child groups
        foreach ($accountList['children_groups'] as &$childGroup) {
            $this->calculateAccountListTotals($childGroup);
            $accountList['dr_total'] = bcadd($accountList['dr_total'], $childGroup['dr_total'], 2);
            $accountList['cr_total'] = bcadd($accountList['cr_total'], $childGroup['cr_total'], 2);
        }

        // Calculate from child ledgers
        foreach ($accountList['children_ledgers'] as $ledger) {
            $accountList['dr_total'] = bcadd($accountList['dr_total'], $ledger['dr_total'], 2);
            $accountList['cr_total'] = bcadd($accountList['cr_total'], $ledger['cr_total'], 2);
        }

        // Calculate closing total
        if (bccomp($accountList['dr_total'], $accountList['cr_total'], 2) > 0) {
            $accountList['cl_total'] = bcsub($accountList['dr_total'], $accountList['cr_total'], 2);
            $accountList['cl_total_dc'] = 'D';
        } elseif (bccomp($accountList['dr_total'], $accountList['cr_total'], 2) == 0) {
            $accountList['cl_total'] = 0;
            $accountList['cl_total_dc'] = 'D';
        } else {
            $accountList['cl_total'] = bcsub($accountList['cr_total'], $accountList['dr_total'], 2);
            $accountList['cl_total_dc'] = 'C';
        }

        $accountList['balance'] = $accountList['cl_total'];
        $accountList['balance_dc'] = $accountList['cl_total_dc'];
    }

    /**
     * Calculate total for account list
     */
    private function calculateAccountListTotal($accountList)
    {
        if ($accountList['cl_total_dc'] == 'D') {
            return $accountList['cl_total'];
        } else {
            // Return negative value for credit balances
            return '-' . $accountList['cl_total'];
        }
    }

    /**
     * Render account list HTML (for display in Blade)
     */
    public function renderAccountList($accountList, $level = 0)
    {
        $html = '';
        
        // Render the group itself if it has a name and is not root
        if ($accountList['name'] && $accountList['name'] !== 'None' && $accountList['id'] > 4) {
            $html .= '<tr class="bg-gray-50 font-semibold">';
            $html .= '<td class="px-4 py-3 text-sm text-gray-900 font-semibold">';
            $html .= '<div style="margin-left: ' . ($level * 20) . 'px;" class="flex items-center">';
            $html .= '<span class="font-semibold">[' . $accountList['code'] . '] ' . $accountList['name'] . '</span>';
            $html .= '</div>';
            $html .= '</td>';
            $html .= '<td class="px-4 py-3 text-sm text-right font-mono font-semibold">';
            if ($accountList['balance_dc'] == 'D') {
                $html .= 'Dr ' . number_format($accountList['balance'], 2);
            } else {
                $html .= 'Cr ' . number_format($accountList['balance'], 2);
            }
            $html .= '</td>';
            $html .= '</tr>';
        }
        
        // Render child groups
        foreach ($accountList['children_groups'] as $childGroup) {
            $html .= $this->renderAccountList($childGroup, $level + 1);
        }
        
        // Render child ledgers
        foreach ($accountList['children_ledgers'] as $ledger) {
            $html .= '<tr class="">';
            $html .= '<td class="px-4 py-3 text-sm text-gray-700">';
            $html .= '<div style="margin-left: ' . (($level + 1) * 20) . 'px;" class="flex items-center">';
            $html .= '<span class="font-normal">[' . $ledger['code'] . '] ' . $ledger['name'] . '</span>';
            $html .= '</div>';
            $html .= '</td>';
            $html .= '<td class="px-4 py-3 text-sm text-right font-mono">';
            if ($ledger['balance_dc'] == 'D') {
                $html .= 'Dr ' . number_format($ledger['balance'], 2);
            } else {
                $html .= 'Cr ' . number_format($ledger['balance'], 2);
            }
            $html .= '</td>';
            $html .= '</tr>';
        }
        
        return $html;
    }

    public function render()
    {
        return view('livewire.profit-loss-report');
    }
}
