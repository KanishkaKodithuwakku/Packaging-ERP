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
        // Create roles
        $roles = [
            'admin',
            'sales',
            'purchase',
            'production',
            'store',
            'account'
        ];

        foreach ($roles as $role) {
            Role::create(['name' => $role]);
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
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
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
        ]);

        // Create sample users
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $sales = User::create([
            'name' => 'Sales User',
            'email' => 'sales@example.com',
            'password' => bcrypt('password'),
        ]);
        $sales->assignRole('sales');

        $purchase = User::create([
            'name' => 'Purchase User',
            'email' => 'purchase@example.com',
            'password' => bcrypt('password'),
        ]);
        $purchase->assignRole('purchase');

        $production = User::create([
            'name' => 'Production User',
            'email' => 'production@example.com',
            'password' => bcrypt('password'),
        ]);
        $production->assignRole('production');

        $store = User::create([
            'name' => 'Store User',
            'email' => 'store@example.com',
            'password' => bcrypt('password'),
        ]);
        $store->assignRole('store');

        $account = User::create([
            'name' => 'Account User',
            'email' => 'account@example.com',
            'password' => bcrypt('password'),
        ]);
        $account->assignRole('account');
    }
}
