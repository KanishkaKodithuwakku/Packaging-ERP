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
        Schema::create('entry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entry_id')->constrained('entries')->onDelete('cascade')->comment('Associated entry');
            $table->foreignId('ledger_id')->constrained('ledgers')->onDelete('restrict')->comment('Associated ledger');
            $table->decimal('amount', 25, 2)->default(0)->comment('Amount');
            $table->enum('dc', ['D', 'C'])->comment('Debit or Credit');
            $table->date('reconciliation_date')->nullable()->comment('Reconciliation date for bank/cash accounts');
            $table->timestamps();
            
            $table->index('entry_id');
            $table->index('ledger_id');
            $table->index('dc');
            $table->index('reconciliation_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entry_items');
    }
};
