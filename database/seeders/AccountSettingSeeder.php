<?php

namespace Database\Seeders;

use App\Models\AccountSetting;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AccountSettingSeeder extends Seeder
{
    public function run(): void
    {
        AccountSetting::create([
            'company_name' => 'Kings Packaging ERP',
            'address' => '123 Business Street, Colombo, Sri Lanka',
            'email' => 'info@kingspackaging.com',
            'fy_start' => Carbon::create(Carbon::now()->year, 1, 1),
            'fy_end' => Carbon::create(Carbon::now()->year, 12, 31),
            'currency_symbol' => 'Rs',
            'currency_format' => '1,234.56',
            'decimal_places' => 2,
            'date_format' => 'd/m/Y',
            'timezone' => 'Asia/Colombo',
            'manage_inventory' => true,
            'account_locked' => false,
            'email_use_default' => true,
            'email_protocol' => 'smtp',
            'email_host' => 'smtp.gmail.com',
            'email_port' => 587,
            'email_tls' => true,
            'email_username' => '',
            'email_password' => '',
            'email_from' => 'noreply@kingspackaging.com',
            'print_paper_height' => 297.000,
            'print_paper_width' => 210.000,
            'print_margin_top' => 10.000,
            'print_margin_bottom' => 10.000,
            'print_margin_left' => 10.000,
            'print_margin_right' => 10.000,
            'print_orientation' => 'P',
            'print_page_format' => 'A',
            'database_version' => 1,
        ]);
    }
}
