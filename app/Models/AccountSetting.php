<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountSetting extends Model
{
    protected $fillable = [
        'company_name',
        'address',
        'email',
        'fy_start',
        'fy_end',
        'currency_symbol',
        'currency_format',
        'decimal_places',
        'date_format',
        'timezone',
        'manage_inventory',
        'account_locked',
        'email_use_default',
        'email_protocol',
        'email_host',
        'email_port',
        'email_tls',
        'email_username',
        'email_password',
        'email_from',
        'print_paper_height',
        'print_paper_width',
        'print_margin_top',
        'print_margin_bottom',
        'print_margin_left',
        'print_margin_right',
        'print_orientation',
        'print_page_format',
        'database_version',
    ];

    protected $casts = [
        'fy_start' => 'date',
        'fy_end' => 'date',
        'decimal_places' => 'integer',
        'manage_inventory' => 'boolean',
        'account_locked' => 'boolean',
        'email_use_default' => 'boolean',
        'email_port' => 'integer',
        'email_tls' => 'boolean',
        'print_paper_height' => 'decimal:3',
        'print_paper_width' => 'decimal:3',
        'print_margin_top' => 'decimal:3',
        'print_margin_bottom' => 'decimal:3',
        'print_margin_left' => 'decimal:3',
        'print_margin_right' => 'decimal:3',
        'database_version' => 'integer',
    ];

    /**
     * Get the singleton instance
     */
    public static function getInstance(): self
    {
        $settings = self::first();
        
        if (!$settings) {
            $settings = self::create([
                'company_name' => 'Company Name',
                'fy_start' => now()->startOfYear(),
                'fy_end' => now()->endOfYear(),
                'currency_symbol' => '$',
                'currency_format' => '1,234.56',
                'decimal_places' => 2,
                'date_format' => 'Y-m-d',
                'timezone' => 'UTC',
            ]);
        }

        return $settings;
    }

    /**
     * Format amount based on settings
     */
    public function formatAmount($amount): string
    {
        $formatted = number_format($amount, $this->decimal_places);
        
        return $this->currency_symbol . ' ' . $formatted;
    }

    /**
     * Check if account is locked
     */
    public function isLocked(): bool
    {
        return $this->account_locked;
    }

    /**
     * Check if date is within financial year
     */
    public function isWithinFY($date): bool
    {
        $checkDate = is_string($date) ? \Carbon\Carbon::parse($date) : $date;
        
        return $checkDate->between($this->fy_start, $this->fy_end);
    }
}
