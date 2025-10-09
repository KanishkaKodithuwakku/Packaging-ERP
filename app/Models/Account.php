<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    // Define the fillable fields for mass assignment
    protected $fillable = [
        'account_name',
        'account_code',
        'account_type',
        'opening_balance',
        'description',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the user who created the account.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated the account.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the transactions for the account.
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'account_id');
    }

    /**
     * Get the current balance of the account, including opening balance and associated transactions.
     */
    public function balance()
    {
        // Add any business logic if needed to calculate current balance
        return $this->opening_balance + $this->transactions()->sum('debit') - $this->transactions()->sum('credit');
    }
}
