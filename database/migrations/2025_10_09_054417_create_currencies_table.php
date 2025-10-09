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
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Full currency name (e.g., United States Dollar)');
            $table->string('code', 3)->unique()->comment('ISO 4217 currency code (e.g., USD)');
            $table->string('symbol', 10)->comment('Currency symbol (e.g., $)');
            $table->enum('symbol_position', ['before', 'after'])->default('before')->comment('Position of symbol relative to amount');
            $table->tinyInteger('decimal_places')->default(2)->comment('Number of decimal places to display');
            $table->boolean('is_base_currency')->default(false)->comment('Whether this is the base currency');
            $table->boolean('is_active')->default(true)->comment('Whether this currency is active');
            $table->timestamps();
            
            $table->index('code');
            $table->index('is_base_currency');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
