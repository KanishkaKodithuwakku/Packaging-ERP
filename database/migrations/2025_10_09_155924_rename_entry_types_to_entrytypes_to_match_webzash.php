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
        // Rename entry_types table to entrytypes to match Webzash
        Schema::rename('entry_types', 'entrytypes');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename back to entry_types
        Schema::rename('entrytypes', 'entry_types');
    }
};