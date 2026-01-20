<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GRNProcessingBatch extends Model
{
    protected $table = 'grn_processing_batches';
    
    protected $fillable = [
        'grn_id',
        'processed_by',
        'processed_at',
        'total_value',
        'notes',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'total_value' => 'decimal:2',
    ];

    public function grn(): BelongsTo
    {
        return $this->belongsTo(GRN::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function itemBatches(): HasMany
    {
        return $this->hasMany(GRNItemProcessingBatch::class, 'batch_id');
    }
}
