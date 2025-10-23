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
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique()->comment('Tag title');
            $table->string('color', 6)->default('000000')->comment('Text color (hex)');
            $table->string('background', 6)->default('FFFFFF')->comment('Background color (hex)');
            $table->timestamps();
            
            $table->index('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
