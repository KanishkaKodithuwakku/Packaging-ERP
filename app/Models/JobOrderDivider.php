<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobOrderDivider extends Model
{
    protected $fillable = [
        'job_order_id',
        'combination_1',
        'combination_2',
        'combination_3',
        'combination_4',
        'combination_5',
        'combination_6',
        'combination_7',
        'ply',
        'quantity',
        'unit',
        'fsc_claim',
        'supplier_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'supplier_price' => 'decimal:2',
    ];

    /**
     * Get the job order that owns the divider.
     */
    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class, 'job_order_id');
    }

    /**
     * Get combination as array
     */
    public function getCombinationArray(): array
    {
        $combination = [];
        for ($i = 1; $i <= 7; $i++) {
            $field = "combination_{$i}";
            if ($this->$field) {
                $combination[] = $this->$field;
            }
        }
        return $combination;
    }

    /**
     * Get ply-based parameter count
     */
    public function getParameterCount(): int
    {
        return match($this->ply) {
            '2' => 3,
            '5' => 5,
            '7' => 7,
            default => 3
        };
    }
}