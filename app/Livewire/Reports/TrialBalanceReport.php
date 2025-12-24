<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\AccountGroup;
use App\Models\Ledger;
use Carbon\Carbon;

class TrialBalanceReport extends Component
{
    public $companyName = 'Kings Packaging ERP';
    public $subtitle = '';
    
    // Report data
    public $accountList = [];
    public $totalDr = 0;
    public $totalCr = 0;
    public $isBalanced = false;

    public function mount()
    {
        // Set subtitle
        $this->subtitle = 'Trial Balance from ' . now()->startOfYear()->format('d-M-Y') . ' to ' . now()->endOfYear()->format('d-M-Y');
        
        // Generate trial balance
        $this->generateTrialBalance();
    }

    public function generateTrialBalance()
    {
        // Build account list following Webzash logic
        // Webzash uses: only_opening = false, start_date = null, end_date = null, affects_gross = -1, start(0)
        // Start with root groups (Assets=1, Liabilities=2, Income=3, Expenses=4)
        $this->accountList = [
            'id' => 0,
            'name' => 'Root',
            'code' => 'ROOT',
            'type' => 'group',
            'level' => 0,
            'op_total' => 0,
            'op_total_dc' => 'D',
            'dr_total' => 0,
            'cr_total' => 0,
            'cl_total' => 0,
            'cl_total_dc' => 'D',
            'children_groups' => [],
            'children_ledgers' => []
        ];

        // Get root groups (Assets, Liabilities, Income, Expenses)
        $rootGroups = AccountGroup::whereNull('parent_id')->get();
        foreach ($rootGroups as $rootGroup) {
            $groupData = $this->buildAccountList($rootGroup->id, null, null);
            $this->accountList['children_groups'][] = $groupData;
        }
        
        // Calculate totals
        $this->calculateAccountListTotals($this->accountList);
        
        // Check if balanced
        $this->isBalanced = bccomp($this->totalDr, $this->totalCr, 2) == 0;
    }

