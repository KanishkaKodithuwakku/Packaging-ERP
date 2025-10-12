<?php

namespace App\Livewire;

use App\Models\AccountGroup;
use App\Models\AccountSetting;
use Livewire\Component;
use Carbon\Carbon;

class BalanceSheetReport extends Component
{
    public $showOpeningBalance = false;
    public $startDate = '';
    public $endDate = '';
    
    public $balanceSheet = [];
    public $subtitle = '';
    public $companyName = '';
    
    public function mount()
    {
        $this->companyName = AccountSetting::first()->company_name ?? 'Packaging ERP';
        
        // Set default dates to current financial year
        $settings = AccountSetting::first();
        if ($settings) {
            $this->startDate = $settings->fy_start ? Carbon::parse($settings->fy_start)->format('Y-m-d') : '';
            $this->endDate = $settings->fy_end ? Carbon::parse($settings->fy_end)->format('Y-m-d') : '';
        } else {
            $this->startDate = now()->startOfYear()->format('Y-m-d');
            $this->endDate = now()->endOfYear()->format('Y-m-d');
        }
        
        $this->generateBalanceSheet();
    }
    
    
    public function updated($propertyName)
    {
        if (in_array($propertyName, ['showOpeningBalance', 'startDate', 'endDate'])) {
            $this->generateBalanceSheet();
        }
    }
    
    public function generateBalanceSheet()
    {
        $startDate = $this->startDate ? Carbon::parse($this->startDate) : null;
        $endDate = $this->endDate ? Carbon::parse($this->endDate) : null;
        
        // Set subtitle
        if ($this->showOpeningBalance) {
            $this->subtitle = 'Opening Balance Sheet as on ' . ($startDate ? $startDate->format('d-M-Y') : 'Start of Year');
        } else {
            $this->subtitle = 'Closing Balance Sheet as on ' . ($endDate ? $endDate->format('d-M-Y') : 'End of Year');
        }
        
        // Use Webzash logic: start from root group IDs
        // Assets (ID 1), Liabilities (ID 2), Income (ID 3), Expenses (ID 4)
        
        // Get Assets using AccountList logic
        $assets = $this->buildAccountList(1, $startDate, $endDate);
        
        // Get Liabilities using AccountList logic
        $liabilities = $this->buildAccountList(2, $startDate, $endDate);
        
        // Get Income for P&L calculation
        $income = $this->buildAccountList(3, $startDate, $endDate);
        
        // Get Expenses for P&L calculation
        $expenses = $this->buildAccountList(4, $startDate, $endDate);
        
        // Calculate totals following Webzash logic exactly
        $assetsTotal = 0;
        if ($assets['cl_total_dc'] == 'D') {
            $assetsTotal = $assets['cl_total'];
        } else {
            $assetsTotal = bcmul($assets['cl_total'], '-1', 2);
        }
        
        $liabilitiesTotal = 0;
        if ($liabilities['cl_total_dc'] == 'C') {
            $liabilitiesTotal = $liabilities['cl_total'];
        } else {
            $liabilitiesTotal = bcmul($liabilities['cl_total'], '-1', 2);
        }
        
        // Calculate P&L following Webzash logic exactly
        $incomeTotal = 0;
        if ($income['cl_total_dc'] == 'C') {
            $incomeTotal = $income['cl_total'];
        } else {
            $incomeTotal = bcmul($income['cl_total'], '-1', 2);
        }
        
        $expenseTotal = 0;
        if ($expenses['cl_total_dc'] == 'D') {
            $expenseTotal = $expenses['cl_total'];
        } else {
            $expenseTotal = bcmul($expenses['cl_total'], '-1', 2);
        }
        
        $profitLoss = bcsub($incomeTotal, $expenseTotal, 2);
        
        // Final totals including P&L (following Webzash logic exactly)
        $finalAssetsTotal = $assetsTotal;
        $finalLiabilitiesTotal = $liabilitiesTotal;
        
        if (bccomp($profitLoss, 0, 2) >= 0) {
            // Net Profit - add to liabilities
            $finalLiabilitiesTotal = bcadd($finalLiabilitiesTotal, $profitLoss, 2);
        } else {
            // Net Loss - add to assets (as positive)
            $positiveLoss = bcmul($profitLoss, '-1', 2);
            $finalAssetsTotal = bcadd($finalAssetsTotal, $positiveLoss, 2);
        }
        
        $this->balanceSheet = [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'assets_total' => $assetsTotal,
            'liabilities_total' => $liabilitiesTotal,
            'profit_loss' => $profitLoss,
            'final_assets_total' => $finalAssetsTotal,
            'final_liabilities_total' => $finalLiabilitiesTotal,
            'is_balanced' => bccomp($finalAssetsTotal, $finalLiabilitiesTotal, 2) == 0
        ];
    }
    
