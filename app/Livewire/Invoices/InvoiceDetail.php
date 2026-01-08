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
            'customer',
            'deliveryNote',
            'jobOrder',
            'items'
        ])->findOrFail($id);

        // Initialize item prices for editing
        foreach ($this->invoice->items as $item) {
            $this->itemPrices[$item->id] = $item->unit_price;
        }
    }

    public function updatedItemPrices($value, $key)
    {
        // Validate and update unit price
        $this->itemPrices[$key] = max(0, (float) $value);
    }

    public function getCalculatedSubtotalProperty()
    {
        $subtotal = 0;
        foreach ($this->invoice->items as $item) {
            $currentPrice = $this->itemPrices[$item->id] ?? $item->unit_price;
            $subtotal += $currentPrice * (float) $item->quantity;
        }
        return $subtotal;
    }

    public function getCalculatedTotalProperty()
    {
        return $this->calculatedSubtotal + $this->invoice->tax_amount - $this->invoice->discount_amount;
    }

    public function saveInvoice()
    {
        if ($this->invoice->isConfirmed()) {
            session()->flash('error', 'Cannot edit confirmed invoice.');
            return;
        }

        try {
            $subtotal = 0;

            foreach ($this->invoice->items as $item) {
                $newUnitPrice = $this->itemPrices[$item->id] ?? $item->unit_price;
                $newLineTotal = $newUnitPrice * (float) $item->quantity;

                $item->update([
                    'unit_price' => $newUnitPrice,
                    'line_total' => $newLineTotal,
                ]);

                $subtotal += $newLineTotal;
            }

            // Update invoice totals
            $this->invoice->update([
                'subtotal' => $subtotal,
                'total_amount' => $subtotal + $this->invoice->tax_amount - $this->invoice->discount_amount,
            ]);

            // Refresh invoice
            $this->invoice->refresh();
            $this->invoice->load('items');

            // Update item prices array with saved values
            foreach ($this->invoice->items as $item) {
                $this->itemPrices[$item->id] = $item->unit_price;
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
