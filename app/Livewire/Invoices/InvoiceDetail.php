<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Livewire\Component;
use Illuminate\Support\Facades\Log;

class InvoiceDetail extends Component
{
    protected $layout = 'components.layouts.app';

    public $invoice;
    public $itemPrices = [];

    protected $listeners = ['refreshInvoice' => '$refresh'];

    public function mount($id)
    {
        $this->invoice = Invoice::with([
            'customer.taxes',
            'deliveryNote',
            'jobOrder',
            'items'
        ])->findOrFail($id);

        // Initialize item prices for editing/display.
        // For Non Tax Customer + With Tax: show VAT-inclusive prices.
        // For Tax Customer: show base prices.
        $isNonTaxWithTax = $this->isNonTaxCustomerWithTax();
        $vatRate = $this->getVatRate();
        $vatFactor = $vatRate > 0 ? (1 + ($vatRate / 100)) : 1;
        
        foreach ($this->invoice->items as $item) {
            $basePrice = (float) $item->unit_price;
            // For Non Tax + With Tax: display VAT-inclusive price
            // For Tax Customer: display base price
            $this->itemPrices[$item->id] = $isNonTaxWithTax 
                ? ($basePrice * $vatFactor)
                : $basePrice;
        }
    }

    public function updatedItemPrices($value, $key)
    {
        // Validate and update displayed unit price (always base price, no VAT).
        $this->itemPrices[$key] = max(0, (float) $value);
    }

    /**
     * Check if customer has VAT (15%) tax assigned.
     */
    private function hasVat15Tax(): bool
    {
        $this->invoice->loadMissing('customer.taxes');
        $taxes = $this->invoice->customer?->taxes ?? collect();

        return $taxes->contains(function ($tax) {
            return strtoupper($tax->abbreviation) === 'VAT' && (float) $tax->percentage === 15.00;
        });
    }

    /**
     * Check if customer is a Tax Customer (not Non Tax Customer).
     */
    private function isTaxCustomer(): bool
    {
        $customerType = $this->invoice->customer?->customer_type ?? 'non_tax_customer';
        return $customerType === 'tax_customer';
    }

    /**
     * Check if customer is Non Tax Customer with tax enabled (has taxes assigned).
     */
    private function isNonTaxCustomerWithTax(): bool
    {
        $customerType = $this->invoice->customer?->customer_type ?? 'non_tax_customer';
        if ($customerType !== 'non_tax_customer') {
            return false;
        }
        
        $this->invoice->loadMissing('customer.taxes');
        return $this->invoice->customer?->taxes->isNotEmpty();
    }

    /**
     * Get VAT rate (15%) if customer has VAT tax.
     */
    private function getVatRate(): float
    {
        if (!$this->hasVat15Tax()) {
            return 0.0;
        }
        return 15.00;
    }

    public function getCalculatedSubtotalProperty()
    {
        // For Non Tax Customer + With Tax: subtotal includes VAT (from VAT-inclusive unit prices).
        // For Tax Customer: subtotal is base prices only (VAT shown separately).
        $subtotal = 0;
        $isNonTaxWithTax = $this->isNonTaxCustomerWithTax();
        
        foreach ($this->invoice->items as $item) {
            $displayUnitPrice = (float) ($this->itemPrices[$item->id] ?? $item->unit_price);
            $lineTotal = $displayUnitPrice * (float) $item->quantity;
            $subtotal += $lineTotal;
        }
        return $subtotal;
    }

    public function getCalculatedTaxAmountProperty()
    {
        $isNonTaxWithTax = $this->isNonTaxCustomerWithTax();
        
        // For Non Tax Customer + With Tax: VAT is already included in unit prices, so tax amount = 0 for display.
        if ($isNonTaxWithTax) {
            return 0;
        }
        
        // For Tax Customer: calculate VAT on subtotal (after discount).
        $this->invoice->loadMissing('customer.taxes');
        $taxes = $this->invoice->customer?->taxes ?? collect();
        $hasVatTax = $this->hasVat15Tax();
        
        if ($hasVatTax) {
            $vatRate = $this->getVatRate();
            $baseAmount = max(0, (float) $this->calculatedSubtotal - (float) $this->invoice->discount_amount);
            $taxAmount = $baseAmount * ($vatRate / 100);
            return $taxAmount;
        }
        
        // For other tax types, calculate tax on total subtotal
        $baseAmount = max(0, (float) $this->calculatedSubtotal - (float) $this->invoice->discount_amount);
        $taxLines = Invoice::buildTaxLines($baseAmount, $taxes);
        return Invoice::sumTaxLines($taxLines);
    }

