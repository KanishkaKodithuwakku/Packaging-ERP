<?php

namespace App\Livewire\Accounting;

use App\Models\AccountGroup;
use App\Models\Ledger;
use App\Models\Entry;
use App\Models\EntryType;
use App\Models\AccountLog;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AccountingDashboard extends Component
{
    protected $layout = 'components.layouts.app';

    public $accountDetails = [];
    public $bankCashSummary = [];
    public $accountSummary = [];
    public $recentActivity = [];
    public $monthlyIncomeData = [];
    public $monthlyExpenseData = [];
    public $assetsLiabilitiesData = [];

    public function mount()
    {
        $this->loadAccountDetails();
        $this->loadBankCashSummary();
        $this->loadAccountSummary();
        $this->loadRecentActivity();
        $this->loadChartData();
    }

    private function loadAccountDetails()
    {
        $this->accountDetails = [
            'name' => 'Kings Packaging ERP',
            'address' => 'Industrial Zone, Kiribathgoda, Sri Lanka',
            'email' => 'info@kingspackaging.com',
            'role' => Auth::user()->name ?? 'User',
            'currency' => 'Rs',
            'financial_year_start' => Carbon::now()->startOfYear()->format('d-M-Y'),
            'financial_year_end' => Carbon::now()->endOfYear()->format('d-M-Y'),
            'status' => 'Unlocked'
        ];
    }

    private function loadBankCashSummary()
    {
        // Get all bank/cash accounts (type = 1)
        $bankCashAccounts = Ledger::where('type', 1)
            ->with('group')
            ->orderBy('name')
            ->get();

        $this->bankCashSummary = [];
        foreach ($bankCashAccounts as $ledger) {
            $balance = $ledger->closingBalance();
            $this->bankCashSummary[] = [
                'code' => $ledger->code,
                'name' => $ledger->name,
                'balance' => $balance,
                'formatted_balance' => $this->formatCurrency($balance['dc'], $balance['amount'])
            ];
        }
    }

    private function loadAccountSummary()
    {
        // Get root account groups (Assets, Liabilities, Income, Expenses)
        $assetsGroup = AccountGroup::find(1); // Assets
        $liabilitiesGroup = AccountGroup::find(2); // Liabilities
        $incomeGroup = AccountGroup::find(3); // Income
        $expenseGroup = AccountGroup::find(4); // Expenses

        $this->accountSummary = [
            'assets' => $assetsGroup ? $this->safeCalculateClosingBalance($assetsGroup) : ['dc' => 'D', 'amount' => 0],
            'liabilities' => $liabilitiesGroup ? $this->safeCalculateClosingBalance($liabilitiesGroup) : ['dc' => 'C', 'amount' => 0],
            'income' => $incomeGroup ? $this->safeCalculateClosingBalance($incomeGroup) : ['dc' => 'C', 'amount' => 0],
            'expense' => $expenseGroup ? $this->safeCalculateClosingBalance($expenseGroup) : ['dc' => 'D', 'amount' => 0]
        ];
    }

    private function safeCalculateClosingBalance($group)
    {
        try {
            $balance = $group->calculateClosingBalance();
            // Ensure the returned array has the expected structure
            if (!isset($balance['amount'])) {
                $balance['amount'] = 0;
            }
            if (!isset($balance['dc'])) {
                $balance['dc'] = 'D';
            }
            return $balance;
        } catch (\Exception $e) {
            return ['dc' => 'D', 'amount' => 0];
        }
    }

    private function loadRecentActivity()
    {
        // Get recent entries with their entry types
        $recentEntries = Entry::with(['entryType', 'entryItems.ledger'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $this->recentActivity = [];
        foreach ($recentEntries as $entry) {
            $entryTypeName = $entry->entryType ? $entry->entryType->name : 'Unknown';
            $this->recentActivity[] = [
                'date' => $entry->created_at->format('d-M-Y'),
                'message' => "Added {$entryTypeName} entry numbered {$entry->number}",
                'entry_id' => $entry->id,
                'entry_type' => $entryTypeName,
                'amount' => $entry->dr_total
            ];
        }
    }

    private function loadChartData()
    {
        // Load monthly income and expense data for the last 6 months
        $this->monthlyIncomeData = $this->getMonthlyIncomeData();
        $this->monthlyExpenseData = $this->getMonthlyExpenseData();
        $this->assetsLiabilitiesData = $this->getAssetsLiabilitiesData();
    }

    private function getMonthlyIncomeData()
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            // Get income entries for this month (simplified approach)
            $incomeEntries = Entry::where('cr_total', '>', 0)
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->sum('cr_total');

            $data[] = [
                'month' => $month->format('M Y'),
                'amount' => (float)$incomeEntries
            ];
        }
        return $data;
    }

    private function getMonthlyExpenseData()
    {
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();
            
            // Get expense entries for this month (simplified approach)
            $expenseEntries = Entry::where('dr_total', '>', 0)
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->sum('dr_total');

            $data[] = [
                'month' => $month->format('M Y'),
                'amount' => (float)$expenseEntries
            ];
        }
        return $data;
    }

    private function getAssetsLiabilitiesData()
    {
        return [
            'assets' => $this->accountSummary['assets']['amount'] ?? 0,
            'liabilities' => $this->accountSummary['liabilities']['amount'] ?? 0
        ];
    }

    public function formatCurrency($dc, $amount)
    {
        $sign = $dc == 'D' ? 'Dr' : 'Cr';
        return $sign . ' ' . number_format((float)$amount, 2);
    }

    public function render()
    {
        return view('livewire.accounting.accounting-dashboard');
    }
}
