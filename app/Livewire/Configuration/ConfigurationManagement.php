<?php

namespace App\Livewire\Configuration;

use Livewire\Component;
use App\Models\SystemConfiguration;
use Carbon\Carbon;

class ConfigurationManagement extends Component
{
    public $configurations = [];
    public $editingKey = null;
    public $editingValue = '';
    
    // Date format properties
    public $dateFormat = 'd-m-Y';
    public $dateFormats = [];

    public function mount()
    {
        $this->loadConfigurations();
        $this->loadDateFormat();
    }

    public function loadConfigurations()
    {
        $this->configurations = SystemConfiguration::getByCategory('grn_processing');
    }
    
    public function loadDateFormat()
    {
        $this->dateFormat = SystemConfiguration::getValue('date_format', 'd-m-Y');
        $this->initializeDateFormats();
    }
    
    public function initializeDateFormats()
    {
        $currentDate = Carbon::now();
        
        $this->dateFormats = [
            [
                'value' => 'd-m-Y',
                'label' => 'dd-mm-yyyy',
                'example' => $currentDate->format('d-m-Y')
            ],
            [
                'value' => 'd/m/Y',
                'label' => 'dd/mm/yyyy',
                'example' => $currentDate->format('d/m/Y')
            ],
            [
                'value' => 'Y-m-d',
                'label' => 'yyyy-mm-dd',
                'example' => $currentDate->format('Y-m-d')
            ],
            [
                'value' => 'Y/m/d',
                'label' => 'yyyy/mm/dd',
                'example' => $currentDate->format('Y/m/d')
            ],
        ];
    }
    
    public function updatedDateFormat()
    {
        $this->initializeDateFormats();
    }
    
    public function saveDateFormat()
    {
        SystemConfiguration::setValue(
            'date_format',
            $this->dateFormat,
            'string',
            'Date format for displaying dates throughout the system',
            'display'
        );

        session()->flash('success', 'Date format updated successfully! All dates will now display in the selected format.');
        $this->initializeDateFormats();
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
        return view('livewire.configuration.configuration-management');
    }
}