    public function getCalculatedTotalProperty()
    {
        $isNonTaxWithTax = $this->isNonTaxCustomerWithTax();
        $discount = (float) $this->invoice->discount_amount;
        
        // For Non Tax Customer + With Tax: Total = Subtotal - Discount (VAT already included in subtotal).
        // For Tax Customer: Total = Subtotal - Discount + Tax
        if ($isNonTaxWithTax) {
            return max(0, (float) $this->calculatedSubtotal - $discount);
        }
        
        return max(0, (float) $this->calculatedSubtotal - $discount) + (float) $this->calculatedTaxAmount;
    }

    public function saveInvoice()
    {
        if ($this->invoice->isConfirmed()) {
            session()->flash('error', 'Cannot edit confirmed invoice.');
            return;
        }

        try {
            $this->invoice->loadMissing('customer.taxes');
            $taxes = $this->invoice->customer?->taxes ?? collect();
            $isNonTaxWithTax = $this->isNonTaxCustomerWithTax();
            $isTaxCustomer = $this->isTaxCustomer();
            $hasVatTax = $this->hasVat15Tax();
            $vatRate = $this->getVatRate();
            $vatFactor = $vatRate > 0 ? (1 + ($vatRate / 100)) : 1;
            
            $subtotal = 0;

            foreach ($this->invoice->items as $item) {
                $displayUnitPrice = (float) ($this->itemPrices[$item->id] ?? $item->unit_price);
                
                if ($isNonTaxWithTax) {
                    // For Non Tax + With Tax: displayed price is VAT-inclusive.
                    // Store base price in DB, but line_total includes VAT.
                    $baseUnitPrice = $vatFactor > 0 ? ($displayUnitPrice / $vatFactor) : $displayUnitPrice;
                    $lineTotal = $displayUnitPrice * (float) $item->quantity; // VAT-inclusive line total
                    
                    $item->update([
                        'unit_price' => $baseUnitPrice, // Store base price
                        'line_total' => $lineTotal, // Store VAT-inclusive line total
                    ]);
                } else {
                    // For Tax Customer: displayed price is base price.
                    $baseUnitPrice = $displayUnitPrice;
                    $lineTotal = $baseUnitPrice * (float) $item->quantity;
                    
                    $item->update([
                        'unit_price' => $baseUnitPrice,
                        'line_total' => $lineTotal,
                    ]);
                }

                $subtotal += $lineTotal;
            }

            // Update invoice totals
            $discountAmount = (float) $this->invoice->discount_amount;
            $netAmount = max(0, (float) $subtotal - $discountAmount);
            
            $taxAmount = 0;
            if ($isNonTaxWithTax) {
                // For Non Tax + With Tax: VAT is already in subtotal, so tax_amount = 0.
                $taxAmount = 0;
            } elseif ($hasVatTax && $isTaxCustomer) {
                // For Tax Customer: calculate VAT on subtotal (after discount).
                $taxAmount = $netAmount * ($vatRate / 100);
            } else {
                $taxLines = Invoice::buildTaxLines($netAmount, $taxes);
                $taxAmount = Invoice::sumTaxLines($taxLines);
            }

            // Total calculation
            if ($isNonTaxWithTax) {
                // VAT already included in subtotal
                $totalAmount = $netAmount;
            } else {
                $totalAmount = $netAmount + $taxAmount;
            }

            $this->invoice->update([
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
            ]);

            // Refresh invoice
            $this->invoice->refresh();
            $this->invoice->load('items');

            // Update item prices array with saved values
            foreach ($this->invoice->items as $item) {
                $basePrice = (float) $item->unit_price;
                $this->itemPrices[$item->id] = $isNonTaxWithTax 
                    ? ($basePrice * $vatFactor)
                    : $basePrice;
            }

            session()->flash('success', 'Invoice saved successfully.');
        } catch (\Exception $e) {
            Log::error('Error saving invoice: ' . $e->getMessage());
            session()->flash('error', 'Error saving invoice: ' . $e->getMessage());
        }
    }


    public function confirmInvoice()
    {
        if ($this->invoice->isConfirmed()) {
            session()->flash('error', 'Invoice is already confirmed.');
            $this->dispatch('closeConfirmModal');
            return;
        }

        try {
            // Save any pending changes first
            $this->saveInvoice();

            // Confirm the invoice
            $this->invoice->update([
                'confirmed_at' => now(),
                'status' => 'sent', // Change status from draft to sent when confirmed
            ]);

            // Close modal via JavaScript
            $this->dispatch('closeConfirmModal');

            // Refresh invoice data
            $this->invoice = Invoice::with([
                'customer',
                'deliveryNote',
                'jobOrder',
                'items'
            ])->findOrFail($this->invoice->id);

            session()->flash('success', 'Invoice confirmed successfully. Invoice is now locked and cannot be edited.');
        } catch (\Exception $e) {
            Log::error('Error confirming invoice: ' . $e->getMessage());
            session()->flash('error', 'Error confirming invoice: ' . $e->getMessage());
            $this->dispatch('closeConfirmModal');
        }
    }

    public function render()
    {
        return view('livewire.invoices.invoice-detail');
    }
}
