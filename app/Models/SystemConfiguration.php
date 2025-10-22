<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemConfiguration extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'category',
    ];

    protected $casts = [
        'value' => 'string',
    ];

    /**
     * Get a configuration value by key
     */
    public static function getValue(string $key, $default = null)
    {
        $config = static::where('key', $key)->first();
        
        if (!$config) {
            return $default;
        }

        return match ($config->type) {
            'boolean' => filter_var($config->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $config->value,
            'json' => json_decode($config->value, true),
            default => $config->value,
        };
    }

    /**
     * Set a configuration value
     */
    public static function setValue(string $key, $value, string $type = 'string', string $description = null, string $category = 'general')
    {
        $config = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'type' => $type,
                'description' => $description,
                'category' => $category,
            ]
        );

        return $config;
    }

    /**
     * Get all configurations by category
     */
    public static function getByCategory(string $category)
    {
        return static::where('category', $category)->get();
    }
}