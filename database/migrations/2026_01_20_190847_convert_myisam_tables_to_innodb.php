<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Get the database name from your configuration
        $databaseName = config('database.connections.mysql.database');

        // 2. Find all tables currently using MyISAM
        $tables = DB::select("
            SELECT TABLE_NAME 
            FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = ? 
            AND ENGINE = 'MyISAM'
            AND TABLE_TYPE = 'BASE TABLE'
        ", [$databaseName]);

        // 3. Loop through and execute the ALTER command for each
        foreach ($tables as $table) {
            $name = $table->TABLE_NAME;
            DB::statement("ALTER TABLE `{$name}` ENGINE = InnoDB");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Typically no need to revert to MyISAM as it's an older engine
    }
};
