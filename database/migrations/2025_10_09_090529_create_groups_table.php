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
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('parent_id')->nullable()->default(0)->index();
            $table->string('name')->index();
            $table->string('code')->nullable()->index();
            $table->tinyInteger('affects_gross')->default(0);
            $table->timestamps();
        });

        // Add self-referencing foreign key after table creation
        Schema::table('groups', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('groups')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
        });

        Schema::dropIfExists('groups');
    }
};
