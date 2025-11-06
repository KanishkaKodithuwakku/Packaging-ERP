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
        Schema::table('suppliers', function (Blueprint $table) {
            // General Info fields
            $table->string('website')->nullable()->after('email');
            $table->text('notes')->nullable()->after('address');
            $table->string('status')->default('active')->after('notes');

            // Primary Contact fields
            $table->string('contact_first_name')->nullable()->after('status');
            $table->string('contact_last_name')->nullable()->after('contact_first_name');
            $table->string('contact_email')->nullable()->after('contact_last_name');
            $table->string('contact_phone')->nullable()->after('contact_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn([
                'website',
                'notes',
                'status',
                'contact_first_name',
                'contact_last_name',
                'contact_email',
                'contact_phone',
            ]);
        });
    }
};
