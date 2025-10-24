<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class PermissionService
{
    /**
     * Get all permissions grouped by module
     */
    public function getPermissionsByGroup(): array
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode(' ', $permission->name)[0]; // Get first word as group
        });

        return $permissions->toArray();
    }

    /**
     * Create a new permission
     */
    public function createPermission(string $name, string $group = null): Permission
    {
        return Permission::create([
            'name' => $name,
            'guard_name' => 'web'
        ]);
    }

    /**
     * Create multiple permissions for a module
     */
    public function createModulePermissions(string $module, array $actions = ['view', 'create', 'edit', 'delete']): array
    {
        $permissions = [];
        
        foreach ($actions as $action) {
            $permissions[] = $this->createPermission("{$action} {$module}");
        }

        return $permissions;
    }

    /**
     * Assign permissions to role by group
     */
    public function assignPermissionsToRole(Role $role, array $permissionGroups): void
    {
        foreach ($permissionGroups as $group) {
            $permissions = Permission::where('name', 'like', "{$group}%")->get();
            $role->givePermissionTo($permissions);
        }
    }

    /**
     * Check if user has permission for a specific action
     */
    public function userCan(User $user, string $action, string $module): bool
    {
        return $user->can("{$action} {$module}");
    }

    /**
     * Get user's permissions grouped by module
     */
    public function getUserPermissions(User $user): array
    {
        return $user->getAllPermissions()->groupBy(function ($permission) {
            return explode(' ', $permission->name)[0];
        })->toArray();
    }

    /**
     * Sync role permissions (remove old, add new)
     */
    public function syncRolePermissions(Role $role, array $permissionNames): void
    {
        $permissions = Permission::whereIn('name', $permissionNames)->get();
        $role->syncPermissions($permissions);
    }
}
