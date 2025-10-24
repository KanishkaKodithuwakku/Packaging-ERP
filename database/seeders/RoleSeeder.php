<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define roles with their default permissions
        $roles = [
            'admin' => [
                'permissions' => 'all', // Admin gets all permissions
                'description' => 'Full system access'
            ],
            'sales' => [
                'permissions' => [
                    'quotations',
                    'customer_orders',
                ],
                'description' => 'Sales team access'
            ],
            'purchase' => [
                'permissions' => [
                    'supplier_orders',
                    'purchase_orders',
                ],
                'description' => 'Purchase team access'
            ],
            'production' => [
                'permissions' => [
                    'job_orders',
                    'production_orders',
                    'material_requests',
                ],
                'description' => 'Production team access'
            ],
            'store' => [
                'permissions' => [
                    'grns',
                    'inventory',
                ],
                'description' => 'Store/warehouse access'
            ],
            'account' => [
                'permissions' => [
                    'delivery_notes',
                    'accounting',
                ],
                'description' => 'Accounting team access'
            ],
            'planner' => [
                'permissions' => [
                    'job_orders',
                    'purchase_orders',
                    'production_orders',
                    'uom_management',
                ],
                'description' => 'Planning team access'
            ],
        ];

        foreach ($roles as $roleName => $config) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web'
            ]);

            if ($config['permissions'] === 'all') {
                // Admin gets all permissions
                $role->givePermissionTo(Permission::all());
            } else {
                // Assign permissions by group
                foreach ($config['permissions'] as $group) {
                    $permissions = Permission::where('name', 'like', $group . '%')->get();
                    $role->givePermissionTo($permissions);
                }
            }

            $this->command->info("Role '{$roleName}' created with permissions");
        }
    }
}
