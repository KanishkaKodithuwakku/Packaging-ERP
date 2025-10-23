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
        // Add job_order_id column to job_order_boxes if it doesn't exist
        if (!Schema::hasColumn('job_order_boxes', 'job_order_id')) {
            Schema::table('job_order_boxes', function (Blueprint $table) {
                $table->foreignId('job_order_id')->constrained('job_orders')->onDelete('cascade');
            });
        }

        // Add job_order_id column to job_order_dividers if it doesn't exist
        if (!Schema::hasColumn('job_order_dividers', 'job_order_id')) {
            Schema::table('job_order_dividers', function (Blueprint $table) {
                $table->foreignId('job_order_id')->constrained('job_orders')->onDelete('cascade');
            });
        }

        // Copy data from new_job_order_id to job_order_id if needed
        if (Schema::hasColumn('job_order_boxes', 'new_job_order_id') && Schema::hasColumn('job_order_boxes', 'job_order_id')) {
            DB::statement('UPDATE job_order_boxes SET job_order_id = new_job_order_id WHERE job_order_id IS NULL');
        }

        if (Schema::hasColumn('job_order_dividers', 'new_job_order_id') && Schema::hasColumn('job_order_dividers', 'job_order_id')) {
            DB::statement('UPDATE job_order_dividers SET job_order_id = new_job_order_id WHERE job_order_id IS NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_order_boxes', function (Blueprint $table) {
            $table->dropForeign(['job_order_id']);
            $table->dropColumn('job_order_id');
        });

        Schema::table('job_order_dividers', function (Blueprint $table) {
            $table->dropForeign(['job_order_id']);
            $table->dropColumn('job_order_id');
        });
    }
};