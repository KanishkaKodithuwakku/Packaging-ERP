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
        // Add costing fields to inventory table
        Schema::table('inventory', function (Blueprint $table) {
            $table->decimal('unit_cost', 15, 4)->default(0)->after('qty_available');
            $table->decimal('total_value', 15, 4)->default(0)->after('unit_cost');
            $table->date('first_receipt_date')->nullable()->after('total_value');
            $table->date('last_movement_date')->nullable()->after('first_receipt_date');
            $table->enum('costing_method', ['FIFO', 'LIFO', 'AVERAGE'])->default('FIFO')->after('last_movement_date');
        });

        // Add costing fields to inventory_transactions table
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->decimal('unit_cost', 15, 4)->default(0)->after('qty');
            $table->decimal('total_cost', 15, 4)->default(0)->after('unit_cost');
            $table->decimal('remaining_qty', 15, 4)->default(0)->after('total_cost');
            $table->decimal('remaining_cost', 15, 4)->default(0)->after('remaining_qty');
            $table->string('costing_method', 10)->default('FIFO')->after('remaining_cost');
        });

        // Create inventory_layers table for FIFO/LIFO tracking
        Schema::create('inventory_layers', function (Blueprint $table) {
            $table->id();
            $table->string('lot_code');
            $table->string('item_code');
            $table->enum('category', ['RAW', 'WIP', 'FG']);
            $table->decimal('qty_available', 15, 4);
            $table->decimal('unit_cost', 15, 4);
            $table->decimal('total_cost', 15, 4);
            $table->date('receipt_date');
            $table->string('warehouse');
            $table->string('source_doc_type');
            $table->unsignedBigInteger('source_doc_id');
            $table->timestamps();
            
            $table->foreign('lot_code')->references('lot_code')->on('inventory')->onDelete('cascade');
            $table->index(['item_code', 'category', 'warehouse']);
            $table->index('receipt_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_layers');
        
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'unit_cost', 'total_cost', 'remaining_qty', 'remaining_cost', 'costing_method'
            ]);
        });
        
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropColumn([
                'unit_cost', 'total_value', 'first_receipt_date', 'last_movement_date', 'costing_method'
            ]);
        });
    }
};