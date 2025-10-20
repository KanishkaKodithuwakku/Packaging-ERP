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
        // Add processing fields to grns table
        Schema::table('grns', function (Blueprint $table) {
            $table->enum('status', ['pending', 'processed', 'cancelled'])->default('pending')->after('notes');
            $table->timestamp('processed_at')->nullable()->after('status');
            $table->decimal('total_value', 15, 4)->default(0)->after('processed_at');
        });

        // Add processing fields to grn_items table
        Schema::table('grn_items', function (Blueprint $table) {
            $table->string('inventory_lot_code')->nullable()->after('uom');
            $table->decimal('unit_cost', 15, 4)->default(0)->after('inventory_lot_code');
            $table->decimal('total_cost', 15, 4)->default(0)->after('unit_cost');
            $table->timestamp('processed_at')->nullable()->after('total_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grn_items', function (Blueprint $table) {
            $table->dropColumn(['inventory_lot_code', 'unit_cost', 'total_cost', 'processed_at']);
        });
        
        Schema::table('grns', function (Blueprint $table) {
            $table->dropColumn(['status', 'processed_at', 'total_value']);
        });
    }
};