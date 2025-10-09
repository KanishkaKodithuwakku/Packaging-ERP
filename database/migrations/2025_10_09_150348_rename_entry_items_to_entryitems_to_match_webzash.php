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
        // Rename entry_items table to entryitems to match Webzash
        Schema::rename('entry_items', 'entryitems');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename back to entry_items
        Schema::rename('entryitems', 'entry_items');
    }
};