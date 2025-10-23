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
        // First, drop the existing entries table and recreate it to match Webzash exactly
        Schema::dropIfExists('entries');
        
        Schema::create('entries', function (Blueprint $table) {
            // Primary key - bigint unsigned
            $table->id();
            
            // Foreign key to tags table - bigint unsigned, nullable
            $table->foreignId('tag_id')->nullable()->constrained('tags')->onDelete('set null');
            
            // Foreign key to entrytypes table - bigint unsigned, not null
            $table->foreignId('entrytype_id')->constrained('entrytypes')->onDelete('restrict');
            
            // Entry number - bigint unsigned, nullable
            $table->unsignedBigInteger('number')->nullable();
            
            // Entry date - date, not null
            $table->date('date');
            
            // Debit total - decimal(25,2), not null, default 0.00
            $table->decimal('dr_total', 25, 2)->default(0.00);
            
            // Credit total - decimal(25,2), not null, default 0.00
            $table->decimal('cr_total', 25, 2)->default(0.00);
            
            // Narration - varchar(500), not null
            $table->string('narration', 500);
            
            // Timestamps (Laravel specific - not in Webzash but useful for Laravel)
            $table->timestamps();
            
            // Indexes to match Webzash
            $table->index('tag_id');
            $table->index('entrytype_id');
            $table->index('date');
            $table->index('number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the original entries table structure if needed
        Schema::dropIfExists('entries');
        
        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tag_id')->nullable()->constrained('tags')->onDelete('set null');
            $table->foreignId('entrytype_id')->constrained('entrytypes')->onDelete('restrict');
            $table->unsignedBigInteger('number')->nullable();
            $table->date('date');
            $table->decimal('dr_total', 25, 2)->default(0);
            $table->decimal('cr_total', 25, 2)->default(0);
            $table->text('narration')->nullable();
            $table->timestamps();
            
            $table->index('tag_id');
            $table->index('entry_type_id');
            $table->index('date');
            $table->index('number');
        });
    }
};