<?php

namespace App\Livewire;

use Livewire\Component;

class TestModalSimple extends Component
{
    public $showModal = false;

    public function openModal()
    {
        $this->showModal = true;
        session()->flash('message', 'Modal opened successfully!');
    }

    public function closeModal()
    {
        $this->showModal = false;
        session()->flash('message', 'Modal closed successfully!');
    }

    public function render()
    {
        return view('test-modal-simple');
    }
}

