<?php

namespace App\Livewire;

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
        return view('debug-modal')
            ->layout('components.layouts.blank');
    }
}