    /**
     * Build account list following Webzash logic exactly
     */
    private function buildAccountList($groupId, $startDate, $endDate)
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
            'op_total' => 0,
            'op_total_dc' => 'D',
            'dr_total' => 0,
            'cr_total' => 0,
            'cl_total' => 0,
            'cl_total_dc' => 'D',
            'children_groups' => [],
            'children_ledgers' => []
        ];

        // Process child groups recursively
        foreach ($group->children as $childGroup) {
            $childAccountList = $this->buildAccountList($childGroup->id, $startDate, $endDate);
            $accountList['children_groups'][] = $childAccountList;
        }

        // Process ledgers - include ALL ledgers like Webzash does
        foreach ($group->ledgers as $ledger) {
            // For trial balance: only_opening = false, so we use closing balance
            $closingBalance = $ledger->closingBalance($startDate?->format('Y-m-d'), $endDate?->format('Y-m-d'));
            
            // Get opening balance for the period (this is the opening balance at start of financial year)
            $openingBalance = $ledger->openingBalance($startDate?->format('Y-m-d'));
            
            // Calculate debit and credit totals for the period
            $drTotal = $ledger->entryItems()
                ->where('dc', 'D')
                ->when($startDate, function($query) use ($startDate) {
                    return $query->whereHas('entry', function($q) use ($startDate) {
                        $q->where('date', '>=', $startDate);
                    });
                })
                ->when($endDate, function($query) use ($endDate) {
                    return $query->whereHas('entry', function($q) use ($endDate) {
                        $q->where('date', '<=', $endDate);
                    });
                })
                ->sum('amount');

            $crTotal = $ledger->entryItems()
                ->where('dc', 'C')
                ->when($startDate, function($query) use ($startDate) {
                    return $query->whereHas('entry', function($q) use ($startDate) {
                        $q->where('date', '>=', $startDate);
                    });
                })
                ->when($endDate, function($query) use ($endDate) {
                    return $query->whereHas('entry', function($q) use ($endDate) {
                        $q->where('date', '<=', $endDate);
                    });
                })
                ->sum('amount');

            $accountList['children_ledgers'][] = [
                'id' => $ledger->id,
                'name' => $ledger->name,
                'code' => $ledger->code,
                'type' => 'ledger',
                'level' => 1,
                'op_total' => $openingBalance['amount'],
                'op_total_dc' => $openingBalance['dc'],
                'dr_total' => $drTotal,
                'cr_total' => $crTotal,
                'cl_total' => $closingBalance['amount'],
                'cl_total_dc' => $closingBalance['dc']
            ];
        }

        return $accountList;
    }

    /**
     * Calculate totals for account list (following Webzash logic)
     */
    private function calculateAccountListTotals(&$accountList)
    {
        $accountList['dr_total'] = 0;
        $accountList['cr_total'] = 0;
        $accountList['op_total'] = 0;

        // Calculate from child groups
        if (isset($accountList['children_groups']) && is_array($accountList['children_groups'])) {
            foreach ($accountList['children_groups'] as &$childGroup) {
                $this->calculateAccountListTotals($childGroup);
                $accountList['dr_total'] = bcadd($accountList['dr_total'], $childGroup['dr_total'], 2);
                $accountList['cr_total'] = bcadd($accountList['cr_total'], $childGroup['cr_total'], 2);
                $accountList['op_total'] = bcadd($accountList['op_total'], $childGroup['op_total'], 2);
            }
        }

        // Calculate from child ledgers
        if (isset($accountList['children_ledgers']) && is_array($accountList['children_ledgers'])) {
            foreach ($accountList['children_ledgers'] as $ledger) {
                $accountList['dr_total'] = bcadd($accountList['dr_total'], $ledger['dr_total'], 2);
                $accountList['cr_total'] = bcadd($accountList['cr_total'], $ledger['cr_total'], 2);
                $accountList['op_total'] = bcadd($accountList['op_total'], $ledger['op_total'], 2);
            }
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

        // Set global totals for the root account
        if ($accountList['id'] == 0) {
            $this->totalDr = $accountList['dr_total'];
            $this->totalCr = $accountList['cr_total'];
        }
    }

    /**
     * Render account list HTML (for display in Blade) - following Webzash structure
     */
    public function renderAccountList($accountList, $level = 0)
    {
        $html = '';
        
        // Render the group itself if it has a name and is not the root
        if ($accountList['name'] && $accountList['id'] > 0) {
            $html .= '<tr class="bg-gray-50 font-semibold">';
            $html .= '<td class="px-4 py-3 text-sm text-gray-900 font-semibold">';
            $html .= '<div style="margin-left: ' . ($level * 20) . 'px;" class="flex items-center">';
            $html .= '<span class="font-semibold">[' . $accountList['code'] . '] ' . $accountList['name'] . '</span>';
            $html .= '</div>';
            $html .= '</td>';
            $html .= '<td class="px-4 py-3 text-sm text-center text-gray-600 font-medium">Group</td>';
            $html .= '<td class="px-4 py-3 text-sm text-right font-mono">';
            if ($accountList['op_total_dc'] == 'D') {
                $html .= 'Dr ' . number_format($accountList['op_total'], 2);
            } else {
                $html .= 'Cr ' . number_format($accountList['op_total'], 2);
            }
            $html .= '</td>';
            $html .= '<td class="px-4 py-3 text-sm text-right font-mono">Dr ' . number_format($accountList['dr_total'], 2) . '</td>';
            $html .= '<td class="px-4 py-3 text-sm text-right font-mono">Cr ' . number_format($accountList['cr_total'], 2) . '</td>';
            $html .= '<td class="px-4 py-3 text-sm text-right font-mono">';
            if ($accountList['cl_total_dc'] == 'D') {
                $html .= 'Dr ' . number_format($accountList['cl_total'], 2);
            } else {
                $html .= 'Cr ' . number_format($accountList['cl_total'], 2);
            }
            $html .= '</td>';
            $html .= '</tr>';
        }
        
        // Render child groups recursively
        if (isset($accountList['children_groups']) && is_array($accountList['children_groups'])) {
            foreach ($accountList['children_groups'] as $childGroup) {
                $html .= $this->renderAccountList($childGroup, $level + 1);
            }
        }
        
        // Render child ledgers
        if (isset($accountList['children_ledgers']) && is_array($accountList['children_ledgers'])) {
            foreach ($accountList['children_ledgers'] as $ledger) {
            $html .= '<tr class="">';
            $html .= '<td class="px-4 py-3 text-sm text-gray-700">';
            $html .= '<div style="margin-left: ' . (($level + 1) * 20) . 'px;" class="flex items-center">';
            $html .= '<a href="#" class="font-normal text-blue-600 hover:text-blue-800">[' . $ledger['code'] . '] ' . $ledger['name'] . '</a>';
            $html .= '</div>';
            $html .= '</td>';
            $html .= '<td class="px-4 py-3 text-sm text-center text-gray-600">Ledger</td>';
            $html .= '<td class="px-4 py-3 text-sm text-right font-mono">';
            if ($ledger['op_total_dc'] == 'D') {
                $html .= 'Dr ' . number_format($ledger['op_total'], 2);
            } else {
                $html .= 'Cr ' . number_format($ledger['op_total'], 2);
            }
            $html .= '</td>';
            $html .= '<td class="px-4 py-3 text-sm text-right font-mono">Dr ' . number_format($ledger['dr_total'], 2) . '</td>';
            $html .= '<td class="px-4 py-3 text-sm text-right font-mono">Cr ' . number_format($ledger['cr_total'], 2) . '</td>';
            $html .= '<td class="px-4 py-3 text-sm text-right font-mono">';
            if ($ledger['cl_total_dc'] == 'D') {
                $html .= 'Dr ' . number_format($ledger['cl_total'], 2);
            } else {
                $html .= 'Cr ' . number_format($ledger['cl_total'], 2);
            }
            $html .= '</td>';
            $html .= '</tr>';
            }
        }
        
        return $html;
    }

    public function render()
    {
        return view('livewire.reports.trial-balance-report');
    }
}
