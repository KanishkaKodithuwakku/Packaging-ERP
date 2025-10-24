<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default users with roles
        $users = [
            [
                'name' => 'System Administrator',
                'email' => 'admin@example.com',
                'password' => 'password',
                'role' => 'admin'
            ],
            [
                'name' => 'Sales Manager',
                'email' => 'sales@example.com', 
                'password' => 'password',
                'role' => 'sales'
            ],
            [
                'name' => 'Purchase Manager',
                'email' => 'purchase@example.com',
                'password' => 'password', 
                'role' => 'purchase'
            ],
            [
                'name' => 'Production Manager',
                'email' => 'production@example.com',
                'password' => 'password',
                'role' => 'production'
            ],
            [
                'name' => 'Store Manager',
                'email' => 'store@example.com',
                'password' => 'password',
                'role' => 'store'
            ],
            [
                'name' => 'Account Manager',
                'email' => 'account@example.com',
                'password' => 'password',
                'role' => 'account'
            ],
            [
                'name' => 'Planning Manager',
                'email' => 'planner@example.com',
                'password' => 'password',
                'role' => 'planner'
            ],
        ];

        foreach ($users as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => bcrypt($userData['password']),
                ]
            );

            // Assign role if user doesn't have it
            if (!$user->hasRole($userData['role'])) {
                $user->assignRole($userData['role']);
            }

            $this->command->info("User '{$userData['name']}' created/updated with role '{$userData['role']}'");
        }
    }
}
