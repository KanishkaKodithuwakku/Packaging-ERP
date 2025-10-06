<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceipt extends Model
{
    protected $fillable = [
        'supplier_po_id',
        'grn_no',
        'lot_code',
        'material_code',
        'qty_received',
        'uom',
        'received_date',
    ];

    protected $casts = [
        'qty_received' => 'decimal:2',
        'received_date' => 'date',
    ];

    public function supplierOrder(): BelongsTo
    {
        return $this->belongsTo(SupplierOrder::class, 'supplier_po_id');
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'lot_code', 'lot_code');
    }
}
