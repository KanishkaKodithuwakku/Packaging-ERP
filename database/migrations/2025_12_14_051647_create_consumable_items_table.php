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
        Schema::create('consumable_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_code', 100)->unique();
            $table->string('item_name', 255);
            $table->text('description')->nullable();
            
            // Classification
            $table->enum('material_type', ['consumable'])->default('consumable');
            
            // Defaults
            $table->string('default_uom', 20)->default('KG');
            $table->decimal('default_unit_cost', 15, 4)->default(0);
            $table->string('default_warehouse', 100)->default('MAIN');
            
            // Inventory Management
            $table->decimal('min_stock_level', 10, 2)->default(0);
            $table->decimal('max_stock_level', 10, 2)->nullable();
            $table->decimal('reorder_point', 10, 2)->default(0);
            
            // Supplier Info (Optional)
            $table->foreignId('preferred_supplier_id')->nullable()->constrained('suppliers')->onDelete('set null');
            
            // Status
            $table->boolean('is_active')->default(true);
            
            // Metadata
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('item_code');
            $table->index('is_active');
            $table->index('preferred_supplier_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consumable_items');
    }
};
