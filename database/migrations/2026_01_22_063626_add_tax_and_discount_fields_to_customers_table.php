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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('customer_type')->default('non_tax_customer'); // tax_customer or non_tax_customer
            $table->string('vat_number')->nullable(); // VAT registration number
            $table->boolean('accept_discount')->default(false); // Accept discount checkbox
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['customer_type', 'vat_number', 'accept_discount']);
        });
    }
};
