<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\SystemConfiguration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert default date format configuration if it doesn't exist
        if (!SystemConfiguration::where('key', 'date_format')->exists()) {
            SystemConfiguration::create([
                'key' => 'date_format',
                'value' => 'd-m-Y',
                'type' => 'string',
                'description' => 'Date format for displaying dates throughout the system',
                'category' => 'display',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove date format configuration
        SystemConfiguration::where('key', 'date_format')->delete();
    }
};
