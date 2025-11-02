<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class GRNItem extends Model
{
    protected $table = 'grn_items';
    
    protected $fillable = [
        'grn_id',
        'production_order_item_id',
        'item_type',
        'item_id',
        'description',
        'material_code',
        'qty_received',
        'qty_expected',
        'qty_received_partial',
        'qty_pending',
        'is_fully_received',
        'last_received_at',
        'qty_processed',
        'qty_remaining',
        'uom',
        'inventory_lot_code',
        'unit_cost',
        'total_cost',
        'processed_at',
    ];

    protected $casts = [
        'qty_received' => 'decimal:4',
        'qty_expected' => 'decimal:4',
        'qty_received_partial' => 'decimal:4',
        'qty_pending' => 'decimal:4',
        'is_fully_received' => 'boolean',
        'last_received_at' => 'datetime',
        'qty_processed' => 'decimal:4',
        'qty_remaining' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'total_cost' => 'decimal:4',
        'processed_at' => 'datetime',
    ];

    /**
     * Get the GRN that owns the item.
     */
    public function grn(): BelongsTo
    {
        return $this->belongsTo(GRN::class, 'grn_id');
    }

    /**
     * Get the production order item that owns this GRN item.
     */
    public function productionOrderItem(): BelongsTo
    {
        return $this->belongsTo(ProductionOrderItem::class);
    }

    /**
     * Get the related box or divider item
     */
    public function getItem()
    {
        if (!$this->item_id) {
            return null;
        }

        try {
            if ($this->item_type === 'box') {
                return JobOrderBox::find($this->item_id);
            } elseif ($this->item_type === 'divider') {
                return JobOrderDivider::find($this->item_id);
            }
        } catch (\Exception $e) {
            Log::warning('Error getting GRN item details', [
                'grn_item_id' => $this->id,
                'item_type' => $this->item_type,
                'item_id' => $this->item_id,
                'error' => $e->getMessage()
            ]);
        }
        
        return null;
    }

    /**
     * Initialize partial receiving fields when GRN item is created
     */
    public function initializePartialReceiving()
    {
        $this->qty_expected = $this->qty_received;
        $this->qty_received_partial = 0;
        $this->qty_pending = $this->qty_received;
        $this->is_fully_received = false;
        $this->save();
    }

    /**
     * Add partial quantity received
     */
    public function addPartialReceiving($quantity, $notes = null)
    {
        if ($quantity <= 0) {
            throw new \Exception('Quantity must be greater than 0');
        }

        if ($this->qty_received_partial + $quantity > $this->qty_expected) {
            throw new \Exception('Cannot receive more than expected quantity');
        }

        $this->qty_received_partial += $quantity;
        $this->last_received_at = now();

        // Sync status and recalculate pending
        $this->syncReceivingStatus();

        $this->save();

        // Log the partial receiving
        Log::info('Partial GRN receiving', [
            'grn_item_id' => $this->id,
            'quantity_received' => $quantity,
            'total_received' => $this->qty_received_partial,
            'remaining' => $this->qty_pending,
            'is_fully_received' => $this->is_fully_received,
            'notes' => $notes
        ]);

        return $this;
    }

    /**
     * Sync receiving status based on current quantities
     * This ensures is_fully_received and qty_pending are always correct
     */
    public function syncReceivingStatus()
    {
        // Ensure we have valid values, default to 0 if null
        $qtyReceived = $this->qty_received_partial ?? 0;
        $qtyExpected = $this->qty_expected ?? ($this->qty_received ?? 0);
        
        // If qty_expected is 0 or null but qty_received exists, use that
        if (($qtyExpected <= 0) && ($this->qty_received > 0)) {
            $qtyExpected = $this->qty_received;
            $this->qty_expected = $qtyExpected;
        }
        
        // Use a small tolerance for floating point comparison
        $tolerance = 0.0001;
        $difference = abs($qtyReceived - $qtyExpected);
        
        // Check if fully received (accounting for floating point precision)
        if ($qtyExpected > 0 && ($qtyReceived >= $qtyExpected || $difference <= $tolerance)) {
            $this->is_fully_received = true;
            $this->qty_pending = 0;
            // Ensure received doesn't exceed expected
            if ($qtyReceived > $qtyExpected) {
                $this->qty_received_partial = $qtyExpected;
            }
        } else {
            $this->is_fully_received = false;
            $this->qty_pending = max(0, $qtyExpected - $qtyReceived);
        }
    }

    /**
     * Check if item is fully received
     */
    public function isFullyReceived(): bool
    {
        return $this->is_fully_received;
    }

    /**
     * Get remaining quantity to receive
     */
    public function getRemainingQuantity(): float
    {
        return $this->qty_pending;
    }

    /**
     * Get received percentage
     */
    public function getReceivedPercentage(): float
    {
        if ($this->qty_expected <= 0) {
            return 0;
        }
        return ($this->qty_received_partial / $this->qty_expected) * 100;
    }
}