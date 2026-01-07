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
        Schema::table('production_order_items', function (Blueprint $table) {
            $table->integer('finished_goods_quantity')->default(0)->after('completed_quantity');
            $table->integer('waste_quantity')->default(0)->after('finished_goods_quantity')->comment('Positive = waste, Negative = conserve');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('production_order_items', function (Blueprint $table) {
            $table->dropColumn(['finished_goods_quantity', 'waste_quantity']);
        });
    }
};
