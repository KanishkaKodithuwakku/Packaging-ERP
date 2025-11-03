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
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('delivery_note_item_id')->nullable()->after('related_doc_id');
            $table->foreign('delivery_note_item_id')->references('id')->on('delivery_note_items')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropForeign(['delivery_note_item_id']);
            $table->dropColumn('delivery_note_item_id');
        });
    }
};
