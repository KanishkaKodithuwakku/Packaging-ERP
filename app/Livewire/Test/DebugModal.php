<?php

namespace App\Livewire\Test;

use Livewire\Component;

class DebugModal extends Component
{
    public $showModal = false;

    public function toggleModal()
    {
        $this->showModal = !$this->showModal;
    }

    public function render()
    {
        return view('test.debug-modal')
            ->layout('components.layouts.blank');
    }
}

