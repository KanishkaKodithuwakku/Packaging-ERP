<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemConfiguration;

class GRNConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // GRN Processing Configurations
        SystemConfiguration::setValue(
            'grn_default_costing_method',
            'FIFO',
            'string',
            'Default costing method for GRN processing (FIFO or LIFO)',
            'grn_processing'
        );

        SystemConfiguration::setValue(
            'grn_enable_partial_processing',
            'true',
            'boolean',
            'Enable partial quantity processing for GRN items',
            'grn_processing'
        );

        SystemConfiguration::setValue(
            'grn_auto_process_to_stock',
            'false',
            'boolean',
            'Automatically process GRN to stock when created',
            'grn_processing'
        );

        SystemConfiguration::setValue(
            'grn_require_approval',
            'false',
            'boolean',
            'Require approval before processing GRN to stock',
            'grn_processing'
        );
    }
}