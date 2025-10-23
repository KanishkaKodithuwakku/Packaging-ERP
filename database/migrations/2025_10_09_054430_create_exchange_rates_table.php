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
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_currency_id')->constrained('currencies')->onDelete('cascade')->comment('Source currency ID');
            $table->foreignId('to_currency_id')->constrained('currencies')->onDelete('cascade')->comment('Target currency ID');
            $table->decimal('rate', 15, 8)->comment('Exchange rate (from_currency to to_currency)');
            $table->date('rate_date')->comment('Date for which this rate is valid');
            $table->timestamps();
            
            $table->unique(['from_currency_id', 'to_currency_id', 'rate_date'], 'unique_rate');
            $table->index('from_currency_id');
            $table->index('to_currency_id');
            $table->index('rate_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
