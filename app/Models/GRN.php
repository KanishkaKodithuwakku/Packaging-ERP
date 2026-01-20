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

    /**
     * Get the formatted GRN number
     * Ensures GRN number is always displayed in correct format
     */
    public function getFormattedGrnNoAttribute(): string
    {
        $grnNo = $this->grn_no;
        
        // If GRN number contains "GRN-LOT-" prefix incorrectly, extract just the GRN part
        if (strpos($grnNo, 'GRN-LOT-') === 0) {
            // Remove "GRN-LOT-" prefix
            $remaining = substr($grnNo, 8); // Length of "GRN-LOT-"
            
            // Try to extract the GRN number part (usually 6 digits)
            // Pattern: GRN-LOT-00000220260108-000001 -> extract 000002
            if (preg_match('/^([0-9]{6})/', $remaining, $matches)) {
                return 'GRN-' . $matches[1];
            }
            
            // Fallback: try to find any 6-digit number
            if (preg_match('/([0-9]{6})/', $remaining, $matches)) {
                return 'GRN-' . $matches[1];
            }
        }
        
        // If it already starts with GRN-, return as is (but ensure proper format)
        if (strpos($grnNo, 'GRN-') === 0) {
            // Check if it's in correct format GRN-XXXXXX
            if (preg_match('/^GRN-([0-9]+)$/', $grnNo)) {
                return $grnNo;
            }
            // If malformed, try to fix it
            $parts = explode('-', $grnNo);
            if (count($parts) >= 2 && isset($parts[1])) {
                // Extract first numeric part
                if (preg_match('/([0-9]+)/', $parts[1], $matches)) {
                    return 'GRN-' . str_pad($matches[1], 6, '0', STR_PAD_LEFT);
                }
            }
        }
        
        // If it starts with GRN but no dash, add dash
        if (strpos($grnNo, 'GRN') === 0 && strpos($grnNo, '-') === false) {
            $number = preg_replace('/[^0-9]/', '', substr($grnNo, 3));
            if ($number) {
                return 'GRN-' . str_pad($number, 6, '0', STR_PAD_LEFT);
            }
        }
        
        // Try to extract GRN number from any format
        if (preg_match('/([0-9]{4,6})/', $grnNo, $matches)) {
            return 'GRN-' . str_pad($matches[1], 6, '0', STR_PAD_LEFT);
        }
        
        // Default: return as is
        return $grnNo;
    }

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
     * Get processing batches for this GRN
     */
    public function processingBatches(): HasMany
    {
        return $this->hasMany(GRNProcessingBatch::class, 'grn_id');
    }

    /**
     * Get total quantity from items
     * For FG GRNs from production orders, qty_received already stores FG quantity (boxes), not board quantity
     * So we should NOT multiply by No of UPS
     */
    public function getTotalQuantity(): float
    {
        $total = 0;
        foreach ($this->items as $item) {
            $qty = $item->qty_received_partial ?? $item->qty_received ?? 0;
            // For FG GRNs, qty_received is already in finished goods (boxes) units
            // No conversion needed
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
     * For FG GRNs from production orders, qty_processed already stores FG quantity (boxes), not board quantity
     * So we should NOT multiply by No of UPS
     */
    public function getTotalProcessedQuantity(): float
    {
        $total = 0;
        foreach ($this->items as $item) {
            $qty = $item->qty_processed ?? 0;
            // For FG GRNs, qty_processed is already in finished goods (boxes) units
            // No conversion needed
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

    /**
     * Get currency symbol for this GRN
     * Gets currency from purchase order, supplier, or defaults to LKR
     */
    public function getCurrencySymbol(): string
    {
        // If GRN is from purchase order, use purchase order currency
        if ($this->purchaseOrder) {
            return $this->purchaseOrder->getCurrencySymbol();
        }
        
        // If GRN has a supplier, try to get currency from supplier
        if ($this->supplier && $this->supplier->currency) {
            $currencyCode = $this->supplier->currency;
            return match($currencyCode) {
                'LKR' => 'Rs.',
                'USD' => '$',
                'EUR' => '€',
                'GBP' => '£',
                'INR' => '₹',
                default => $currencyCode . ' '
            };
        }
        
        // Default to LKR (base currency)
        return 'Rs.';
    }
}
