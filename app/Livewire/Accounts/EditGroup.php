<?php
namespace App\Livewire\Accounts;

use App\Models\Group;
use Livewire\Component;

class EditGroup extends Component
{
    protected $layout = 'components.layouts.app';

    public $groupId;
    public $name;
    public $code;
    public $parent_id;

    public function mount($id)
    {
        $group = Group::findOrFail($id);
        $this->groupId = $group->id;
        $this->name = $group->name;
        $this->code = $group->code;
        $this->parent_id = $group->parent_id;
    }

    public function updateGroup()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:groups,id',
        ]);

        $group = Group::findOrFail($this->groupId);
        $group->update([
            'name' => $this->name,
            'code' => $this->code,
            'parent_id' => $this->parent_id,
        ]);

        session()->flash('success', 'Group updated successfully.');
        return redirect()->route('chart-of-accounts');
    }

    public function render()
    {
        $parentGroups = Group::whereNull('parent_id')->orWhere('id', '!=', $this->groupId)->get();
        return view('livewire.accounts.edit-group', compact('parentGroups'));
    }
}
