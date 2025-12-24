<?php

namespace App\Livewire\UOM;

use App\Models\Uom;
use App\Models\UomConversionProfile;
use App\Models\UomConversionProfileItem;
use App\Repositories\UomRepository;
use Livewire\Component;
use Livewire\WithPagination;

class UomConversionProfileManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedProfile = null;
    
    // Profile form fields
    public $profileId;
    public $profileName = '';
    public $profileDescription = '';
    public $profileStatus = 'active';
    
    // Conversion item form fields
    public $conversionItemId;
    public $fromUomId = '';
    public $toUomId = '';
    public $factor = '';
    public $notes = '';
    
    public $showProfileModal = false;
    public $showConversionModal = false;
    public $isEditingProfile = false;
    public $isEditingConversion = false;

    protected $rules = [
        'profileName' => 'required|string|max:100',
        'profileDescription' => 'nullable|string|max:500',
        'profileStatus' => 'required|in:active,inactive',
        'fromUomId' => 'required|exists:uoms,id',
        'toUomId' => 'required|exists:uoms,id|different:fromUomId',
        'factor' => 'required|numeric|min:0.000001',
        'notes' => 'nullable|string|max:500',
    ];

    protected $messages = [
        'profileName.required' => 'Profile name is required.',
        'fromUomId.required' => 'Source UOM is required.',
        'toUomId.required' => 'Target UOM is required.',
        'toUomId.different' => 'Source and target UOMs must be different.',
        'factor.required' => 'Conversion factor is required.',
        'factor.min' => 'Conversion factor must be greater than 0.',
    ];

    public function mount()
    {
        // Check permissions
        if (!auth()->user()->hasAnyRole(['admin', 'planner'])) {
            abort(403, 'Unauthorized access to UOM conversion profile management.');
        }
    }

    public function render()
    {
        $query = UomConversionProfile::with('conversionItems.fromUom', 'conversionItems.toUom');

        // Apply search filter
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
        }

        $profiles = $query->orderBy('name')->paginate(10);
        $uoms = Uom::active()->orderBy('name')->get();
        
        $selectedProfileData = null;
        if ($this->selectedProfile) {
            $selectedProfileData = UomConversionProfile::with('conversionItems.fromUom', 'conversionItems.toUom')
                ->find($this->selectedProfile);
        }

        return view('livewire.uom.uom-conversion-profile-management', compact('profiles', 'uoms', 'selectedProfileData'));
    }

    // Profile Management Methods
    public function createProfile()
    {
        $this->resetProfileForm();
        $this->isEditingProfile = false;
        $this->showProfileModal = true;
    }

    public function editProfile($id)
    {
        $profile = UomConversionProfile::findOrFail($id);
        $this->profileId = $profile->id;
        $this->profileName = $profile->name;
        $this->profileDescription = $profile->description;
        $this->profileStatus = $profile->status;
        $this->isEditingProfile = true;
        $this->showProfileModal = true;
    }

    public function saveProfile()
    {
        $this->validate([
            'profileName' => 'required|string|max:100',
            'profileDescription' => 'nullable|string|max:500',
            'profileStatus' => 'required|in:active,inactive',
        ]);

        $data = [
            'name' => $this->profileName,
            'description' => $this->profileDescription,
            'status' => $this->profileStatus,
        ];

        if ($this->isEditingProfile) {
            $profile = UomConversionProfile::findOrFail($this->profileId);
            $profile->update($data);
            session()->flash('message', 'Profile updated successfully!');
        } else {
            UomConversionProfile::create($data);
            session()->flash('message', 'Profile created successfully!');
        }

        $this->closeProfileModal();
    }

    public function deleteProfile($id)
    {
        $profile = UomConversionProfile::findOrFail($id);
        
        // Check if profile is being used
        if ($profile->inventoryItems()->exists()) {
            session()->flash('error', 'Cannot delete profile as it is being used by inventory items.');
            return;
        }

        $profile->delete();
        session()->flash('message', 'Profile deleted successfully!');
    }

    public function selectProfile($id)
    {
        $this->selectedProfile = $id;
    }

    // Conversion Item Management Methods
    public function createConversionItem()
    {
        if (!$this->selectedProfile) {
            session()->flash('error', 'Please select a profile first.');
            return;
        }

        $this->resetConversionForm();
        $this->isEditingConversion = false;
        $this->showConversionModal = true;
    }

    public function editConversionItem($id)
    {
        $item = UomConversionProfileItem::findOrFail($id);
        $this->conversionItemId = $item->id;
        $this->fromUomId = $item->from_uom_id;
        $this->toUomId = $item->to_uom_id;
        $this->factor = $item->factor;
        $this->notes = $item->notes;
        $this->isEditingConversion = true;
        $this->showConversionModal = true;
    }

    public function saveConversionItem()
    {
        $this->validate([
            'fromUomId' => 'required|exists:uoms,id',
            'toUomId' => 'required|exists:uoms,id|different:fromUomId',
            'factor' => 'required|numeric|min:0.000001',
            'notes' => 'nullable|string|max:500',
        ]);

        $data = [
            'profile_id' => $this->selectedProfile,
            'from_uom_id' => $this->fromUomId,
            'to_uom_id' => $this->toUomId,
            'factor' => $this->factor,
            'notes' => $this->notes,
        ];

        if ($this->isEditingConversion) {
            $item = UomConversionProfileItem::findOrFail($this->conversionItemId);
            $item->update($data);
            session()->flash('message', 'Conversion item updated successfully!');
        } else {
            // Check if conversion already exists in this profile
            $existing = UomConversionProfileItem::where('profile_id', $this->selectedProfile)
                ->where('from_uom_id', $this->fromUomId)
                ->where('to_uom_id', $this->toUomId)
                ->first();
            
            if ($existing) {
                session()->flash('error', 'Conversion between these UOMs already exists in this profile.');
                return;
            }

            UomConversionProfileItem::create($data);
            session()->flash('message', 'Conversion item created successfully!');
        }

        $this->closeConversionModal();
    }

    public function deleteConversionItem($id)
    {
        $item = UomConversionProfileItem::findOrFail($id);
        $item->delete();
        session()->flash('message', 'Conversion item deleted successfully!');
    }

    // Modal Management
    public function closeProfileModal()
    {
        $this->showProfileModal = false;
        $this->resetProfileForm();
    }

    public function closeConversionModal()
    {
        $this->showConversionModal = false;
        $this->resetConversionForm();
    }

    private function resetProfileForm()
    {
        $this->profileId = null;
        $this->profileName = '';
        $this->profileDescription = '';
        $this->profileStatus = 'active';
        $this->isEditingProfile = false;
        $this->resetErrorBag();
    }

    private function resetConversionForm()
    {
        $this->conversionItemId = null;
        $this->fromUomId = '';
        $this->toUomId = '';
        $this->factor = '';
        $this->notes = '';
        $this->isEditingConversion = false;
        $this->resetErrorBag();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFromUomId()
    {
        $this->toUomId = '';
    }
}