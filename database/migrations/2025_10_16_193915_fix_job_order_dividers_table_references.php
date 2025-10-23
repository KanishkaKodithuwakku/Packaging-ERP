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
        // Drop the existing foreign key constraint
        Schema::table('job_order_dividers', function (Blueprint $table) {
            $table->dropForeign(['job_order_id']);
        });

        // Rename the column to match our new table structure
        Schema::table('job_order_dividers', function (Blueprint $table) {
            $table->renameColumn('job_order_id', 'new_job_order_id');
        });

        // Add the new foreign key constraint
        Schema::table('job_order_dividers', function (Blueprint $table) {
            $table->foreign('new_job_order_id')->references('id')->on('new_job_orders')->onDelete('cascade');
        });

        // Update the PLY enum values
        Schema::table('job_order_dividers', function (Blueprint $table) {
            $table->enum('ply', ['3', '5', '7'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the new foreign key constraint
        Schema::table('job_order_dividers', function (Blueprint $table) {
            $table->dropForeign(['new_job_order_id']);
        });

        // Rename the column back
        Schema::table('job_order_dividers', function (Blueprint $table) {
            $table->renameColumn('new_job_order_id', 'job_order_id');
        });

        // Add the old foreign key constraint
        Schema::table('job_order_dividers', function (Blueprint $table) {
            $table->foreign('job_order_id')->references('id')->on('job_orders')->onDelete('cascade');
        });

        // Revert the PLY enum values
        Schema::table('job_order_dividers', function (Blueprint $table) {
            $table->enum('ply', ['2', '5', '7'])->change();
        });
    }
};