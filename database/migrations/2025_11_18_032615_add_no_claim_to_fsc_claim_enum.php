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
        // Update job_order_boxes table
        DB::statement("ALTER TABLE job_order_boxes MODIFY COLUMN fsc_claim ENUM('100%', 'MIX', 'No Claim') NULL");

        // Update job_order_dividers table
        DB::statement("ALTER TABLE job_order_dividers MODIFY COLUMN fsc_claim ENUM('100%', 'MIX', 'No Claim') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert job_order_boxes table
        DB::statement("ALTER TABLE job_order_boxes MODIFY COLUMN fsc_claim ENUM('100%', 'MIX') NULL");

        // Revert job_order_dividers table
        DB::statement("ALTER TABLE job_order_dividers MODIFY COLUMN fsc_claim ENUM('100%', 'MIX') NULL");
    }
};
