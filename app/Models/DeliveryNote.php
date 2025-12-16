<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DeliveryNote extends Model
{
    protected $fillable = [
        'dn_number',
        'job_order_id',
        'dispatch_date',
        'status',
        'delivery_address',
        'notes',
    ];

    protected $casts = [
        'dispatch_date' => 'date',
    ];

    public static function generateDnNumber(): string
    {
        $lastDN = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastDN ? (intval(substr($lastDN->dn_number, 3)) + 1) : 1;
        return 'DN-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DeliveryNoteItem::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
