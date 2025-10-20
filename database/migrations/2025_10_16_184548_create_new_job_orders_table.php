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
        Schema::create('new_job_orders', function (Blueprint $table) {
            $table->id();
            $table->string('job_number')->unique();
            $table->date('date');
            $table->foreignId('supplier_id')->constrained()->onDelete('cascade');
            $table->string('supplier_po_number');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('customer_address');
            $table->string('purchase_order_no')->nullable();
            $table->date('po_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'confirmed', 'in_production', 'completed', 'cancelled'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('new_job_orders');
    }
};