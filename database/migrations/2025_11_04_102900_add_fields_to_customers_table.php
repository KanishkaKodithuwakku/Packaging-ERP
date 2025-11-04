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
            $table->string('code')->nullable()->after('id');
            $table->string('contact_person')->nullable()->after('name');
            $table->string('currency', 3)->default('LKR')->after('email');
            $table->boolean('is_active')->default(true)->after('currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['code', 'contact_person', 'currency', 'is_active']);
        });
    }
};
