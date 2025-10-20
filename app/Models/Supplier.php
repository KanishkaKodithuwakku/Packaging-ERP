<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $fillable = [
        'code',
        'name',
        'address',
        'contact_person',
        'phone',
        'email',
        'currency',
        'display_format',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the job orders for the supplier.
     */
    public function jobOrders(): HasMany
    {
        return $this->hasMany(JobOrder::class);
    }

    /**
     * Get the purchase orders for the supplier.
     */
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    /**
     * Get the production orders for the supplier.
     */
    public function productionOrders(): HasMany
    {
        return $this->hasMany(ProductionOrder::class);
    }

    /**
     * Get the reel sizes for the supplier.
     */
    public function reelSizes(): HasMany
    {
        return $this->hasMany(SupplierReelSize::class);
    }

    /**
     * Get available reel sizes as array
     */
    public function getReelSizesArray(): array
    {
        return $this->reelSizes()->orderBy('reel_size')->pluck('reel_size')->toArray();
    }

    /**
     * Check if supplier uses reel format display
     */
    public function usesReelFormat(): bool
    {
        return $this->display_format === 'reel';
    }

    /**
     * Check if supplier uses dimension format display
     */
    public function usesDimensionFormat(): bool
    {
        return $this->display_format === 'dimensions';
    }
}