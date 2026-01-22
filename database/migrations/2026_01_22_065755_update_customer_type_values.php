<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update existing records that have old values
        DB::table('customers')
            ->where('customer_type', 'without_tax')
            ->update(['customer_type' => 'non_tax_customer']);
        
        DB::table('customers')
            ->where('customer_type', 'with_tax')
            ->update(['customer_type' => 'tax_customer']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
