<?php

namespace App\Livewire;

use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionEditor extends Component
{
    protected $layout = 'components.layouts.app';

    public int $roleId;
    public ?Role $role = null;
    public array $selected = [];

    public function mount(Role $role)
    {
        $this->roleId = $role->id;
        $this->loadRole();
    }

    public function loadRole(): void
    {
        $this->role = Role::findOrFail($this->roleId);
        $this->selected = $this->role->permissions()->pluck('name')->toArray();
    }

    public function togglePermission(string $permissionName)
    {
        if (in_array($permissionName, $this->selected)) {
            $this->selected = array_values(array_diff($this->selected, [$permissionName]));
        } else {
            $this->selected[] = $permissionName;
        }
    }

    public function save()
    {
        $perms = Permission::whereIn('name', $this->selected)->get();
        $this->role->syncPermissions($perms);
        session()->flash('success', 'Permissions updated for role: ' . $this->role->name);
        $this->loadRole();
    }

    public function render()
    {
        $permissions = Permission::orderBy('name')->get()->groupBy(function ($p) {
            // simple grouping by first word for display
            return ucfirst(strtok($p->name, ' '));
        });

        return view('livewire.roles.permission-editor', [
            'permissions' => $permissions,
        ]);
    }
}


