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
        Schema::create('uom_conversions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_uom_id')->constrained('uoms')->onDelete('cascade');
            $table->foreignId('to_uom_id')->constrained('uoms')->onDelete('cascade');
            $table->decimal('factor', 15, 6); // Conversion factor
            $table->boolean('is_bidirectional')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Ensure no duplicate conversions
            $table->unique(['from_uom_id', 'to_uom_id']);
            
            // Index for performance
            $table->index(['from_uom_id', 'to_uom_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uom_conversions');
    }
};