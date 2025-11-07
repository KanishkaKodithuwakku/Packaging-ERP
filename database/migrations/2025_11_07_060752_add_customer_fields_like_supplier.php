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
        Schema::table('customers', function (Blueprint $table) {
            // General Info fields
            $table->string('website')->nullable()->after('email');
            $table->text('notes')->nullable()->after('address');
            $table->string('status')->default('active')->after('notes');

            // Primary Contact fields
            $table->string('contact_first_name')->nullable()->after('status');
            $table->string('contact_last_name')->nullable()->after('contact_first_name');
            $table->string('contact_email')->nullable()->after('contact_last_name');
            $table->string('contact_phone')->nullable()->after('contact_email');
            $table->string('contact_mobile')->nullable()->after('contact_phone');

            // Finance fields
            $table->string('account_receivable')->nullable()->after('contact_mobile');
            $table->string('sales_revenue')->nullable()->after('account_receivable');
            $table->string('tax')->nullable()->after('sales_revenue');
            $table->string('bank')->nullable()->after('tax');

            // Drop old is_active column if exists (we'll use status instead)
            if (Schema::hasColumn('customers', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'website',
                'notes',
                'status',
                'contact_first_name',
                'contact_last_name',
                'contact_email',
                'contact_phone',
                'contact_mobile',
                'account_receivable',
                'sales_revenue',
                'tax',
                'bank',
            ]);

            // Restore is_active if needed
            $table->boolean('is_active')->default(true)->after('currency');
        });
    }
};
