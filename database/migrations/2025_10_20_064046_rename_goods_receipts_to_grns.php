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
        // Rename the table
        Schema::rename('goods_receipts', 'grns');
        
        // Update the grn_items table to reference the new table name
        Schema::table('grn_items', function (Blueprint $table) {
            $table->dropForeign(['grn_id']);
        });
        
        Schema::table('grn_items', function (Blueprint $table) {
            $table->foreign('grn_id')->references('id')->on('grns')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Update the grn_items table to reference the old table name
        Schema::table('grn_items', function (Blueprint $table) {
            $table->dropForeign(['grn_id']);
        });
        
        Schema::table('grn_items', function (Blueprint $table) {
            $table->foreign('grn_id')->references('id')->on('goods_receipts')->onDelete('cascade');
        });
        
        // Rename the table back
        Schema::rename('grns', 'goods_receipts');
    }
};