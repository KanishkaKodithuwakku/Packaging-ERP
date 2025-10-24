<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles (only if they don't exist)
        $roles = [
            'admin',
            'sales',
            'purchase',
            'production',
            'store',
            'account'
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Create permissions for each module
        $permissions = [
            // Quotation permissions
            'view quotations',
            'create quotations',
            'edit quotations',
            'delete quotations',
            'send quotations',
            'accept quotations',
            'reject quotations',

            // Customer Order permissions
            'view customer orders',
            'create customer orders',
            'edit customer orders',
            'delete customer orders',

            // Supplier Order permissions
            'view supplier orders',
            'create supplier orders',
            'edit supplier orders',
            'delete supplier orders',

            // GRN permissions
            'view grns',
            'create grns',
            'edit grns',
            'delete grns',

            // Job Order permissions
            'view job orders',
            'create job orders',
            'edit job orders',
            'delete job orders',

            // Inventory permissions
            'view inventory',
            'manage inventory',
            'view inventory transactions',

            // Delivery Note permissions
            'view delivery notes',
            'create delivery notes',
            'edit delivery notes',
            'delete delivery notes',
            'print delivery notes',

            // Material Request permissions
            'view material requests',
            'create material requests',
            'edit material requests',
            'delete material requests',

            // Purchase Order permissions
            'view purchase orders',
            'create purchase orders',
            'edit purchase orders',
            'delete purchase orders',
            'confirm purchase orders',
            'cancel purchase orders',

            // Production Order permissions
            'view production orders',
            'create production orders',
            'edit production orders',
            'delete production orders',

            // UOM Management permissions
            'view uom management',
            'create uom management',
            'edit uom management',
            'delete uom management',

            // Configuration permissions
            'view configuration',
            'edit configuration',

            // Accounting permissions
            'view accounting',
            'create accounting entries',
            'edit accounting entries',
            'delete accounting entries',
            'view accounting reports',

            // Role and Permission Management
            'view role management',
            'create roles',
            'edit roles',
            'delete roles',
            'view permission management',
            'create permissions',
            'edit permissions',
            'delete permissions',
            'view user management',
            'create users',
            'edit users',
            'delete users',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $adminRole = Role::findByName('admin');
        $adminRole->givePermissionTo(Permission::all());

        $salesRole = Role::findByName('sales');
        $salesRole->givePermissionTo([
            'view quotations',
            'create quotations',
            'edit quotations',
            'delete quotations',
            'send quotations',
            'accept quotations',
            'reject quotations',
            'view customer orders',
            'create customer orders',
            'edit customer orders',
            'delete customer orders',
        ]);

        $purchaseRole = Role::findByName('purchase');
        $purchaseRole->givePermissionTo([
            'view supplier orders',
            'create supplier orders',
            'edit supplier orders',
            'delete supplier orders',
            'view purchase orders',
            'create purchase orders',
            'edit purchase orders',
            'delete purchase orders',
            'confirm purchase orders',
            'cancel purchase orders',
        ]);

        $productionRole = Role::findByName('production');
        $productionRole->givePermissionTo([
            'view job orders',
            'create job orders',
            'edit job orders',
            'delete job orders',
            'view material requests',
            'create material requests',
            'edit material requests',
            'delete material requests',
            'view production orders',
            'create production orders',
            'edit production orders',
            'delete production orders',
        ]);

        $storeRole = Role::findByName('store');
        $storeRole->givePermissionTo([
            'view grns',
            'create grns',
            'edit grns',
            'delete grns',
            'view inventory',
            'manage inventory',
            'view inventory transactions',
        ]);

        $accountRole = Role::findByName('account');
        $accountRole->givePermissionTo([
            'view delivery notes',
            'create delivery notes',
            'edit delivery notes',
            'delete delivery notes',
            'print delivery notes',
            'view accounting',
            'create accounting entries',
            'edit accounting entries',
            'delete accounting entries',
            'view accounting reports',
        ]);

        // Create sample users (only if they don't exist)
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
            ]
        );
        $admin->assignRole('admin');

        $sales = User::firstOrCreate(
            ['email' => 'sales@example.com'],
            [
                'name' => 'Sales User',
                'password' => bcrypt('password'),
            ]
        );
        $sales->assignRole('sales');

        $purchase = User::firstOrCreate(
            ['email' => 'purchase@example.com'],
            [
                'name' => 'Purchase User',
                'password' => bcrypt('password'),
            ]
        );
        $purchase->assignRole('purchase');

        $production = User::firstOrCreate(
            ['email' => 'production@example.com'],
            [
                'name' => 'Production User',
                'password' => bcrypt('password'),
            ]
        );
        $production->assignRole('production');

        $store = User::firstOrCreate(
            ['email' => 'store@example.com'],
            [
                'name' => 'Store User',
                'password' => bcrypt('password'),
            ]
        );
        $store->assignRole('store');

        $account = User::firstOrCreate(
            ['email' => 'account@example.com'],
            [
                'name' => 'Account User',
                'password' => bcrypt('password'),
            ]
        );
        $account->assignRole('account');
    }
}
