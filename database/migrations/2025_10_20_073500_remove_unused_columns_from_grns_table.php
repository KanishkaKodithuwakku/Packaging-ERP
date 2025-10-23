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
        Schema::table('grns', function (Blueprint $table) {
            // Remove columns that are now in grn_items table
            $table->dropColumn([
                'production_order_item_id',
                'item_type',
                'item_id',
                'description',
                'material_code',
                'qty_received',
                'uom'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grns', function (Blueprint $table) {
            // Add back the columns if needed to rollback
            $table->unsignedBigInteger('production_order_item_id')->nullable();
            $table->enum('item_type', ['box', 'divider', 'multi'])->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->text('description')->nullable();
            $table->string('material_code')->nullable();
            $table->decimal('qty_received', 10, 2)->nullable();
            $table->string('uom', 10)->nullable();
        });
    }
};