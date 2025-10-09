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
        Schema::create('entryitems', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->bigInteger('entry_id')->index();
            $table->bigInteger('ledger_id')->index();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->decimal('amount', 25, 2)->default(0.00);
            $table->char('dc', 1);
            $table->date('reconciliation_date')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('entry_id')->references('id')->on('entries')->onDelete('cascade');
            $table->foreign('ledger_id')->references('id')->on('ledgers')->onDelete('restrict');
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entryitems');
    }
};
