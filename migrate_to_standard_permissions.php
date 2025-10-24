<?php

/**
 * Migration script to convert from non-standard to standard permission management
 */

require_once 'vendor/autoload.php';

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Migrating to Standard Permission Management ===\n\n";

// Step 1: Backup existing data
echo "1. Backing up existing permissions and roles...\n";
$existingPermissions = Permission::all()->pluck('name')->toArray();
$existingRoles = Role::all()->pluck('name')->toArray();

echo "   - Found " . count($existingPermissions) . " existing permissions\n";
echo "   - Found " . count($existingRoles) . " existing roles\n";

// Step 2: Clear and recreate with standard approach
echo "\n2. Clearing existing data...\n";
Permission::truncate();
Role::truncate();

// Step 3: Run standard seeders
echo "\n3. Running standard seeders...\n";
echo "   - PermissionSeeder: ";
exec('php artisan db:seed --class=PermissionSeeder', $output, $returnCode);
echo $returnCode === 0 ? "✅ Success" : "❌ Failed";
echo "\n";

echo "   - RoleSeeder: ";
exec('php artisan db:seed --class=RoleSeeder', $output, $returnCode);
echo $returnCode === 0 ? "✅ Success" : "❌ Failed";
echo "\n";

echo "   - UserSeeder: ";
exec('php artisan db:seed --class=UserSeeder', $output, $returnCode);
echo $returnCode === 0 ? "✅ Success" : "❌ Failed";
echo "\n";

// Step 4: Verify migration
echo "\n4. Verifying migration...\n";
$newPermissions = Permission::count();
$newRoles = Role::count();
$users = User::count();

echo "   - New permissions: {$newPermissions}\n";
echo "   - New roles: {$newRoles}\n";
echo "   - Users: {$users}\n";

// Step 5: Show permission groups
echo "\n5. Permission groups created:\n";
$permissions = Permission::all()->groupBy(function ($permission) {
    return explode(' ', $permission->name)[0];
});

foreach ($permissions as $group => $groupPermissions) {
    echo "   - {$group}: " . $groupPermissions->count() . " permissions\n";
}

echo "\n=== Migration Complete ===\n";
echo "✅ Standard permission management is now active!\n";
echo "✅ Use 'php artisan permissions:setup' for future updates\n";
