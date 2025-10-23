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
        Schema::create('account_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable()->comment('Parent group ID for hierarchical structure');
            $table->string('name')->unique()->comment('Group name');
            $table->string('code')->nullable()->unique()->comment('Group code');
            $table->boolean('affects_gross')->default(false)->comment('Affects Gross or Net Profit & Loss');
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->onDelete('set null')->comment('Default currency for this group');
            $table->timestamps();
            
            $table->index('parent_id');
            $table->index('name');
            $table->index('code');
            
            $table->foreign('parent_id')->references('id')->on('account_groups')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_groups');
    }
};
