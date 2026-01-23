<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'delivery_note_id',
        'customer_id',
        'job_order_id',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'status',
        'notes',
        'terms',
        'confirmed_at',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'confirmed_at' => 'datetime',
    ];

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }

    public static function generateInvoiceNumber(): string
    {
        $lastInvoice = self::orderBy('id', 'desc')->first();
        $nextNumber = $lastInvoice ? (intval(substr($lastInvoice->invoice_number, 3)) + 1) : 1;
        return 'INV' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public function deliveryNote(): BelongsTo
    {
        return $this->belongsTo(DeliveryNote::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function jobOrder(): BelongsTo
    {
        return $this->belongsTo(JobOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    /**
     * Net amount before taxes (subtotal - discount).
     */
    public function getNetAmount(): float
    {
        return (float) $this->subtotal - (float) $this->discount_amount;
    }

    /**
     * Build tax lines for display and calculations.
     *
     * NOTE: Taxes are applied sequentially (compounded) in a stable order:
     * SSCL first, VAT last, others in-between.
     *
     * @param  float  $netAmount  Amount AFTER discount and BEFORE any taxes
     * @param  iterable<\App\Models\Tax>  $taxes
     * @return array<int, array{label:string, abbreviation:?string, percentage:float, amount:float}>
     */
    public static function buildTaxLines(float $netAmount, iterable $taxes): array
    {
        $taxesCollection = $taxes instanceof Collection ? $taxes : collect($taxes);

        $sorted = $taxesCollection
            ->filter(fn ($tax) => (int) ($tax->status ?? 1) === 1)
            ->sortBy(function ($tax) {
                $abbr = strtoupper((string) ($tax->abbreviation ?? ''));
                return match ($abbr) {
                    'SSCL' => 0,
                    'VAT' => 20,
                    default => 10,
                };
            })
            ->values();

        $runningBase = max(0, (float) $netAmount);
        $lines = [];

        foreach ($sorted as $tax) {
            $percentage = (float) ($tax->percentage ?? 0);
            if ($percentage <= 0) {
                continue;
            }

            $amount = round($runningBase * ($percentage / 100), 2);
            if ($amount == 0.0) {
                continue;
            }

            $label = trim((string) ($tax->tax_label ?? ''));
            if ($label === '') {
                $abbr = strtoupper((string) ($tax->abbreviation ?? ''));
                $label = $abbr === 'VAT' ? 'Tax' : ($tax->abbreviation ?? 'Tax');
            }

            $lines[] = [
                'label' => $label,
                'abbreviation' => $tax->abbreviation ?? null,
                'percentage' => $percentage,
                'amount' => $amount,
            ];

            // Compound taxes by adding current tax amount to the base for next taxes.
            $runningBase += $amount;
        }

        return $lines;
    }

    /**
     * Sum tax amount from tax lines.
     *
     * @param  array<int, array{amount:float}>  $taxLines
     */
    public static function sumTaxLines(array $taxLines): float
    {
        return round(collect($taxLines)->sum(fn ($l) => (float) ($l['amount'] ?? 0)), 2);
    }

    /**
     * Tax lines for this invoice based on current customer tax configuration.
     */
    public function getTaxLines(): array
    {
        $this->loadMissing('customer.taxes');

        $taxes = $this->customer?->taxes ?? collect();
        if ($taxes->isEmpty()) {
            return [];
        }

        // Check customer type
        $customerType = $this->customer?->customer_type ?? 'non_tax_customer';
        $isNonTaxCustomerWithTax = ($customerType === 'non_tax_customer') && $taxes->isNotEmpty();

        // For Non Tax Customer + With Tax: VAT is included in unit prices, don't show as separate line.
        if ($isNonTaxCustomerWithTax) {
            return [];
        }

        // For Tax Customers: calculate all taxes using buildTaxLines (supports multiple taxes)
        return self::buildTaxLines($this->getNetAmount(), $taxes);
    }
}
