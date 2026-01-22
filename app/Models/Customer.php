<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'website',
        'notes',
        'status',
        'contact_first_name',
        'contact_last_name',
        'contact_email',
        'contact_phone',
        'contact_mobile',
        'account_receivable',
        'sales_revenue',
        'currency',
        'tax',
        'bank',
        'credit_limit_period',
        'credit_limit_amount',
        'customer_type', // with_tax or without_tax
        'vat_number',
        'accept_discount',
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
    
    /**
     * Get the taxes assigned to this customer.
     */
    public function taxes(): BelongsToMany
    {
        return $this->belongsToMany(Tax::class, 'customer_tax')
                    ->withTimestamps();
    }
}