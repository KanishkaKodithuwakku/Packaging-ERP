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
        Schema::create('entry_types', function (Blueprint $table) {
            $table->id();
            $table->string('label')->unique()->comment('Unique identifier label');
            $table->string('name')->comment('Display name');
            $table->string('description')->comment('Description');
            $table->tinyInteger('base_type')->default(0)->comment('0: Journal, 1: Receipt, 2: Payment, 3: Contra');
            $table->tinyInteger('numbering')->default(1)->comment('1: Auto, 2: Manual');
            $table->string('prefix')->nullable()->comment('Number prefix');
            $table->string('suffix')->nullable()->comment('Number suffix');
            $table->tinyInteger('zero_padding')->default(0)->comment('Zero padding for auto numbers');
            $table->tinyInteger('restriction_bankcash')->default(1)->comment('Bank/Cash restriction: 1: None, 2: At least one bank/cash, 3: Exactly one bank/cash');
            $table->timestamps();
            
            $table->index('label');
            $table->index('base_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entry_types');
    }
};
