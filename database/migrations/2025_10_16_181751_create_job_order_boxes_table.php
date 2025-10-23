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
        Schema::create('job_order_boxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_order_id')->constrained('job_orders')->onDelete('cascade');
            $table->integer('order_qty');
            $table->decimal('selling_price', 10, 2);
            $table->string('activity')->nullable();
            $table->string('printing_instruction')->nullable();
            $table->integer('no_of_colours')->nullable();
            $table->enum('stitched_glued', ['stitched', 'glued'])->nullable();
            $table->boolean('sample_available')->default(false);
            $table->boolean('sample_attached')->default(false);
            
            // Box Dimensions
            $table->decimal('length', 8, 3);
            $table->decimal('width', 8, 3);
            $table->decimal('height', 8, 3);
            $table->enum('unit', ['CM', 'MM', 'INCHES']);
            $table->enum('dimension_type', ['INTERNAL', 'EXTERNAL']);
            
            // Material Specifications
            $table->enum('top_liner', ['WHITE', 'BROWN']);
            $table->enum('ply', ['2', '5', '7']);
            $table->string('combination_1')->nullable();
            $table->string('combination_2')->nullable();
            $table->string('combination_3')->nullable();
            $table->string('combination_4')->nullable();
            $table->string('combination_5')->nullable();
            $table->string('combination_6')->nullable();
            $table->string('combination_7')->nullable();
            $table->enum('flute', ['B', 'C', 'B/C']);
            $table->enum('fsc_claim', ['100%', 'MIX']);
            
            // Calculated Fields
            $table->decimal('reel_size', 8, 3)->nullable();
            $table->decimal('cut_size', 8, 3)->nullable();
            $table->integer('no_of_ups')->nullable();
            $table->integer('board_qty')->nullable();
            $table->decimal('supplier_price', 10, 2)->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_order_boxes');
    }
};