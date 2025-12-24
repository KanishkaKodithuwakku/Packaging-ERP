<?php

namespace App\Livewire\Accounting;

use App\Models\ExchangeRate;
use App\Models\Currency;
use Livewire\Component;
use Livewire\WithPagination;

class ExchangeRateManagement extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editingRate = false;
    public $search = '';
    public $filterFromCurrency = '';
    public $filterToCurrency = '';
    public $filterDate = '';
    
    public $form = [
        'from_currency_id' => '',
        'to_currency_id' => '',
        'rate' => '',
        'rate_date' => '',
    ];

    public function mount()
    {
        $this->form['rate_date'] = now()->format('Y-m-d');
    }

    public function openAddRateModal()
    {
        $this->resetForm();
        $this->showModal = true;
        session()->flash('message', 'Exchange rate modal opened!');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->editingRate = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->form = [
            'from_currency_id' => '',
            'to_currency_id' => '',
            'rate' => '',
            'rate_date' => now()->format('Y-m-d'),
        ];
        $this->editingRate = false;
        $this->resetErrorBag();
    }

    public function addRate()
    {
        $this->validate([
            'form.from_currency_id' => 'required|exists:currencies,id',
            'form.to_currency_id' => 'required|exists:currencies,id|different:form.from_currency_id',
            'form.rate' => 'required|numeric|min:0.00000001',
            'form.rate_date' => 'required|date',
        ]);

        ExchangeRate::create([
            'from_currency_id' => $this->form['from_currency_id'],
            'to_currency_id' => $this->form['to_currency_id'],
            'rate' => $this->form['rate'],
            'rate_date' => $this->form['rate_date'],
        ]);

        $this->resetForm();
        $this->showModal = false;
        session()->flash('message', 'Exchange rate created successfully.');
    }

    public function updateRate()
    {
        // Implementation for updating rates
        session()->flash('message', 'Update functionality coming soon.');
    }

    public function deleteRate($id)
    {
        $rate = ExchangeRate::findOrFail($id);
        $rate->delete();
        session()->flash('message', 'Exchange rate deleted successfully.');
    }

    public function editRate($id)
    {
        // Implementation for editing rates
        session()->flash('message', 'Edit functionality coming soon.');
    }

    public function render()
    {
        $query = ExchangeRate::with(['fromCurrency', 'toCurrency']);
        
        if ($this->search) {
            $query->whereHas('fromCurrency', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            })->orWhereHas('toCurrency', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }
        
        if ($this->filterFromCurrency) {
            $query->where('from_currency_id', $this->filterFromCurrency);
        }
        
        if ($this->filterToCurrency) {
            $query->where('to_currency_id', $this->filterToCurrency);
        }
        
        if ($this->filterDate) {
            $query->where('rate_date', $this->filterDate);
        }
        
        $exchangeRates = $query->orderBy('rate_date', 'desc')
                              ->orderBy('created_at', 'desc')
                              ->paginate(10);

        $currencies = Currency::active()->orderBy('name')->get();

        return view('livewire.accounting.exchange-rate-management', [
            'exchangeRates' => $exchangeRates,
            'currencies' => $currencies,
        ]);
    }
}
