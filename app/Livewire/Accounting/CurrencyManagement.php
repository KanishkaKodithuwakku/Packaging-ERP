<?php

namespace App\Livewire\Accounting;

use App\Models\Currency;
use Livewire\Component;
use Livewire\WithPagination;

class CurrencyManagement extends Component
{
    use WithPagination;

    public $showModal = false;
    public $editingCurrency = false;
    public $search = '';
    public $filterStatus = '';
    public $filterBase = '';
    
    public $form = [
        'name' => '',
        'code' => '',
        'symbol' => '',
        'symbol_position' => 'before',
        'decimal_places' => 2,
        'is_base_currency' => false,
        'is_active' => true,
    ];

    public function addCurrency()
    {
        $this->validate([
            'form.name' => 'required|string|max:255|unique:currencies,name',
            'form.code' => 'required|string|max:3|unique:currencies,code',
            'form.symbol' => 'required|string|max:10',
            'form.symbol_position' => 'required|in:before,after',
            'form.decimal_places' => 'required|integer|min:0|max:8',
            'form.is_base_currency' => 'boolean',
            'form.is_active' => 'boolean',
        ]);

        // If setting as base currency, remove base currency from others
        if ($this->form['is_base_currency']) {
            Currency::where('is_base_currency', true)->update(['is_base_currency' => false]);
        }

        Currency::create([
            'name' => $this->form['name'],
            'code' => strtoupper($this->form['code']),
            'symbol' => $this->form['symbol'],
            'symbol_position' => $this->form['symbol_position'],
            'decimal_places' => $this->form['decimal_places'],
            'is_base_currency' => $this->form['is_base_currency'],
            'is_active' => $this->form['is_active'],
        ]);

        $this->resetForm();
        $this->showModal = false;
        session()->flash('message', 'Currency created successfully.');
    }

    public function updateCurrency()
    {
        // Implementation for updating currencies
        session()->flash('message', 'Update functionality coming soon.');
    }

    public function toggleStatus($id)
    {
        $currency = Currency::findOrFail($id);
        
        if ($currency->is_base_currency) {
            session()->flash('error', 'Cannot deactivate base currency.');
            return;
        }
        
        $currency->update(['is_active' => !$currency->is_active]);
        session()->flash('message', 'Currency status updated successfully.');
    }

    public function deleteCurrency($id)
    {
        $currency = Currency::findOrFail($id);
        
        if ($currency->is_base_currency) {
            session()->flash('error', 'Cannot delete base currency.');
            return;
        }
        
        $currency->delete();
        session()->flash('message', 'Currency deleted successfully.');
    }

    public function editCurrency($id)
    {
        // Implementation for editing currencies
        session()->flash('message', 'Edit functionality coming soon.');
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->form = [
            'name' => '',
            'code' => '',
            'symbol' => '',
            'symbol_position' => 'before',
            'decimal_places' => 2,
            'is_base_currency' => false,
            'is_active' => true,
        ];
        $this->editingCurrency = false;
        $this->resetErrorBag();
    }

    public function openAddCurrencyModal()
    {
        $this->resetForm();
        $this->showModal = true;
        session()->flash('message', 'Currency modal opened!');
    }

    public function render()
    {
        $query = Currency::query();
        
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('code', 'like', '%' . $this->search . '%');
        }
        
        if ($this->filterStatus === 'active') {
            $query->where('is_active', true);
        } elseif ($this->filterStatus === 'inactive') {
            $query->where('is_active', false);
        }
        
        if ($this->filterBase === 'base') {
            $query->where('is_base_currency', true);
        } elseif ($this->filterBase === 'non-base') {
            $query->where('is_base_currency', false);
        }
        
        $currencies = $query->orderBy('is_base_currency', 'desc')
                           ->orderBy('name')
                           ->paginate(10);

        return view('livewire.accounting.currency-management', [
            'currencies' => $currencies,
        ]);
    }
}