    /**
     * Build AccountList following Webzash logic
     */
    protected function buildAccountList($groupId, $startDate, $endDate)
    {
        $group = AccountGroup::with(['children', 'ledgers'])->find($groupId);
        
        if (!$group) {
            return [
                'id' => $groupId,
                'name' => 'None',
                'code' => '',
                'type' => 'group',
                'level' => 0,
                'balance' => 0,
                'balance_dc' => 'D',
                'children_groups' => [],
                'children_ledgers' => [],
                'op_total' => 0,
                'op_total_dc' => 'D',
                'dr_total' => 0,
                'cr_total' => 0,
                'cl_total' => 0,
                'cl_total_dc' => 'D'
            ];
        }
        
        $accountList = [
            'id' => $group->id,
            'name' => $group->name,
            'code' => $group->code,
            'type' => 'group',
            'level' => 0,
            'balance' => 0,
            'balance_dc' => 'D',
            'children_groups' => [],
            'children_ledgers' => [],
            'op_total' => 0,
            'op_total_dc' => 'D',
            'dr_total' => 0,
            'cr_total' => 0,
            'cl_total' => 0,
            'cl_total_dc' => 'D'
        ];
        
        // Process child groups
        foreach ($group->children as $childGroup) {
            $childAccountList = $this->buildAccountList($childGroup->id, $startDate, $endDate);
            $accountList['children_groups'][] = $childAccountList;
        }
        
        // Process ledgers - follow Webzash logic exactly
        foreach ($group->ledgers as $ledger) {
            if ($this->showOpeningBalance) {
                // Opening Balance Sheet: Use opening balance only
                $balance = $ledger->openingBalance($startDate?->format('Y-m-d'));
                $drTotal = 0;
                $crTotal = 0;
            } else {
                // Closing Balance Sheet: Use calculated closing balance (opening + transactions)
                $closingBalance = $ledger->closingBalance($startDate?->format('Y-m-d'), $endDate?->format('Y-m-d'));
                $balance = $closingBalance;
                $drTotal = $closingBalance['dc'] == 'D' ? $closingBalance['amount'] : 0;
                $crTotal = $closingBalance['dc'] == 'C' ? $closingBalance['amount'] : 0;
            }
            
            // Include ALL ledgers like Webzash does
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
                'dr_total' => $drTotal,
                'cr_total' => $crTotal,
                'cl_total' => $balance['amount'],
                'cl_total_dc' => $balance['dc']
            ];
        }
        
        // Calculate totals following Webzash logic
        $this->calculateAccountListTotals($accountList);
        
        return $accountList;
    }
    
    /**
     * Calculate totals for AccountList following Webzash logic
     */
    protected function calculateAccountListTotals(&$accountList)
    {
        $opTotal = 0;
        $drTotal = 0;
        $crTotal = 0;
        $clTotal = 0;
        $clTotalDc = 'D';
        
        // Sum from child groups
        foreach ($accountList['children_groups'] as &$childGroup) {
            $this->calculateAccountListTotals($childGroup);
            $opTotal = bcadd($opTotal, $childGroup['op_total'], 2);
            $drTotal = bcadd($drTotal, $childGroup['dr_total'], 2);
            $crTotal = bcadd($crTotal, $childGroup['cr_total'], 2);
        }
        
        // Sum from child ledgers
        foreach ($accountList['children_ledgers'] as $ledger) {
            $opTotal = bcadd($opTotal, $ledger['op_total'], 2);
            $drTotal = bcadd($drTotal, $ledger['dr_total'], 2);
            $crTotal = bcadd($crTotal, $ledger['cr_total'], 2);
        }
        
        // Calculate closing balance following Webzash logic
        if (bccomp($drTotal, $crTotal, 2) > 0) {
            $clTotal = bcsub($drTotal, $crTotal, 2);
            $clTotalDc = 'D';
        } elseif (bccomp($crTotal, $drTotal, 2) > 0) {
            $clTotal = bcsub($crTotal, $drTotal, 2);
            $clTotalDc = 'C';
        } else {
            $clTotal = 0;
            $clTotalDc = 'D';
        }
        
        $accountList['op_total'] = $opTotal;
        $accountList['dr_total'] = $drTotal;
        $accountList['cr_total'] = $crTotal;
        $accountList['cl_total'] = $clTotal;
        $accountList['cl_total_dc'] = $clTotalDc;
        $accountList['balance'] = $clTotal;
        $accountList['balance_dc'] = $clTotalDc;
    }
    
    /**
     * Calculate total for AccountList following Webzash logic
     */
    protected function calculateAccountListTotal($accountList)
    {
        return $accountList['cl_total'];
    }
    
    public function downloadCsv()
    {
        // TODO: Implement CSV download
        session()->flash('message', 'CSV download feature coming soon!');
    }
    
    public function downloadXls()
    {
        // TODO: Implement XLS download
        session()->flash('message', 'XLS download feature coming soon!');
    }
    
    public function printReport()
    {
        // TODO: Implement print functionality
        session()->flash('message', 'Print feature coming soon!');
    }
    
    /**
     * Render AccountList for Blade template
     */
    public function renderAccountList($accountList, $level = 0)
    {
        $html = '';
        
        // Render the group itself if it has a name
        if ($accountList['name'] && $accountList['name'] !== 'None') {
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
        return view('livewire.balance-sheet-report');
    }
}
