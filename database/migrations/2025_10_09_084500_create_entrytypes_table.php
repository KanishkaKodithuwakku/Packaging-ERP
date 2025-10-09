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
        Schema::create('entrytypes', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('name');
            $table->string('description');
            $table->integer('base_type')->default(0);
            $table->integer('numbering')->default(1);
            $table->string('prefix');
            $table->string('suffix');
            $table->integer('zero_padding')->default(0);
            $table->integer('restriction_bankcash')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entrytypes');
    }
};
