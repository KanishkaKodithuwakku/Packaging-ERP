<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update the item_type enum to include 'multi'
        DB::statement("ALTER TABLE goods_receipts MODIFY COLUMN item_type ENUM('box', 'divider', 'multi')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE goods_receipts MODIFY COLUMN item_type ENUM('box', 'divider')");
    }
};