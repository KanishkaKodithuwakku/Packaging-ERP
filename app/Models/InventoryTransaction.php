<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    protected $fillable = [
        'lot_code',
        'item_code',
        'category',
        'material_type',
        'txn_type',
        'qty',
        'uom',
        'warehouse',
        'related_doc_type',
        'related_doc_id',
        'delivery_note_item_id',
        'txn_date',
        'remarks',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'txn_date' => 'date',
    ];

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'lot_code', 'lot_code');
    }

    /**
     * Get the related GRN if this transaction is from a GRN
     */
    public function grn(): BelongsTo
    {
        return $this->belongsTo(GRN::class, 'related_doc_id');
    }

    /**
     * Get the job order through GRN -> Purchase Order -> Job Order
     */
    public function getJobOrder()
    {
        if ($this->related_doc_type === 'GRN' && $this->grn) {
            return $this->grn->purchaseOrder?->jobOrder;
        }
        return null;
    }

    /**
     * Get the delivery note item if this transaction is from a delivery note
     */
    public function deliveryNoteItem(): BelongsTo
    {
        return $this->belongsTo(DeliveryNoteItem::class);
    }

    /**
     * Get the delivery note if this transaction is from a delivery note
     */
    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class, 'related_doc_id');
    }
}
