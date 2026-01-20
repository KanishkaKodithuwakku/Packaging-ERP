<?php

namespace App\Livewire\Invoices;

use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceManagement extends Component
{
    use WithPagination;

    protected $layout = 'components.layouts.app';

    public $search = '';
    public $showSuggestions = false;

    public function updatingSearch()
    {
        $this->resetPage();
        if (!empty($this->search)) {
            $this->showSuggestions = true;
        }
    }

    public function showDropdown()
    {
        if (!empty($this->search)) {
            $this->showSuggestions = true;
        }
    }

    public function hideDropdown()
    {
        // Delay hiding to allow clicks on suggestions
        $this->dispatch('hide-dropdown-delayed');
    }

    public function selectSuggestion($suggestion)
    {
        $this->search = $suggestion;
        $this->showSuggestions = false;
        $this->resetPage();
    }

    public function getSuggestions()
    {
        if (empty($this->search) || strlen($this->search) < 2) {
            return [];
        }

        $searchTerm = '%' . $this->search . '%';
        $suggestions = collect();

        // Get invoice numbers
        $invoiceNumbers = Invoice::where('invoice_number', 'like', $searchTerm)
            ->limit(5)
            ->pluck('invoice_number')
            ->map(fn($item) => ['type' => 'Invoice', 'value' => $item, 'label' => $item]);

        // Get customer names
        $customers = Invoice::whereHas('customer', function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm);
            })
            ->with('customer')
            ->limit(5)
            ->get()
            ->pluck('customer.name')
            ->unique()
            ->map(fn($item) => ['type' => 'Customer', 'value' => $item, 'label' => $item]);

        // Get delivery note numbers
        $deliveryNotes = Invoice::whereHas('deliveryNote', function ($q) use ($searchTerm) {
                $q->where('dn_number', 'like', $searchTerm);
            })
            ->with('deliveryNote')
            ->limit(5)
            ->get()
            ->pluck('deliveryNote.dn_number')
            ->unique()
            ->map(fn($item) => ['type' => 'Delivery Note', 'value' => $item, 'label' => $item]);

        // Get item descriptions with unit price
        $descriptions = Invoice::whereHas('items', function ($q) use ($searchTerm) {
                $q->where('description', 'like', $searchTerm);
            })
            ->with(['items.invoice.customer', 'customer'])
            ->limit(10)
            ->get()
            ->flatMap(function($invoice) {
                return $invoice->items->map(function($item) use ($invoice) {
                    $customerCurrency = ($invoice->customer && $invoice->customer->currency) 
                        ? $invoice->customer->currency 
                        : 'LKR';
                    $currencySymbol = match($customerCurrency) {
                        'LKR' => 'Rs.',
                        'USD' => '$',
                        'EUR' => '€',
                        'GBP' => '£',
                        'INR' => '₹',
                        default => $customerCurrency . ' '
                    };
                    return [
                        'description' => $item->description,
                        'unit_price' => $item->unit_price,
                        'currency_symbol' => $currencySymbol,
                    ];
                });
            })
            ->filter(fn($item) => stripos($item['description'], trim($this->search, '%')) !== false)
            ->unique('description')
            ->take(5)
            ->map(fn($item) => [
                'type' => 'Item Description', 
                'value' => $item['description'], 
                'label' => $item['description'],
                'unit_price' => $item['unit_price'],
                'currency_symbol' => $item['currency_symbol']
            ]);

        // Get material codes with unit price
        $materialCodes = Invoice::whereHas('items', function ($q) use ($searchTerm) {
                $q->where('material_code', 'like', $searchTerm);
            })
            ->with(['items.invoice.customer', 'customer'])
            ->limit(10)
            ->get()
            ->flatMap(function($invoice) {
                return $invoice->items->map(function($item) use ($invoice) {
                    $customerCurrency = ($invoice->customer && $invoice->customer->currency) 
                        ? $invoice->customer->currency 
                        : 'LKR';
                    $currencySymbol = match($customerCurrency) {
                        'LKR' => 'Rs.',
                        'USD' => '$',
                        'EUR' => '€',
                        'GBP' => '£',
                        'INR' => '₹',
                        default => $customerCurrency . ' '
                    };
                    return [
                        'material_code' => $item->material_code,
                        'unit_price' => $item->unit_price,
                        'currency_symbol' => $currencySymbol,
                    ];
                });
            })
            ->filter(fn($item) => stripos($item['material_code'], trim($this->search, '%')) !== false)
            ->unique('material_code')
            ->take(5)
            ->map(fn($item) => [
                'type' => 'Material Code', 
                'value' => $item['material_code'], 
                'label' => $item['material_code'],
                'unit_price' => $item['unit_price'],
                'currency_symbol' => $item['currency_symbol']
            ]);

        $suggestions = $suggestions
            ->merge($invoiceNumbers)
            ->merge($customers)
            ->merge($deliveryNotes)
            ->merge($descriptions)
            ->merge($materialCodes)
            ->unique('value')
            ->take(10);

        return $suggestions->values()->all();
    }

    public function render()
    {
        $invoices = Invoice::with(['customer', 'deliveryNote', 'jobOrder'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('invoice_number', 'like', "%{$this->search}%")
                      ->orWhereHas('customer', function ($q) {
                          $q->where('name', 'like', "%{$this->search}%");
                      })
                      ->orWhereHas('deliveryNote', function ($q) {
                          $q->where('dn_number', 'like', "%{$this->search}%");
                      })
                      ->orWhereHas('items', function ($q) {
                          $q->where('description', 'like', "%{$this->search}%")
                            ->orWhere('material_code', 'like', "%{$this->search}%");
                      });
                });
            })
            ->orderBy('invoice_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $suggestions = $this->getSuggestions();

        return view('livewire.invoices.invoice-management', [
            'invoices' => $invoices,
            'suggestions' => $suggestions,
        ]);
    }
}
