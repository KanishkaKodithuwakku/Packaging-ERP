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
            // Make item_type nullable to support consumable items
            $table->enum('item_type', ['box', 'divider'])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grn_items', function (Blueprint $table) {
            // Revert to non-nullable (this might fail if there are null values)
            $table->enum('item_type', ['box', 'divider'])->nullable(false)->change();
        });
    }
};
