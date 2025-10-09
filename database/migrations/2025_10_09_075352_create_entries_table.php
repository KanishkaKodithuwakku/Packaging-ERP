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
        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->bigInteger('tag_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->bigInteger('entrytype_id');
            $table->bigInteger('number')->nullable();
            $table->date('date');
            $table->decimal('dr_total', 25, 2)->default(0.00);
            $table->decimal('cr_total', 25, 2)->default(0.00);
            $table->string('narration', 500);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('tag_id')->references('id')->on('tags')->nullOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('entrytype_id')->references('id')->on('entrytypes')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entries');
    }
};
