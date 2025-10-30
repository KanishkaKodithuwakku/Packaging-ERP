<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Ensure required permissions exist and Admin role has access
        try {
            $perm = Permission::firstOrCreate(['name' => 'view suppliers', 'guard_name' => 'web']);
            $admin = Role::where('name', 'admin')->first();
            if ($admin && !$admin->hasPermissionTo($perm)) {
                $admin->givePermissionTo($perm);
            }
        } catch (\Throwable $e) {
            // Swallow errors if tables not migrated yet
        }
    }
}
