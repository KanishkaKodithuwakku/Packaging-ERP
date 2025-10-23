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
            $table->decimal('qty_processed', 15, 4)->default(0)->after('qty_received');
            $table->decimal('qty_remaining', 15, 4)->default(0)->after('qty_processed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grn_items', function (Blueprint $table) {
            $table->dropColumn(['qty_processed', 'qty_remaining']);
        });
    }
};