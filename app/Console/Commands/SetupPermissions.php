<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PermissionService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class SetupPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:setup {--fresh : Clear existing permissions and roles}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup default permissions and roles for the application';

    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        parent::__construct();
        $this->permissionService = $permissionService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('fresh')) {
            $this->info('Clearing existing permissions and roles...');
            Permission::truncate();
            Role::truncate();
        }

        $this->info('Setting up permissions...');
        
        // Run the seeders
        $this->call('db:seed', ['--class' => 'PermissionSeeder']);
        $this->call('db:seed', ['--class' => 'RoleSeeder']);
        $this->call('db:seed', ['--class' => 'UserSeeder']);

        $this->info('Permissions setup completed successfully!');
        
        // Show summary
        $this->showSummary();
    }

    private function showSummary()
    {
        $this->info("\n=== Permission Setup Summary ===");
        $this->info("Permissions: " . Permission::count());
        $this->info("Roles: " . Role::count());
        
        $this->info("\nAvailable Roles:");
        Role::all()->each(function ($role) {
            $this->line("  - {$role->name} ({$role->permissions->count()} permissions)");
        });
    }
}
