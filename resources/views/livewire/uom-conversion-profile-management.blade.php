<div>
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">UOM Conversion Profile Management</h1>
        <button wire:click="createProfile" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Profile
        </button>
    </div>

    <!-- Search -->
    <div class="mb-6">
        <input type="text" wire:model.live="search" placeholder="Search profiles..." class="w-full max-w-md px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Profiles List -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Conversion Profiles</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($profiles as $profile)
                    <div class="p-6 hover:bg-gray-50 cursor-pointer {{ $selectedProfile == $profile->id ? 'bg-indigo-50 border-l-4 border-indigo-500' : '' }}" 
                         wire:click="selectProfile({{ $profile->id }})">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-gray-900">{{ $profile->name }}</h4>
                                <p class="text-sm text-gray-500 mt-1">{{ $profile->description }}</p>
                                <div class="mt-2 flex items-center space-x-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($profile->status === 'active') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        {{ ucfirst($profile->status) }}
                                    </span>
                                    <span class="text-xs text-gray-500">{{ $profile->conversionItems->count() }} conversions</span>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <button wire:click.stop="editProfile({{ $profile->id }})" class="text-indigo-600 hover:text-indigo-900 text-sm">Edit</button>
                                <button wire:click.stop="deleteProfile({{ $profile->id }})" 
                                        wire:confirm="Are you sure you want to delete this profile?"
                                        class="text-red-600 hover:text-red-900 text-sm">Delete</button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-gray-500">No profiles found.</div>
                @endforelse
            </div>
        </div>

        <!-- Selected Profile Details -->
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900">
                        @if($selectedProfileData)
                            {{ $selectedProfileData->name }} Conversions
                        @else
                            Select a Profile
                        @endif
                    </h3>
                    @if($selectedProfile)
                        <button wire:click="createConversionItem" class="px-3 py-1 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">
                            Add Conversion
                        </button>
                    @endif
                </div>
            </div>
            
            @if($selectedProfileData)
                <div class="divide-y divide-gray-200">
                    @forelse($selectedProfileData->conversionItems as $item)
                        <div class="p-4">
                            <div class="flex justify-between items-center">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm font-medium text-gray-900">{{ $item->fromUom->code }}</span>
                                        <span class="text-gray-400">→</span>
                                        <span class="text-sm font-medium text-gray-900">{{ $item->toUom->code }}</span>
                                        <span class="text-sm text-gray-500">({{ number_format($item->factor, 6) }})</span>
                                    </div>
                                    @if($item->notes)
                                        <p class="text-xs text-gray-500 mt-1">{{ $item->notes }}</p>
                                    @endif
                                </div>
                                <div class="flex space-x-2">
                                    <button wire:click="editConversionItem({{ $item->id }})" class="text-indigo-600 hover:text-indigo-900 text-sm">Edit</button>
                                    <button wire:click="deleteConversionItem({{ $item->id }})" 
                                            wire:confirm="Are you sure you want to delete this conversion?"
                                            class="text-red-600 hover:text-red-900 text-sm">Delete</button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-gray-500">No conversions in this profile.</div>
                    @endforelse
                </div>
            @else
                <div class="p-6 text-center text-gray-500">Select a profile to view its conversions.</div>
            @endif
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $profiles->links() }}
    </div>

    <!-- Profile Modal -->
    @if($showProfileModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            {{ $isEditingProfile ? 'Edit Profile' : 'Create New Profile' }}
                        </h3>
                        
                        <form wire:submit.prevent="saveProfile">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Profile Name</label>
                                <input type="text" wire:model="profileName" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g., Glue Profile">
                                @error('profileName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea wire:model="profileDescription" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Optional description"></textarea>
                                @error('profileDescription') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select wire:model="profileStatus" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                @error('profileStatus') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </form>
                    </div>
                    
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="saveProfile" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            {{ $isEditingProfile ? 'Update' : 'Create' }}
                        </button>
                        <button wire:click="closeProfileModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Conversion Item Modal -->
    @if($showConversionModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 z-50">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            {{ $isEditingConversion ? 'Edit Conversion' : 'Add Conversion to Profile' }}
                        </h3>
                        
                        <form wire:submit.prevent="saveConversionItem">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">From UOM</label>
                                    <select wire:model="fromUomId" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">Select Source UOM</option>
                                        @foreach($uoms as $uom)
                                            <option value="{{ $uom->id }}">{{ $uom->code }} - {{ $uom->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('fromUomId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">To UOM</label>
                                    <select wire:model="toUomId" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                        <option value="">Select Target UOM</option>
                                        @foreach($uoms as $uom)
                                            @if($uom->id != $fromUomId)
                                                <option value="{{ $uom->id }}">{{ $uom->code }} - {{ $uom->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('toUomId') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Conversion Factor</label>
                                <input type="number" wire:model="factor" step="0.000001" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g., 0.8">
                                @error('factor') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                <p class="text-xs text-gray-500 mt-1">How many target UOMs equal 1 source UOM</p>
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                <textarea wire:model="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500" placeholder="Optional notes about this conversion"></textarea>
                                @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </form>
</div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button wire:click="saveConversionItem" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                            {{ $isEditingConversion ? 'Update' : 'Add' }}
                        </button>
                        <button wire:click="closeConversionModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>