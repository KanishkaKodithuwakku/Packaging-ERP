<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GRNItemProcessingBatch extends Model
{
    protected $table = 'grn_item_processing_batches';
    
    protected $fillable = [
        'batch_id',
        'grn_item_id',
        'quantity_processed',
        'unit_cost',
        'total_cost',
        'lot_code',
    ];

    protected $casts = [
        'quantity_processed' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:2',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(GRNProcessingBatch::class, 'batch_id');
    }

    public function grnItem(): BelongsTo
    {
        return $this->belongsTo(GRNItem::class, 'grn_item_id');
    }
}
