<?php

namespace App\Helpers;

use App\Models\SystemConfiguration;
use Carbon\Carbon;

class DateFormatHelper
{
    /**
     * Get the configured date format
     */
    public static function getDateFormat(): string
    {
        return SystemConfiguration::getValue('date_format', 'd-m-Y');
    }

    /**
     * Format a date according to system configuration
     */
    public static function format($date, ?string $format = null): string
    {
        if (!$date) {
            return '';
        }

        $dateFormat = $format ?? self::getDateFormat();

        try {
            if ($date instanceof Carbon) {
                return $date->format($dateFormat);
            }

            if (is_string($date)) {
                return Carbon::parse($date)->format($dateFormat);
            }

            return '';
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * Format a date for display (alias for format)
     */
    public static function display($date): string
    {
        return self::format($date);
    }
}

