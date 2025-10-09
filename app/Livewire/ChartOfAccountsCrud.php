<?php

namespace App\Livewire;

use App\Models\AccountGroup;
use App\Models\Currency;
use App\Models\Ledger;
use Livewire\Component;
use Livewire\WithPagination;

class ChartOfAccountsCrud extends Component
{
    use WithPagination;

    public $showAddGroupModal = false;
    public $showAddLedgerModal = false;
    public $search = '';

    public $groupForm = [
        'name' => '',
        'code' => '',
        'parent_id' => null,
        'affects_gross' => false,
        'notes' => '',
        'currency_id' => null,
    ];

    public $ledgerForm = [
        'name' => '',
        'code' => '',
        'group_id' => null,
        'op_balance' => 0,
        'op_balance_dc' => 'D',
        'type' => 0,
        'reconciliation' => false,
        'notes' => '',
        'currency_id' => null,
    ];

    public function openAddGroupModal()
    {
        $this->resetGroupForm();
        $this->showAddGroupModal = true;
        session()->flash('message', 'Group modal opened!');
    }

    public function openAddLedgerModal()
    {
        $this->resetLedgerForm();
        $this->showAddLedgerModal = true;
        session()->flash('message', 'Ledger modal opened!');
    }

    public function closeModal()
    {
        $this->showAddGroupModal = false;
        $this->showAddLedgerModal = false;
        $this->resetGroupForm();
        $this->resetLedgerForm();
    }

    public function addGroup()
    {
        try {
            $this->validate([
                'groupForm.name' => 'required|string|max:255',
                'groupForm.code' => 'nullable|string|max:255',
                'groupForm.parent_id' => 'nullable|exists:account_groups,id',
                'groupForm.affects_gross' => 'boolean',
                'groupForm.currency_id' => 'nullable|exists:currencies,id',
            ]);

            AccountGroup::create([
                'name' => $this->groupForm['name'],
                'code' => $this->groupForm['code'],
                'parent_id' => $this->groupForm['parent_id'],
                'affects_gross' => $this->groupForm['affects_gross'],
                'notes' => $this->groupForm['notes'],
                'currency_id' => $this->groupForm['currency_id'],
            ]);

            session()->flash('success', 'Account group created successfully!');
            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error creating group: ' . $e->getMessage());
        }
    }

    public function addLedger()
    {
        try {
            $this->validate([
                'ledgerForm.name' => 'required|string|max:255',
                'ledgerForm.code' => 'nullable|string|max:255',
                'ledgerForm.group_id' => 'required|exists:account_groups,id',
                'ledgerForm.op_balance' => 'required|numeric',
                'ledgerForm.op_balance_dc' => 'required|in:D,C',
                'ledgerForm.type' => 'required|integer',
                'ledgerForm.currency_id' => 'nullable|exists:currencies,id',
            ]);

            Ledger::create([
                'name' => $this->ledgerForm['name'],
                'code' => $this->ledgerForm['code'],
                'group_id' => $this->ledgerForm['group_id'],
                'op_balance' => $this->ledgerForm['op_balance'],
                'op_balance_dc' => $this->ledgerForm['op_balance_dc'],
                'type' => $this->ledgerForm['type'],
                'reconciliation' => $this->ledgerForm['reconciliation'],
                'notes' => $this->ledgerForm['notes'],
                'currency_id' => $this->ledgerForm['currency_id'],
            ]);

            session()->flash('success', 'Ledger created successfully!');
            $this->closeModal();
        } catch (\Exception $e) {
            session()->flash('error', 'Error creating ledger: ' . $e->getMessage());
        }
    }

    public function editGroup($id)
    {
        $group = AccountGroup::findOrFail($id);
        $this->groupForm = [
            'name' => $group->name,
            'code' => $group->code,
            'parent_id' => $group->parent_id,
            'affects_gross' => $group->affects_gross,
            'notes' => $group->notes,
            'currency_id' => $group->currency_id,
        ];
        $this->showAddGroupModal = true;
    }

    public function editLedger($id)
    {
        $ledger = Ledger::findOrFail($id);
        $this->ledgerForm = [
            'name' => $ledger->name,
            'code' => $ledger->code,
            'group_id' => $ledger->group_id,
            'op_balance' => $ledger->op_balance,
            'op_balance_dc' => $ledger->op_balance_dc,
            'type' => $ledger->type,
            'reconciliation' => $ledger->reconciliation,
            'notes' => $ledger->notes,
            'currency_id' => $ledger->currency_id,
        ];
        $this->showAddLedgerModal = true;
    }

    public function deleteGroup($id)
    {
        try {
            AccountGroup::findOrFail($id)->delete();
            session()->flash('success', 'Account group deleted successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting group: ' . $e->getMessage());
        }
    }

    public function deleteLedger($id)
    {
        try {
            Ledger::findOrFail($id)->delete();
            session()->flash('success', 'Ledger deleted successfully!');
        } catch (\Exception $e) {
            session()->flash('error', 'Error deleting ledger: ' . $e->getMessage());
        }
    }

    private function resetGroupForm()
    {
        $this->groupForm = [
            'name' => '',
            'code' => '',
            'parent_id' => null,
            'affects_gross' => false,
            'notes' => '',
            'currency_id' => null,
        ];
    }

    private function resetLedgerForm()
    {
        $this->ledgerForm = [
            'name' => '',
            'code' => '',
            'group_id' => null,
            'op_balance' => 0,
            'op_balance_dc' => 'D',
            'type' => 0,
            'reconciliation' => false,
            'notes' => '',
            'currency_id' => null,
        ];
    }

    public function render()
    {
        try {
            $query = AccountGroup::with(['children.ledgers', 'children.children', 'ledgers']);
            
            if ($this->search) {
                $query->where('name', 'like', '%' . $this->search . '%');
            }
            
            $rootGroups = $query->whereNull('parent_id')
                ->orWhere('parent_id', 0)
                ->get();

            $currencies = Currency::active()->get();
            $allGroups = AccountGroup::orderBy('name')->get();

            return view('livewire.chart-of-accounts-crud', [
                'rootGroups' => $rootGroups,
                'currencies' => $currencies,
                'allGroups' => $allGroups,
            ]);
        } catch (\Exception $e) {
            // Return empty data if there's an error
            return view('livewire.chart-of-accounts-crud', [
                'rootGroups' => collect(),
                'currencies' => collect(),
                'allGroups' => collect(),
            ]);
        }
    }
}