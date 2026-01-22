<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tax extends Model
{
    protected $fillable = [
        'description',
        'abbreviation',
        'percentage',
        'reverse_calculation',
        'tax_label',
        'status',
    ];

    protected $casts = [
        'percentage' => 'decimal:4',
        'reverse_calculation' => 'decimal:4',
        'status' => 'integer',
    ];

    /**
     * Get the customers that have this tax.
     */
    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'customer_tax')
                    ->withTimestamps();
    }
}
