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
        Schema::table('job_orders', function (Blueprint $table) {
            // Header Section
            $table->date('order_date')->nullable()->after('jo_no');
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null')->after('order_date');
            $table->string('supplier_po_ref')->nullable()->after('supplier_id');
            $table->string('customer_po_no')->nullable()->after('supplier_po_ref');
            
            // Order Details
            $table->decimal('order_qty', 10, 2)->nullable()->after('customer_po_no');
            $table->decimal('selling_price', 10, 2)->nullable()->after('order_qty');
            $table->string('activity')->nullable()->after('selling_price');
            $table->string('finishing_type')->nullable()->after('activity'); // Stitched/Glued
            
            // Box Specification
            $table->decimal('box_length_cm', 8, 2)->nullable()->after('finishing_type');
            $table->decimal('box_width_cm', 8, 2)->nullable()->after('box_length_cm');
            $table->decimal('box_height_cm', 8, 2)->nullable()->after('box_width_cm');
            $table->string('top_liner')->nullable()->after('box_height_cm');
            $table->string('combination')->nullable()->after('top_liner');
            $table->string('flute')->nullable()->after('combination');
            $table->decimal('sheet_width', 8, 2)->nullable()->after('flute');
            $table->decimal('sheet_length', 8, 2)->nullable()->after('sheet_width');
            $table->integer('no_of_ups')->nullable()->after('sheet_length');
            $table->decimal('board_qty', 10, 2)->nullable()->after('no_of_ups');
            
            // Printing Details
            $table->text('printing_instruction')->nullable()->after('board_qty');
            $table->integer('no_of_colours')->nullable()->after('printing_instruction');
            $table->string('sample_available')->nullable()->after('no_of_colours');
            $table->string('fsc_claim')->nullable()->after('sample_available');
            
            // Additional fields
            $table->text('notes')->nullable()->after('fsc_claim');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_orders', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            $table->dropColumn([
                'order_date',
                'supplier_id',
                'supplier_po_ref',
                'customer_po_no',
                'order_qty',
                'selling_price',
                'activity',
                'finishing_type',
                'box_length_cm',
                'box_width_cm',
                'box_height_cm',
                'top_liner',
                'combination',
                'flute',
                'sheet_width',
                'sheet_length',
                'no_of_ups',
                'board_qty',
                'printing_instruction',
                'no_of_colours',
                'sample_available',
                'fsc_claim',
                'notes',
            ]);
        });
    }
};
