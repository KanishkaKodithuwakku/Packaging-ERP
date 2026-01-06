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
     * Get the job order through GRN -> Purchase Order Item -> Job Order
     * OR through GRN -> Production Order -> Job Order
     * Since purchase orders can have multiple job orders through items,
     * we need to find the specific job order via the GRN item's purchase order item
     */
    public function getJobOrder()
    {
        if ($this->related_doc_type === 'GRN' && $this->grn) {
            // Check if GRN is from Production Order (for Finished Goods)
            if ($this->grn->productionOrder && $this->grn->productionOrder->jobOrder) {
                return $this->grn->productionOrder->jobOrder;
            }
            
            // Check if GRN is from Purchase Order (for Raw Materials)
            if ($this->grn->purchaseOrder) {
                // Find the GRN item that matches this transaction's item_code (material_code)
                $grnItem = $this->grn->items->firstWhere('material_code', $this->item_code);
                if ($grnItem) {
                    // Get purchase order item that matches this GRN item
                    $poItem = $this->grn->purchaseOrder->items->first(function($poItem) use ($grnItem) {
                        return $poItem->item_type === $grnItem->item_type && 
                               $poItem->item_id === $grnItem->item_id;
                    });
                    if ($poItem && $poItem->jobOrder) {
                        return $poItem->jobOrder;
                    }
                }
                
                // Fallback: try to get job order from purchase order's direct relationship (for backward compatibility)
                if ($this->grn->purchaseOrder->jobOrder) {
                    return $this->grn->purchaseOrder->jobOrder;
                }
            }
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
