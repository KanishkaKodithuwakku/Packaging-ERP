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
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['job_order_id']);
            // Make the column nullable
            $table->unsignedBigInteger('job_order_id')->nullable()->change();
            // Re-add the foreign key constraint (nullable)
            $table->foreign('job_order_id')->references('id')->on('job_orders')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['job_order_id']);
            // Make the column not nullable again
            $table->unsignedBigInteger('job_order_id')->nullable(false)->change();
            // Re-add the foreign key constraint (not nullable)
            $table->foreign('job_order_id')->references('id')->on('job_orders');
        });
    }
};
