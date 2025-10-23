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
        // Check if both columns exist and drop the old one
        $columns = DB::select("SHOW COLUMNS FROM job_order_dividers");
        $columnNames = array_column($columns, 'Field');
        
        if (in_array('new_job_order_id', $columnNames) && in_array('job_order_id', $columnNames)) {
            // Drop the old foreign key constraint first
            try {
                DB::statement('ALTER TABLE job_order_dividers DROP FOREIGN KEY job_order_dividers_new_job_order_id_foreign');
            } catch (Exception $e) {
                // Foreign key might not exist or have a different name
            }
            
            // Drop the old column
            DB::statement('ALTER TABLE job_order_dividers DROP COLUMN new_job_order_id');
        }
        
        // Do the same for job_order_boxes table
        $columns = DB::select("SHOW COLUMNS FROM job_order_boxes");
        $columnNames = array_column($columns, 'Field');
        
        if (in_array('new_job_order_id', $columnNames) && in_array('job_order_id', $columnNames)) {
            // Drop the old foreign key constraint first
            try {
                DB::statement('ALTER TABLE job_order_boxes DROP FOREIGN KEY job_order_boxes_new_job_order_id_foreign');
            } catch (Exception $e) {
                // Foreign key might not exist or have a different name
            }
            
            // Drop the old column
            DB::statement('ALTER TABLE job_order_boxes DROP COLUMN new_job_order_id');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add back the old columns if needed for rollback
        Schema::table('job_order_dividers', function (Blueprint $table) {
            $table->unsignedBigInteger('new_job_order_id');
            $table->foreign('new_job_order_id')->references('id')->on('job_orders')->onDelete('cascade');
        });
        
        Schema::table('job_order_boxes', function (Blueprint $table) {
            $table->unsignedBigInteger('new_job_order_id');
            $table->foreign('new_job_order_id')->references('id')->on('job_orders')->onDelete('cascade');
        });
    }
};