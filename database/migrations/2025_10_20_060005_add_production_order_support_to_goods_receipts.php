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
        Schema::table('goods_receipts', function (Blueprint $table) {
            // Add production order support
            $table->foreignId('production_order_id')->nullable()->constrained('production_orders')->onDelete('cascade');
            $table->foreignId('production_order_item_id')->nullable()->constrained('production_order_items')->onDelete('cascade');
            
            // Add item details for production GRNs
            $table->enum('item_type', ['box', 'divider'])->nullable();
            $table->unsignedBigInteger('item_id')->nullable(); // ID of job_order_boxes or job_order_dividers
            $table->text('description')->nullable();
            
            // Make supplier_po_id nullable since GRNs can come from production orders
            $table->foreignId('supplier_po_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropForeign(['production_order_id']);
            $table->dropForeign(['production_order_item_id']);
            $table->dropColumn(['production_order_id', 'production_order_item_id', 'item_type', 'item_id', 'description']);
            
            // Revert supplier_po_id to required
            $table->foreignId('supplier_po_id')->nullable(false)->change();
        });
    }
};