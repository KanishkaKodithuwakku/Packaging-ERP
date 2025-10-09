<?php
namespace App\Livewire\Accounts;

use App\Models\Group;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\Ledger;
use Carbon\Carbon;
use App\Helpers\AccountingHelper;
use Livewire\Attributes\Layout;

class CreateGroup extends Component
{
    protected $layout = 'components.layouts.app';

    public $name = '';
    public $code = '';
    public $parent_id = null;

    public $ledger_id;
    public $group_id;

public $groups;
    public function mount(){
$this->groups = Group::orderBy('name')->get();
    }


    #[On('ledgerSelected')] // Correct placement
    public function setLedger($ledgerId)
    {
        $this->ledger_id = $ledgerId;
        $this->ledger = Ledger::find($ledgerId);
    }




    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:100',
            'parent_id' => 'nullable|exists:groups,id',
        ]);

        Group::create([
            'name' => $this->name,
            'code' => $this->code,
            'parent_id' => $this->group_id,
        ]);

        session()->flash('success', 'Group created successfully!');
        return redirect()->route('chart-of-accounts');
    }

    public function render()
    {
        $parentGroups = Group::whereNull('parent_id')->get();
        return view('livewire.accounts.create-group', compact('parentGroups'));
    }
}
