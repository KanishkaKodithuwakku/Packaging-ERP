<?php

namespace App\Livewire\Configuration;

use Livewire\Component;
use App\Models\SystemConfiguration;
use Carbon\Carbon;

class DateFormatConfiguration extends Component
{
    public $dateFormat = 'd-m-Y';
    public $dateFormats = [];

    public function mount()
    {
        // Get current date format from configuration
        $this->dateFormat = SystemConfiguration::getValue('date_format', 'd-m-Y');
        
        // Initialize date format options with examples
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
        // Update examples when format changes in real-time
        $this->initializeDateFormats();
    }
    
    public function getExamplesProperty()
    {
        $currentDate = Carbon::now();
        $sampleDate = Carbon::parse('2024-01-15');
        
        return [
            'today' => $currentDate->format($this->dateFormat),
            'sample' => $sampleDate->format($this->dateFormat),
        ];
    }

    public function saveDateFormat()
    {
        // Save date format to SystemConfiguration
        SystemConfiguration::setValue(
            'date_format',
            $this->dateFormat,
            'string',
            'Date format for displaying dates throughout the system',
            'display'
        );

        session()->flash('success', 'Date format updated successfully! All dates will now display in the selected format.');
        
        // Refresh examples
        $this->initializeDateFormats();
    }

    public function render()
    {
        return view('livewire.configuration.date-format-configuration');
    }
}

