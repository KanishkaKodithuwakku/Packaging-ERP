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
            $table->foreignId('tag_id')->nullable()->constrained('tags')->onDelete('set null')->comment('Associated tag');
            $table->foreignId('entry_type_id')->constrained('entry_types')->onDelete('restrict')->comment('Entry type');
            $table->unsignedBigInteger('number')->nullable()->comment('Entry number');
            $table->date('date')->comment('Entry date');
            $table->decimal('dr_total', 25, 2)->default(0)->comment('Total debit amount');
            $table->decimal('cr_total', 25, 2)->default(0)->comment('Total credit amount');
            $table->text('narration')->nullable()->comment('Entry narration/description');
            $table->timestamps();
            
            $table->index('tag_id');
            $table->index('entry_type_id');
            $table->index('date');
            $table->index('number');
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
