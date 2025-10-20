<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobOrderBox extends Model
{
    protected $fillable = [
        'job_order_id',
        'order_qty',
        'selling_price',
        'activity',
        'printing_instruction',
        'no_of_colours',
        'stitched_glued',
        'sample_available',
        'sample_attached',
        'length',
        'width',
        'height',
        'unit',
        'dimension_type',
        'top_liner',
        'ply',
        'combination_1',
        'combination_2',
        'combination_3',
        'combination_4',
        'combination_5',
        'combination_6',
        'combination_7',
        'flute',
        'fsc_claim',
        'reel_size',
        'cut_size',
        'no_of_ups',
        'board_qty',
        'supplier_price',
    ];

    protected $casts = [
        'sample_available' => 'boolean',
        'sample_attached' => 'boolean',
        'length' => 'decimal:3',
        'width' => 'decimal:3',
        'height' => 'decimal:3',
        'reel_size' => 'decimal:3',
        'cut_size' => 'decimal:3',
        'selling_price' => 'decimal:2',
        'supplier_price' => 'decimal:2',
    ];

    /**
     * Get the job order that owns the box.
     */
    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class, 'job_order_id');
    }

    /**
     * Convert dimensions to CM if needed
     */
    public function convertToCm(): array
    {
        $length = $this->length;
        $width = $this->width;
        $height = $this->height;

        switch ($this->unit) {
            case 'MM':
                $length = $length / 10;
                $width = $width / 10;
                $height = $height / 10;
                break;
            case 'INCHES':
                $length = $length * 2.54;
                $width = $width * 2.54;
                $height = $height * 2.54;
                break;
        }

        return [
            'length' => $length,
            'width' => $width,
            'height' => $height
        ];
    }

    /**
     * Apply ply-based adjustments for internal dimensions
     */
    public function applyPlyAdjustments(): array
    {
        $dimensions = $this->convertToCm();
        
        if ($this->dimension_type === 'INTERNAL') {
            $adjustment = match($this->ply) {
                '3' => 0.3,
                '5' => 0.5,
                '7' => 1.0,
                default => 0
            };

            $dimensions['length'] += $adjustment;
            $dimensions['width'] += $adjustment;
            $dimensions['height'] += $adjustment;
        }

        return $dimensions;
    }

    /**
     * Calculate reel size (WIDTH)
     */
    public function calculateReelSize($supplierId = null): float
    {
        $dimensions = $this->applyPlyAdjustments();
        
        // Formula: (W + H) / 2.54
        $reelSize = ($dimensions['width'] + $dimensions['height']) / 2.54;
        
        // Add 0.75 waste
        $reelSize += 0.75;
        
        // Round to next available reel size from supplier
        return $this->roundToNextReelSize($reelSize, $supplierId);
    }

    /**
     * Calculate cut size (LENTH)
     */
    public function calculateCutSize(): float
    {
        $dimensions = $this->applyPlyAdjustments();
        
        if ($this->dimension_type === 'EXTERNAL') {
            // Formula: (((L + W) × 2) / 2.54) + 2
            $cutSize = ((($dimensions['length'] + $dimensions['width']) * 2) / 2.54) + 2;
        } else {
            // Formula: (((L + W) × 2) / 2.54) + 2.5
            $cutSize = ((($dimensions['length'] + $dimensions['width']) * 2) / 2.54) + 2.5;
        }

        return $cutSize;
    }

    /**
     * Round to next available reel size from supplier
     */
    private function roundToNextReelSize(float $size, $supplierId = null): float
    {
        // Get supplier from parameter or relationship
        if ($supplierId) {
            $supplier = Supplier::find($supplierId);
        } else {
            $supplier = $this->jobOrder->supplier;
        }
        
        if (!$supplier) {
            return $size; // Return original size if no supplier found
        }
        
        $reelSizes = $supplier->reelSizes()->orderBy('reel_size')->pluck('reel_size')->toArray();
        
        foreach ($reelSizes as $reelSize) {
            if ($reelSize >= $size) {
                return $reelSize;
            }
        }
        
        // If no reel size found, return the original size
        return $size;
    }

    /**
     * Calculate board quantity
     */
    public function calculateBoardQty(): int
    {
        if ($this->no_of_ups && $this->no_of_ups > 0) {
            return ceil($this->order_qty / $this->no_of_ups);
        }
        return 0;
    }

    /**
     * Auto-calculate all dimensions
     */
    public function calculateDimensions(): void
    {
        $this->reel_size = $this->calculateReelSize();
        $this->cut_size = $this->calculateCutSize();
        $this->board_qty = $this->calculateBoardQty();
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