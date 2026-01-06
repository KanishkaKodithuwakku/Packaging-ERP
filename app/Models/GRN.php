<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GRN extends Model
{
    protected $table = 'grns';
    
    protected $fillable = [
        'supplier_po_id',
        'production_order_id',
        'purchase_order_id',
        'supplier_id',
        'po_reference',
        'grn_no',
        'lot_code',
        'received_date',
        'notes',
        'status',
        'processed_at',
        'total_value',
    ];

    protected $casts = [
        'received_date' => 'date',
        'processed_at' => 'datetime',
        'total_value' => 'decimal:2',
    ];

    public function supplierOrder(): BelongsTo
    {
        return $this->belongsTo(SupplierOrder::class, 'supplier_po_id');
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(Inventory::class, 'lot_code', 'lot_code');
    }

    public function productionOrder(): BelongsTo
    {
        return $this->belongsTo(ProductionOrder::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Check if this GRN is from production order
     */
    public function isFromProductionOrder(): bool
    {
        return !is_null($this->production_order_id);
    }

    /**
     * Check if this GRN is from purchase order
     */
    public function isFromPurchaseOrder(): bool
    {
        return !is_null($this->purchase_order_id);
    }

    /**
     * Get the GRN items for this GRN
     */
    public function items(): HasMany
    {
        return $this->hasMany(GRNItem::class, 'grn_id');
    }

    /**
     * Get total quantity from items
     * For production GRNs, convert board quantity to FG quantity
     */
    public function getTotalQuantity(): float
    {
        $total = 0;
        foreach ($this->items as $item) {
            $qty = $item->qty_received_partial ?? $item->qty_received ?? 0;
            
            // For production GRNs, convert board quantity to FG quantity
            if ($this->isFromProductionOrder() && $item->productionOrderItem) {
                $noOfUps = $item->productionOrderItem->getNoOfUps();
                if ($noOfUps > 0) {
                    $qty = $qty * $noOfUps;
                }
            }
            
            $total += $qty;
        }
        return $total;
    }

    /**
     * Get total items count
     */
    public function getItemsCount(): int
    {
        return $this->items()->count();
    }

    /**
     * Get total processed quantity
     * For production GRNs, convert board quantity to FG quantity
     */
    public function getTotalProcessedQuantity(): float
    {
        $total = 0;
        foreach ($this->items as $item) {
            $qty = $item->qty_processed ?? 0;
            
            // For production GRNs, convert board quantity to FG quantity
            if ($this->isFromProductionOrder() && $item->productionOrderItem) {
                $noOfUps = $item->productionOrderItem->getNoOfUps();
                if ($noOfUps > 0) {
                    $qty = $qty * $noOfUps;
                }
            }
            
            $total += $qty;
        }
        return $total;
    }

    /**
     * Get total remaining quantity
     */
    public function getTotalRemainingQuantity(): float
    {
        return $this->items()->sum('qty_remaining') ?? 0;
    }

    /**
     * Get total expected quantity across all items
     */
    public function getTotalExpectedQuantity(): float
    {
        return $this->items()->sum('qty_expected') ?? 0;
    }

    /**
     * Get total partially received quantity
     */
    public function getTotalPartiallyReceivedQuantity(): float
    {
        return $this->items()->sum('qty_received_partial') ?? 0;
    }

    /**
     * Get total pending quantity to receive
     */
    public function getTotalPendingQuantity(): float
    {
        return $this->items()->sum('qty_pending') ?? 0;
    }

    /**
     * Check if GRN is fully received (all items are fully received)
     */
    public function isFullyReceived(): bool
    {
        $totalItems = $this->items()->count();
        // If no items, cannot be fully received
        if ($totalItems === 0) {
            return false;
        }
        // Check if all items are fully received
        return $this->items()->where('is_fully_received', false)->count() === 0;
    }

    /**
     * Check if GRN has any partial receiving
     */
    public function hasPartialReceiving(): bool
    {
        return $this->items()->where('qty_received_partial', '>', 0)->count() > 0;
    }

    /**
     * Get overall receiving percentage
     */
    public function getReceivingPercentage(): float
    {
        $totalExpected = $this->getTotalExpectedQuantity();
        if ($totalExpected <= 0) {
            return 0;
        }
        return ($this->getTotalPartiallyReceivedQuantity() / $totalExpected) * 100;
    }

    /**
     * Check if GRN can be closed (fully received)
     */
    public function canBeClosed(): bool
    {
        return $this->isFullyReceived();
    }

    /**
     * Check if GRN has items that can still be processed (not fully processed)
     */
    public function hasUnprocessedItems(): bool
    {
        foreach ($this->items as $item) {
            $qtyReceived = $item->qty_received_partial ?? $item->qty_received ?? 0;
            $qtyProcessed = $item->qty_processed ?? 0;
            $remaining = $qtyReceived - $qtyProcessed;
            if ($remaining > 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if GRN is fully processed (all received items have been processed)
     */
    public function isFullyProcessed(): bool
    {
        return !$this->hasUnprocessedItems();
    }

    /**
     * Get balance quantity (remaining to be processed)
     */
    public function getBalanceQuantity(): float
    {
        return $this->getTotalRemainingQuantity();
    }

    /**
     * Check if this is a multi-item GRN
     * Multi-item GRNs don't end with B (Box) or D (Divider)
     */
    public function isMultiItemGRN(): bool
    {
        $lotCode = $this->lot_code;
        return !preg_match('/-[BD]$/', $lotCode);
    }
}
