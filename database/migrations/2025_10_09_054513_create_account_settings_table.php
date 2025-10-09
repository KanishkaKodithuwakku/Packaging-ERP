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
        Schema::create('account_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->comment('Company name');
            $table->text('address')->nullable()->comment('Company address');
            $table->string('email')->nullable()->comment('Company email');
            $table->date('fy_start')->comment('Financial year start date');
            $table->date('fy_end')->comment('Financial year end date');
            $table->string('currency_symbol', 10)->default('$')->comment('Currency symbol');
            $table->string('currency_format', 100)->default('1,234.56')->comment('Currency format');
            $table->tinyInteger('decimal_places')->default(2)->comment('Decimal places for amounts');
            $table->string('date_format', 100)->default('Y-m-d')->comment('Date format');
            $table->string('timezone', 100)->default('UTC')->comment('Timezone');
            $table->boolean('manage_inventory')->default(false)->comment('Enable inventory management');
            $table->boolean('account_locked')->default(false)->comment('Lock account (prevent modifications)');
            $table->boolean('email_use_default')->default(true)->comment('Use default email settings');
            $table->string('email_protocol', 10)->default('smtp')->comment('Email protocol');
            $table->string('email_host')->nullable()->comment('Email host');
            $table->integer('email_port')->default(587)->comment('Email port');
            $table->boolean('email_tls')->default(true)->comment('Use TLS for email');
            $table->string('email_username')->nullable()->comment('Email username');
            $table->string('email_password')->nullable()->comment('Email password');
            $table->string('email_from')->nullable()->comment('Email from address');
            $table->decimal('print_paper_height', 10, 3)->default(297.000)->comment('Paper height in mm');
            $table->decimal('print_paper_width', 10, 3)->default(210.000)->comment('Paper width in mm');
            $table->decimal('print_margin_top', 10, 3)->default(10.000)->comment('Top margin in mm');
            $table->decimal('print_margin_bottom', 10, 3)->default(10.000)->comment('Bottom margin in mm');
            $table->decimal('print_margin_left', 10, 3)->default(10.000)->comment('Left margin in mm');
            $table->decimal('print_margin_right', 10, 3)->default(10.000)->comment('Right margin in mm');
            $table->enum('print_orientation', ['P', 'L'])->default('P')->comment('P: Portrait, L: Landscape');
            $table->enum('print_page_format', ['A', 'C'])->default('A')->comment('A: A4, C: Custom');
            $table->integer('database_version')->default(1)->comment('Database schema version');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_settings');
    }
};
