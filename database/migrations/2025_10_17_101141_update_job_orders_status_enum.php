<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the status enum to include 'pending' and set it as default
        DB::statement("ALTER TABLE job_orders MODIFY COLUMN status ENUM('pending', 'draft', 'confirmed', 'in_production', 'completed', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum
        DB::statement("ALTER TABLE job_orders MODIFY COLUMN status ENUM('draft', 'confirmed', 'in_production', 'completed', 'cancelled') DEFAULT 'draft'");
    }
};