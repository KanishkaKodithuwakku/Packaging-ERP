<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define permission groups for better organization
        $permissionGroups = [
            'quotations' => [
                'view quotations',
                'create quotations', 
                'edit quotations',
                'delete quotations',
                'send quotations',
                'accept quotations',
                'reject quotations',
            ],
            'customer_orders' => [
                'view customer orders',
                'create customer orders',
                'edit customer orders', 
                'delete customer orders',
            ],
            'job_orders' => [
                'view job orders',
                'create job orders',
                'edit job orders',
                'delete job orders',
            ],
            'purchase_orders' => [
                'view purchase orders',
                'create purchase orders',
                'edit purchase orders',
                'delete purchase orders',
                'confirm purchase orders',
                'cancel purchase orders',
            ],
            'production_orders' => [
                'view production orders',
                'create production orders',
                'edit production orders',
                'delete production orders',
            ],
            'supplier_orders' => [
                'view supplier orders',
                'create supplier orders',
                'edit supplier orders',
                'delete supplier orders',
            ],
            'grns' => [
                'view grns',
                'create grns',
                'edit grns',
                'delete grns',
            ],
            'material_requests' => [
                'view material requests',
                'create material requests',
                'edit material requests',
                'delete material requests',
            ],
            'delivery_notes' => [
                'view delivery notes',
                'create delivery notes',
                'edit delivery notes',
                'delete delivery notes',
                'print delivery notes',
            ],
            'inventory' => [
                'view inventory',
                'manage inventory',
                'view inventory transactions',
            ],
            'accounting' => [
                'view accounting',
                'create accounting entries',
                'edit accounting entries',
                'delete accounting entries',
                'view accounting reports',
            ],
            'uom_management' => [
                'view uom management',
                'create uom management',
                'edit uom management',
                'delete uom management',
            ],
            'configuration' => [
                'view configuration',
                'edit configuration',
            ],
            'user_management' => [
                'view user management',
                'create users',
                'edit users',
                'delete users',
            ],
            'role_management' => [
                'view role management',
                'create roles',
                'edit roles',
                'delete roles',
            ],
            'permission_management' => [
                'view permission management',
                'create permissions',
                'edit permissions',
                'delete permissions',
            ],
        ];

        // Create permissions with groups
        foreach ($permissionGroups as $group => $permissions) {
            foreach ($permissions as $permission) {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => 'web'
                ]);
            }
        }

        $this->command->info('Permissions created successfully!');
    }
}