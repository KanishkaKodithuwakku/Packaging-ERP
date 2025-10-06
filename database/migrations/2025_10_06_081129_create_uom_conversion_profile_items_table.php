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
        Schema::create('uom_conversion_profile_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('uom_conversion_profiles')->onDelete('cascade');
            $table->foreignId('from_uom_id')->constrained('uoms')->onDelete('cascade');
            $table->foreignId('to_uom_id')->constrained('uoms')->onDelete('cascade');
            $table->decimal('factor', 15, 6); // Conversion factor
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Ensure no duplicate conversions within a profile
            $table->unique(['profile_id', 'from_uom_id', 'to_uom_id'], 'uom_profile_items_unique');
            
            // Index for performance
            $table->index(['profile_id', 'from_uom_id', 'to_uom_id'], 'uom_profile_items_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uom_conversion_profile_items');
    }
};