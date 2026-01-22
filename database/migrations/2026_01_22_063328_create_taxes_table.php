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
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->string('description'); // e.g., "Value Added Tax"
            $table->string('abbreviation', 50); // e.g., "VAT"
            $table->decimal('percentage', 8, 4); // Tax percentage (e.g., 18.00)
            $table->decimal('reverse_calculation', 8, 4)->nullable(); // Reverse calculation value (e.g., 1.1800)
            $table->string('tax_label')->nullable(); // Tax label
            $table->tinyInteger('status')->default(1); // 1 = active, 0 = inactive
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};
