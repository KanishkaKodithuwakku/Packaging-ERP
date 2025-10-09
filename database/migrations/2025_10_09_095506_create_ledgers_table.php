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
            $table->bigInteger('group_id')->index();
            $table->string('name')->unique();
            $table->string('code')->nullable()->unique();
            $table->decimal('op_balance', 25, 2)->default(0.00);
            $table->char('op_balance_dc', 1);
            $table->integer('type')->default(0);
            $table->integer('reconciliation')->default(0);
            $table->string('notes', 500);
        });

        // Add foreign key constraint after table creation
        Schema::table('ledgers', function (Blueprint $table) {
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ledgers', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
        });

        Schema::dropIfExists('ledgers');
    }
};
