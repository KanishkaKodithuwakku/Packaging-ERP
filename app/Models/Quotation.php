<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    protected $fillable = [
        'customer_id',
        'qt_no',
        'item_desc',
        'size_mm',
        'ply',
        'flute_type',
        'gsm_layers',
        'qty_requested',
        'raw_material_cost',
        'production_cost',
        'total_cost',
        'selling_price',
        'profit_margin',
        'status',
        'valid_until',
        'notes',
    ];

    protected $casts = [
        'gsm_layers' => 'array',
        'qty_requested' => 'decimal:2',
        'raw_material_cost' => 'decimal:2',
        'production_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'profit_margin' => 'decimal:2',
        'valid_until' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function customerOrders(): HasMany
    {
        return $this->hasMany(CustomerOrder::class);
    }

    public function calculateCosts(): void
    {
        // Calculate raw material cost based on GSM layers and quantity
        $rawMaterialCost = $this->calculateRawMaterialCost();
        $this->raw_material_cost = $rawMaterialCost;

        // Calculate production cost (simplified calculation)
        $productionCost = $this->qty_requested * 0.5; // $0.5 per unit
        $this->production_cost = $productionCost;

        // Calculate total cost
        $this->total_cost = $rawMaterialCost + $productionCost;

        // Calculate selling price with profit margin
        $profitMargin = $this->profit_margin ?? 20; // Default 20%
        $this->selling_price = $this->total_cost * (1 + ($profitMargin / 100));
    }

    private function calculateRawMaterialCost(): float
    {
        // Simplified calculation based on GSM layers
        $baseCostPerKg = 0.8; // Base cost per kg
        $totalGsm = array_sum($this->gsm_layers);
        $paperWeight = ($totalGsm * $this->size_mm * $this->ply) / 1000000; // Weight in kg per unit
        return $paperWeight * $this->qty_requested * $baseCostPerKg;
    }

    public function isExpired(): bool
    {
        return $this->valid_until && $this->valid_until < now();
    }

    public function canBeAccepted(): bool
    {
        return $this->status === 'sent' && !$this->isExpired();
    }

    public function accept(): void
    {
        if ($this->canBeAccepted()) {
            $this->update(['status' => 'accepted']);
        }
    }

    public function reject(): void
    {
        if ($this->status === 'sent') {
            $this->update(['status' => 'rejected']);
        }
    }
}
