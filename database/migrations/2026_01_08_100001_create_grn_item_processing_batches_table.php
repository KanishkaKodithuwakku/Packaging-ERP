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
        Schema::create('grn_item_processing_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('grn_processing_batches')->onDelete('cascade');
            $table->foreignId('grn_item_id')->constrained('grn_items')->onDelete('cascade');
            $table->decimal('quantity_processed', 15, 4);
            $table->decimal('unit_cost', 15, 4)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->string('lot_code')->nullable();
            $table->timestamps();
            
            $table->index('batch_id');
            $table->index('grn_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grn_item_processing_batches');
    }
};
