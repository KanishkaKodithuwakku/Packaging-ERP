<?php

namespace App\Livewire;

use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;

class InvoiceManagement extends Component
{
    use WithPagination;

    protected $layout = 'components.layouts.app';

    public $search = '';

    public function updatingSearch()
    {
        $this->resetPage();
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
                      });
                });
            })
            ->orderBy('invoice_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('livewire.invoice-management', [
            'invoices' => $invoices,
        ]);
    }
}
