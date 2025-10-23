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
        'supplier_id',
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
     * Get the supplier for this box.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
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
        // Keep original dimensions in their original unit
        $dimensions = [
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height
        ];
        
        if ($this->dimension_type === 'INTERNAL') {
            // Convert adjustment to the same unit as input
            $adjustment = $this->getPlyAdjustmentInOriginalUnit();
            
            $dimensions['length'] += $adjustment;
            $dimensions['width'] += $adjustment;
            $dimensions['height'] += $adjustment;
        }

        return $dimensions;
    }

    /**
     * Calculate reel size (WIDTH) - Always returns INCHES
     */
    public function calculateReelSize($supplierId = null): float
    {
        $dimensions = $this->applyPlyAdjustments();
        
        // Convert dimensions to inches based on input unit
        $widthInInches = $this->convertToInches($dimensions['width']);
        $heightInInches = $this->convertToInches($dimensions['height']);
        
        // Formula: (W + H) in inches
        $reelSize = $widthInInches + $heightInInches;
        
        // Add 0.75 waste (in inches)
        $reelSize += 0.75;
        
        // Always apply standard rounding logic for reel sizes
        return $this->roundToNextReelSize($reelSize, $supplierId);
    }

    /**
     * Calculate cut size (LENTH) - Always returns INCHES
     * Note: Cut size does NOT use ply adjustments
     */
    public function calculateCutSize(): float
    {
        // Use original dimensions without ply adjustments
        $lengthInInches = $this->convertToInches($this->length);
        $widthInInches = $this->convertToInches($this->width);
        
        // Formula: ((L + W) × 2) in inches
        $cutSize = ($lengthInInches + $widthInInches) * 2;
        
        if ($this->dimension_type === 'EXTERNAL') {
            // Add 2 inches for external
            $cutSize += 2;
        } else {
            // Add 2.5 inches for internal
            $cutSize += 2.5;
        }

        return $cutSize;
    }

    /**
     * Round to next available reel size from supplier
     */
    private function roundToNextReelSize(float $size, $supplierId = null): float
    {
        // Standard rounding logic based on Excel sheet
        // Round to next available reel size: 13.50, 15.00, 17.00, 19.00, 21.00, 23.00, 25.00, 27.00, 29.00, 31.00, 33.00, 35.00, etc.
        
        if ($size <= 13.50) {
            return 13.50;
        } elseif ($size <= 15.00) {
            return 15.00;
        } elseif ($size <= 17.00) {
            return 17.00;
        } elseif ($size <= 19.00) {
            return 19.00;
        } elseif ($size <= 21.00) {
            return 21.00;
        } elseif ($size <= 23.00) {
            return 23.00;
        } elseif ($size <= 25.00) {
            return 25.00;
        } elseif ($size <= 27.00) {
            return 27.00;
        } elseif ($size <= 29.00) {
            return 29.00;
        } elseif ($size <= 31.00) {
            return 31.00;
        } elseif ($size <= 33.00) {
            return 33.00;
        } elseif ($size <= 35.00) {
            return 35.00;
        } elseif ($size <= 37.00) {
            return 37.00;
        } elseif ($size <= 39.00) {
            return 39.00;
        } elseif ($size <= 41.00) {
            return 41.00;
        } elseif ($size <= 43.00) {
            return 43.00;
        } elseif ($size <= 45.00) {
            return 45.00;
        } elseif ($size <= 47.00) {
            return 47.00;
        } elseif ($size <= 49.00) {
            return 49.00;
        } else {
            // For sizes above 49, round to next 2-inch increment
            return ceil($size / 2) * 2;
        }
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
        $this->reel_size = (string) $this->calculateReelSize();
        $this->cut_size = (string) $this->calculateCutSize();
        $this->board_qty = $this->calculateBoardQty();
    }

    /**
     * Convert dimension to inches based on input unit
     */
    private function convertToInches(float $dimension): float
    {
        switch ($this->unit) {
            case 'MM':
                return $dimension / 25.4; // MM to inches
            case 'CM':
                return $dimension / 2.54; // CM to inches
            case 'INCHES':
            default:
                return $dimension; // Already in inches
        }
    }

    /**
     * Get ply adjustment in the original unit
     */
    private function getPlyAdjustmentInOriginalUnit(): float
    {
        // Base adjustment in inches
        $adjustmentInInches = match($this->ply) {
            '3' => 0.125, // 1/8 inch
            '5' => 0.25,  // 1/4 inch
            '7' => 0.5,   // 1/2 inch
            default => 0
        };

        // Convert to original unit
        switch ($this->unit) {
            case 'MM':
                return $adjustmentInInches * 25.4; // Convert inches to MM
            case 'CM':
                return $adjustmentInInches * 2.54; // Convert inches to CM
            case 'INCHES':
            default:
                return $adjustmentInInches; // Already in inches
        }
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