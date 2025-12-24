<?php

namespace App\Livewire\Test;

use Livewire\Component;

class SimpleModalTest extends Component
{
    public $showModal = false;

    public function toggleModal()
    {
        $this->showModal = !$this->showModal;
    }

    public function render()
    {
        return view('livewire.test.simple-modal-test');
    }
}

