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
        // Add job_order_id column as nullable first (only if it doesn't exist)
        if (!Schema::hasColumn('purchase_order_items', 'job_order_id')) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                $table->unsignedBigInteger('job_order_id')->nullable()->after('purchase_order_id');
            });
        }

        // Backfill existing records with job_order_id from boxes/dividers
        // Get job_order_id from boxes
        DB::statement("
            UPDATE purchase_order_items poi
            INNER JOIN job_order_boxes job ON poi.item_id = job.id
            SET poi.job_order_id = job.job_order_id
            WHERE poi.item_type = 'box' AND poi.job_order_id IS NULL
        ");

        // Get job_order_id from dividers
        DB::statement("
            UPDATE purchase_order_items poi
            INNER JOIN job_order_dividers jod ON poi.item_id = jod.id
            SET poi.job_order_id = jod.job_order_id
            WHERE poi.item_type = 'divider' AND poi.job_order_id IS NULL
        ");

        // Add foreign key constraint if it doesn't exist
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'purchase_order_items' 
            AND COLUMN_NAME = 'job_order_id' 
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        if (empty($foreignKeys)) {
            Schema::table('purchase_order_items', function (Blueprint $table) {
                $table->foreign('job_order_id')->references('id')->on('job_orders')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropForeign(['job_order_id']);
            $table->dropColumn('job_order_id');
        });
    }
};

