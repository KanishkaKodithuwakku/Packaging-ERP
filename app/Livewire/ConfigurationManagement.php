<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SystemConfiguration;

class ConfigurationManagement extends Component
{
    public $configurations = [];
    public $editingKey = null;
    public $editingValue = '';

    public function mount()
    {
        $this->loadConfigurations();
    }

    public function loadConfigurations()
    {
        $this->configurations = SystemConfiguration::getByCategory('grn_processing');
    }

    public function editConfiguration($key)
    {
        $this->editingKey = $key;
        $config = SystemConfiguration::where('key', $key)->first();
        $this->editingValue = $config ? $config->value : '';
    }

    public function saveConfiguration()
    {
        if ($this->editingKey) {
            $config = SystemConfiguration::where('key', $this->editingKey)->first();
            
            if ($config) {
                $config->update(['value' => $this->editingValue]);
                session()->flash('success', 'Configuration updated successfully!');
            }
            
            $this->editingKey = null;
            $this->editingValue = '';
            $this->loadConfigurations();
        }
    }

    public function cancelEdit()
    {
        $this->editingKey = null;
        $this->editingValue = '';
    }

    public function render()
    {
        return view('livewire.configuration-management');
    }
}