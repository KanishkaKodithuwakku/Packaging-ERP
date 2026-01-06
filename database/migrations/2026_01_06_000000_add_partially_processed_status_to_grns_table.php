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
        // Update the status enum to include 'partially_processed'
        DB::statement("ALTER TABLE grns MODIFY COLUMN status ENUM('pending', 'partially_processed', 'processed', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE grns MODIFY COLUMN status ENUM('pending', 'processed', 'cancelled') DEFAULT 'pending'");
    }
};


