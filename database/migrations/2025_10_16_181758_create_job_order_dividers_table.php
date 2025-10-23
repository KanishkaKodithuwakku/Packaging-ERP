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
        Schema::create('job_order_dividers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_order_id')->constrained('job_orders')->onDelete('cascade');
            $table->string('combination_1')->nullable();
            $table->string('combination_2')->nullable();
            $table->string('combination_3')->nullable();
            $table->string('combination_4')->nullable();
            $table->string('combination_5')->nullable();
            $table->string('combination_6')->nullable();
            $table->string('combination_7')->nullable();
            $table->enum('ply', ['2', '5', '7']);
            $table->integer('quantity');
            $table->enum('unit', ['CM', 'MM', 'INCHES']);
            $table->enum('fsc_claim', ['100%', 'MIX']);
            $table->decimal('supplier_price', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_order_dividers');
    }
};