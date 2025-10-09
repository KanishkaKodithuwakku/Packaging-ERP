<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobOrder extends Model
{
    protected $fillable = [
        'customer_order_id',
        'jo_no',
        'item_desc',
        'size_mm',
        'ply',
        'qty_to_make',
        'status',
        // Header Section
        'order_date',
        'supplier_id',
        'supplier_po_ref',
        'customer_po_no',
        // Order Details
        'order_qty',
        'selling_price',
        'activity',
        'finishing_type',
        // Box Specification
        'box_length_cm',
        'box_width_cm',
        'box_height_cm',
        'top_liner',
        'combination',
        'flute',
        'sheet_width',
        'sheet_length',
        'no_of_ups',
        'board_qty',
        // Printing Details
        'printing_instruction',
        'no_of_colours',
        'sample_available',
        'fsc_claim',
        'notes',
    ];

    protected $casts = [
        'qty_to_make' => 'decimal:2',
        'order_date' => 'date',
        'order_qty' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'box_length_cm' => 'decimal:2',
        'box_width_cm' => 'decimal:2',
        'box_height_cm' => 'decimal:2',
        'sheet_width' => 'decimal:2',
        'sheet_length' => 'decimal:2',
        'board_qty' => 'decimal:2',
        'no_of_ups' => 'integer',
        'no_of_colours' => 'integer',
    ];

    public function customerOrder(): BelongsTo
    {
        return $this->belongsTo(CustomerOrder::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function materialRequests(): HasMany
    {
        return $this->hasMany(MaterialRequest::class);
    }
}
