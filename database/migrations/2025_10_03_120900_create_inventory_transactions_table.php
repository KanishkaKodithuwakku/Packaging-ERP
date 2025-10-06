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
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('lot_code');
            $table->string('item_code');
            $table->enum('category', ['RAW', 'WIP', 'FG']);
            $table->enum('txn_type', ['receipt', 'consume', 'produce', 'delivery']);
            $table->decimal('qty', 10, 2);
            $table->string('uom');
            $table->string('warehouse');
            $table->string('related_doc_type');
            $table->unsignedBigInteger('related_doc_id');
            $table->date('txn_date');
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            $table->foreign('lot_code')->references('lot_code')->on('inventory')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
