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
        // Add UOM fields to supplier_po_items if table exists
        if (Schema::hasTable('supplier_po_items')) {
            Schema::table('supplier_po_items', function (Blueprint $table) {
                $table->foreignId('transaction_uom_id')->nullable()->constrained('uoms')->onDelete('set null');
                $table->decimal('transaction_qty', 15, 4)->nullable();
                $table->decimal('converted_qty', 15, 4)->nullable(); // Qty in base UOM
            });
        }

        // Add UOM fields to grn_items if table exists
        if (Schema::hasTable('grn_items')) {
            Schema::table('grn_items', function (Blueprint $table) {
                $table->foreignId('transaction_uom_id')->nullable()->constrained('uoms')->onDelete('set null');
                $table->decimal('transaction_qty', 15, 4)->nullable();
                $table->decimal('converted_qty', 15, 4)->nullable(); // Qty in base UOM
            });
        }

        // Add UOM fields to material_requisition_items if table exists
        if (Schema::hasTable('material_requisition_items')) {
            Schema::table('material_requisition_items', function (Blueprint $table) {
                $table->foreignId('transaction_uom_id')->nullable()->constrained('uoms')->onDelete('set null');
                $table->decimal('transaction_qty', 15, 4)->nullable();
                $table->decimal('converted_qty', 15, 4)->nullable(); // Qty in base UOM
            });
        }

        // Add UOM fields to job_order_items if table exists
        if (Schema::hasTable('job_order_items')) {
            Schema::table('job_order_items', function (Blueprint $table) {
                $table->foreignId('transaction_uom_id')->nullable()->constrained('uoms')->onDelete('set null');
                $table->decimal('transaction_qty', 15, 4)->nullable();
                $table->decimal('converted_qty', 15, 4)->nullable(); // Qty in base UOM
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('supplier_po_items')) {
            Schema::table('supplier_po_items', function (Blueprint $table) {
                $table->dropForeign(['transaction_uom_id']);
                $table->dropColumn(['transaction_uom_id', 'transaction_qty', 'converted_qty']);
            });
        }

        if (Schema::hasTable('grn_items')) {
            Schema::table('grn_items', function (Blueprint $table) {
                $table->dropForeign(['transaction_uom_id']);
                $table->dropColumn(['transaction_uom_id', 'transaction_qty', 'converted_qty']);
            });
        }

        if (Schema::hasTable('material_requisition_items')) {
            Schema::table('material_requisition_items', function (Blueprint $table) {
                $table->dropForeign(['transaction_uom_id']);
                $table->dropColumn(['transaction_uom_id', 'transaction_qty', 'converted_qty']);
            });
        }

        if (Schema::hasTable('job_order_items')) {
            Schema::table('job_order_items', function (Blueprint $table) {
                $table->dropForeign(['transaction_uom_id']);
                $table->dropColumn(['transaction_uom_id', 'transaction_qty', 'converted_qty']);
            });
        }
    }
};