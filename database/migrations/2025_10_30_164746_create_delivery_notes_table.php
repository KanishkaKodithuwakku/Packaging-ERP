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
        Schema::create('delivery_notes', function (Blueprint $table) {
            $table->id();
            $table->string('dn_number')->unique();
            $table->foreignId('job_order_id')->constrained('job_orders');
            $table->date('dispatch_date');
            $table->enum('status', ['draft', 'partial', 'dispatched', 'cancelled'])->default('draft');
            $table->text('delivery_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('delivery_note_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_note_id')->constrained('delivery_notes')->onDelete('cascade');
            $table->string('item_type'); // box, divider
            $table->unsignedBigInteger('item_id'); // JobOrderBox or JobOrderDivider ID
            $table->string('description');
            $table->string('material_code');
            $table->decimal('quantity', 15, 4);
            $table->decimal('dispatched_qty', 15, 4)->default(0);
            $table->decimal('remaining_qty', 15, 4)->default(0);
            $table->enum('status', ['pending', 'partial', 'dispatched'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_note_items');
        Schema::dropIfExists('delivery_notes');
    }
};
