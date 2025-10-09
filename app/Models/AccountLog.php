<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AccountLog extends Model
{
    protected $fillable = [
        'date',
        'level',
        'host_ip',
        'user',
        'url',
        'user_agent',
        'message',
    ];

    protected $casts = [
        'date' => 'datetime',
        'level' => 'integer',
    ];

    const LEVEL_INFO = 1;
    const LEVEL_WARNING = 2;
    const LEVEL_ERROR = 3;

    /**
     * Log an info message
     */
    public static function logInfo(string $message): void
    {
        self::createLog($message, self::LEVEL_INFO);
    }

    /**
     * Log a warning message
     */
    public static function logWarning(string $message): void
    {
        self::createLog($message, self::LEVEL_WARNING);
    }

    /**
     * Log an error message
     */
    public static function logError(string $message): void
    {
        self::createLog($message, self::LEVEL_ERROR);
    }

    /**
     * Create a log entry
     */
    protected static function createLog(string $message, int $level): void
    {
        self::create([
            'date' => now(),
            'level' => $level,
            'host_ip' => request()->ip(),
            'user' => Auth::user()->name ?? 'Guest',
            'url' => request()->fullUrl(),
            'user_agent' => request()->userAgent(),
            'message' => $message,
        ]);
    }

    /**
     * Get level name
     */
    public function getLevelNameAttribute(): string
    {
        return match($this->level) {
            self::LEVEL_WARNING => 'Warning',
            self::LEVEL_ERROR => 'Error',
            default => 'Info',
        };
    }

    /**
     * Scope for info logs
     */
    public function scopeInfo($query)
    {
        return $query->where('level', self::LEVEL_INFO);
    }

    /**
     * Scope for warning logs
     */
    public function scopeWarning($query)
    {
        return $query->where('level', self::LEVEL_WARNING);
    }

    /**
     * Scope for error logs
     */
    public function scopeError($query)
    {
        return $query->where('level', self::LEVEL_ERROR);
    }
}
