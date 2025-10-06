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
        Schema::create('inventory', function (Blueprint $table) {
            $table->string('lot_code')->primary();
            $table->string('item_code');
            $table->enum('category', ['RAW', 'WIP', 'FG']);
            $table->decimal('qty_available', 10, 2);
            $table->string('uom');
            $table->string('warehouse');
            $table->string('source');
            $table->string('ref_doc');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};
