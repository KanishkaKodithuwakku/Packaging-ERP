<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EntryType extends Model
{
    protected $table = 'entrytypes';  // Match Webzash table name
    
    protected $fillable = [
        'label',
        'name',
        'description',
        'base_type',
        'numbering',
        'prefix',
        'suffix',
        'zero_padding',
        'restriction_bankcash',
    ];

    protected $casts = [
        'base_type' => 'integer',
        'numbering' => 'integer',
        'zero_padding' => 'integer',
        'restriction_bankcash' => 'integer',
    ];

    const BASE_TYPE_JOURNAL = 0;
    const BASE_TYPE_RECEIPT = 1;
    const BASE_TYPE_PAYMENT = 2;
    const BASE_TYPE_CONTRA = 3;

    const NUMBERING_AUTO = 1;
    const NUMBERING_MANUAL = 2;

    const RESTRICTION_NONE = 1;
    const RESTRICTION_AT_LEAST_ONE = 2;
    const RESTRICTION_EXACTLY_ONE = 3;

    /**
     * Get entries of this type
     */
    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class, 'entrytype_id');
    }

    /**
     * Generate next entry number
     */
    public function generateNextNumber(): ?int
    {
        if ($this->numbering == self::NUMBERING_MANUAL) {
            return null;
        }

        $lastEntry = $this->entries()->orderBy('number', 'desc')->first();
        $nextNumber = $lastEntry ? $lastEntry->number + 1 : 1;

        return $nextNumber;
    }

    /**
     * Format entry number with prefix/suffix
     */
    public function formatNumber($number): string
    {
        if ($this->numbering == self::NUMBERING_MANUAL && !$number) {
            return '';
        }

        $formatted = $this->zero_padding > 0 
            ? str_pad($number, $this->zero_padding, '0', STR_PAD_LEFT)
            : $number;

        return ($this->prefix ?? '') . $formatted . ($this->suffix ?? '');
    }

    /**
     * Get base type name
     */
    public function getBaseTypeName(): string
    {
        return match($this->base_type) {
            self::BASE_TYPE_RECEIPT => 'Receipt',
            self::BASE_TYPE_PAYMENT => 'Payment',
            self::BASE_TYPE_CONTRA => 'Contra',
            default => 'Journal',
        };
    }
}
