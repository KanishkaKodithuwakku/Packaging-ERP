<?php

namespace App\Livewire;

use App\Models\GRN;
use Livewire\Component;

class TestGRN extends Component
{
    public $grn;
    public $showModal = false;

    public function mount($id)
    {
        $this->grn = GRN::findOrFail($id);
    }

    public function showModal()
    {
        $this->showModal = true;
        session()->flash('success', 'Modal should be visible!');
    }

    public function render()
    {
        return view('livewire.test-grn');
    }
}
