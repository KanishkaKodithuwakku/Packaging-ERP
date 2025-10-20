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
        Schema::create('grn_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grn_id')->constrained('goods_receipts')->onDelete('cascade');
            $table->foreignId('production_order_item_id')->nullable()->constrained('production_order_items')->onDelete('cascade');
            $table->enum('item_type', ['box', 'divider']);
            $table->unsignedBigInteger('item_id')->nullable(); // ID of job_order_boxes or job_order_dividers
            $table->text('description');
            $table->string('material_code');
            $table->decimal('qty_received', 10, 2);
            $table->string('uom', 10)->default('PCS');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grn_items');
    }
};