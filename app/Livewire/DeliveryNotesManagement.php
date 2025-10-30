<?php

namespace App\Livewire;

use App\Models\DeliveryNote;
use App\Models\JobOrder;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Log;

class DeliveryNotesManagement extends Component
{
    use WithPagination;

    protected $layout = 'components.layouts.app';

    public $deliveryNotes = [];

    public function mount()
    {
        $this->loadDeliveryNotes();
    }

    public function loadDeliveryNotes()
    {
        $this->deliveryNotes = DeliveryNote::with(['jobOrder.customer', 'items'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function render()
    {
        return view('livewire.delivery-notes-management');
    }
}
