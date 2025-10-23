<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the old job_orders table (from the original system)
        Schema::dropIfExists('job_orders');
        
        // Rename new_job_orders to job_orders
        Schema::rename('new_job_orders', 'job_orders');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename job_orders back to new_job_orders
        Schema::rename('job_orders', 'new_job_orders');
        
        // Note: We don't recreate the old job_orders table as it would conflict
        // If needed, restore from backup
    }
};