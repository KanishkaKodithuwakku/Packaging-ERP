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
        Schema::create('ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('groups')->onDelete('restrict')->comment('Parent group ID');
            $table->string('name')->unique()->comment('Ledger name');
            $table->string('code')->nullable()->unique()->comment('Ledger code');
            $table->decimal('op_balance', 25, 2)->default(0)->comment('Opening balance');
            $table->enum('op_balance_dc', ['D', 'C'])->default('D')->comment('Opening balance Debit/Credit');
            $table->tinyInteger('type')->default(0)->comment('0: Normal, 1: Bank/Cash account');
            $table->boolean('reconciliation')->default(false)->comment('Enable reconciliation');
            $table->text('notes')->nullable()->comment('Additional notes');
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->onDelete('set null')->comment('Ledger currency');
            $table->timestamps();
            
            $table->index('group_id');
            $table->index('name');
            $table->index('code');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ledgers');
    }
};
