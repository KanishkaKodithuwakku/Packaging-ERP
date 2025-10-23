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
        Schema::table('grn_items', function (Blueprint $table) {
            // Add partial receiving fields
            $table->decimal('qty_expected', 15, 4)->default(0)->after('qty_received')->comment('Total expected quantity');
            $table->decimal('qty_received_partial', 15, 4)->default(0)->after('qty_expected')->comment('Currently received quantity');
            $table->decimal('qty_pending', 15, 4)->default(0)->after('qty_received_partial')->comment('Remaining quantity to receive');
            $table->boolean('is_fully_received')->default(false)->after('qty_pending')->comment('Whether all expected quantity is received');
            $table->timestamp('last_received_at')->nullable()->after('is_fully_received')->comment('Last partial receiving date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grn_items', function (Blueprint $table) {
            $table->dropColumn([
                'qty_expected',
                'qty_received_partial', 
                'qty_pending',
                'is_fully_received',
                'last_received_at'
            ]);
        });
    }
};