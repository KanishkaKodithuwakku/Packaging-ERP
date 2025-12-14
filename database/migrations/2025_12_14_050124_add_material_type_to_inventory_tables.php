<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add material_type to inventory table
        Schema::table('inventory', function (Blueprint $table) {
            $table->enum('material_type', ['raw_material', 'consumable', 'component'])
                ->default('raw_material')
                ->after('category');
        });

        // Add material_type to inventory_transactions table
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->enum('material_type', ['raw_material', 'consumable', 'component'])
                ->default('raw_material')
                ->after('category');
        });

        // Add material_type to inventory_layers table
        Schema::table('inventory_layers', function (Blueprint $table) {
            $table->enum('material_type', ['raw_material', 'consumable', 'component'])
                ->default('raw_material')
                ->after('category');
        });

        // Update existing records to have default material_type
        // This ensures backward compatibility
        DB::table('inventory')->whereNull('material_type')->update(['material_type' => 'raw_material']);
        DB::table('inventory_transactions')->whereNull('material_type')->update(['material_type' => 'raw_material']);
        DB::table('inventory_layers')->whereNull('material_type')->update(['material_type' => 'raw_material']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory', function (Blueprint $table) {
            $table->dropColumn('material_type');
        });

        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropColumn('material_type');
        });

        Schema::table('inventory_layers', function (Blueprint $table) {
            $table->dropColumn('material_type');
        });
    }
};
