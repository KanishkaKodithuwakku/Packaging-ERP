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
        Schema::create('account_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('date')->useCurrent()->comment('Log date and time');
            $table->tinyInteger('level')->default(1)->comment('1: Info, 2: Warning, 3: Error');
            $table->string('host_ip', 45)->nullable()->comment('User IP address');
            $table->string('user', 100)->nullable()->comment('Username');
            $table->string('url')->nullable()->comment('Request URL');
            $table->string('user_agent')->nullable()->comment('User agent');
            $table->text('message')->comment('Log message');
            $table->timestamps();
            
            $table->index('date');
            $table->index('level');
            $table->index('user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_logs');
    }
};
