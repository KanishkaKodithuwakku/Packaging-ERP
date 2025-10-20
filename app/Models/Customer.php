<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'code',
        'name',
        'address',
        'contact_person',
        'phone',
        'email',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the job orders for the customer.
     */
    public function jobOrders(): HasMany
    {
        return $this->hasMany(JobOrder::class);
    }

    /**
     * Get the customer orders for the customer.
     */
    public function customerOrders(): HasMany
    {
        return $this->hasMany(CustomerOrder::class);
    }
}