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
        // Rename account_groups table to groups to match Webzash
        Schema::rename('account_groups', 'groups');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename back to account_groups
        Schema::rename('groups', 'account_groups');
    }
};