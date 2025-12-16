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
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('delivery_note_item_id')->nullable()->constrained('delivery_note_items')->onDelete('set null');
            $table->string('item_type'); // box, divider
            $table->unsignedBigInteger('item_id')->nullable(); // JobOrderBox or JobOrderDivider ID
            $table->string('description');
            $table->string('material_code');
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('line_total', 15, 2);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            
            $table->index('invoice_id');
            $table->index('delivery_note_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
