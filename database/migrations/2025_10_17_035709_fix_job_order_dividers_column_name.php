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
        // Check if new_job_order_id column exists and job_order_id doesn't exist
        $columns = DB::select("SHOW COLUMNS FROM job_order_dividers");
        $columnNames = array_column($columns, 'Field');
        
        if (in_array('new_job_order_id', $columnNames) && !in_array('job_order_id', $columnNames)) {
            // Rename the column
            DB::statement('ALTER TABLE job_order_dividers CHANGE COLUMN new_job_order_id job_order_id BIGINT UNSIGNED');
        }
        
        // Do the same for job_order_boxes table
        $columns = DB::select("SHOW COLUMNS FROM job_order_boxes");
        $columnNames = array_column($columns, 'Field');
        
        if (in_array('new_job_order_id', $columnNames) && !in_array('job_order_id', $columnNames)) {
            // Rename the column
            DB::statement('ALTER TABLE job_order_boxes CHANGE COLUMN new_job_order_id job_order_id BIGINT UNSIGNED');
        }
        
        // Add foreign key constraints if they don't exist
        try {
            DB::statement('ALTER TABLE job_order_dividers ADD CONSTRAINT job_order_dividers_job_order_id_foreign FOREIGN KEY (job_order_id) REFERENCES job_orders(id) ON DELETE CASCADE');
        } catch (Exception $e) {
            // Foreign key might already exist
        }
        
        try {
            DB::statement('ALTER TABLE job_order_boxes ADD CONSTRAINT job_order_boxes_job_order_id_foreign FOREIGN KEY (job_order_id) REFERENCES job_orders(id) ON DELETE CASCADE');
        } catch (Exception $e) {
            // Foreign key might already exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if job_order_id column exists and new_job_order_id doesn't exist
        $columns = DB::select("SHOW COLUMNS FROM job_order_dividers");
        $columnNames = array_column($columns, 'Field');
        
        if (in_array('job_order_id', $columnNames) && !in_array('new_job_order_id', $columnNames)) {
            // Rename the column back
            DB::statement('ALTER TABLE job_order_dividers CHANGE COLUMN job_order_id new_job_order_id BIGINT UNSIGNED');
        }
        
        // Do the same for job_order_boxes table
        $columns = DB::select("SHOW COLUMNS FROM job_order_boxes");
        $columnNames = array_column($columns, 'Field');
        
        if (in_array('job_order_id', $columnNames) && !in_array('new_job_order_id', $columnNames)) {
            // Rename the column back
            DB::statement('ALTER TABLE job_order_boxes CHANGE COLUMN job_order_id new_job_order_id BIGINT UNSIGNED');
        }
    }
};