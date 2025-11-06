<div>
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ session('success') }}
            </div>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h1 class="text-2xl font-bold">Suppliers</h1>
            <div class="space-x-2">
                <input type="text" wire:model.debounce.300ms="search" placeholder="Search suppliers..."
                       class="px-3 py-1.5 border rounded-md" />
                <button wire:click="openModal" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-md">Add Supplier</button>
            </div>
        </div>

        <div class="p-6">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($suppliers as $supplier)
                    <tr wire:click="viewSupplier({{ $supplier->id }})" class="cursor-pointer hover:bg-gray-50">
                        <td class="px-6 py-3 text-sm text-gray-900">
                            <a wire:click.stop="viewSupplier({{ $supplier->id }})" class="text-blue-600 hover:underline">{{ $supplier->name }}</a>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-900">{{ $supplier->code }}</td>
                        <td class="px-6 py-3 text-sm text-gray-900">{{ $supplier->phone }}</td>
                        <td class="px-6 py-3 text-sm text-gray-900">{{ $supplier->email }}</td>
                        <td class="px-6 py-3 text-sm" wire:click.stop>
                            <div class="flex items-center space-x-2">
                                <button wire:click="openModal({{ $supplier->id }})" class="text-indigo-600 hover:text-indigo-900 flex items-center" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button wire:click.stop="openDeleteConfirmModal({{ $supplier->id }})" class="text-red-600 hover:text-red-900 flex items-center" title="Delete">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4">{{ $suppliers->links() }}</div>
        </div>
    </div>

    @if($showModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">
                    @if($isViewMode)
                        View Supplier
                    @elseif($editingId)
                        Edit Supplier
                    @else
                        Add Supplier
                    @endif
                </h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="mt-3">

                <!-- Flash Messages -->
                @if (session()->has('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Tabs -->
                <div class="border-b border-gray-200 mb-4">
                    <nav class="-mb-px flex space-x-8">
                        <button wire:click="setActiveTab('general')"
                                class="@if($activeTab === 'general') border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-400 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            General Info
                        </button>
                        <button wire:click="setActiveTab('contact')"
                                class="@if($activeTab === 'contact') border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-400 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Primary Contact
                        </button>
                        <button wire:click="setActiveTab('finance')"
                                class="@if($activeTab === 'finance') border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-400 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Finance
                        </button>
                        <button wire:click="setActiveTab('reelsize')"
                                class="@if($activeTab === 'reelsize') border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-400 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Add Real Size
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="mt-4">
                    <!-- General Info Tab -->
                    @if($activeTab === 'general')
                    <div class="space-y-3">
                        <!-- Row 1: Company Name, Code -->
                        <div class="flex gap-4" style="gap: 70px !important;">
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4" style="min-width: 140px;">Company Name <span class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <input type="text" wire:model.defer="form.name" {{ $isViewMode ? 'readonly' : '' }} class="block w-[250px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}" />
                                    @error('form.name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 " style="min-width: 70px;">Code</label>
                                <div class="flex-1">
                                    <input type="text" wire:model.defer="form.code" {{ $isViewMode ? 'readonly' : '' }} class="block w-[250px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}" />
                                    @error('form.code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Row 2: Address (Full Width) -->
                        <div class="flex ">
                            <div class="flex items-start " style="width: 100%;">
                                <label class="block text-sm font-medium text-gray-700 mb-1" style="min-width: 152px; padding-top: 6px;">Address</label>
                                <div class="flex-1">
                                    <textarea wire:model.defer="form.address" rows="3" {{ $isViewMode ? 'readonly' : '' }} class="block w-[250px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}"></textarea>
                                    @error('form.address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                        </div>

                        <!-- Row 3: Company Phone Number, Company Email -->
                        <div class="flex gap-4" style="gap: 70px !important;">
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4" style="min-width: 140px;">Company Phone Number</label>
                                <div class="flex-1">
                                    <input type="text" wire:model.defer="form.phone" {{ $isViewMode ? 'readonly' : '' }} class="block w-[250px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}" />
                                    @error('form.phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 " style="min-width: 70px;">Company <br> Email</label>
                                <div class="flex-1">
                                    <input type="email" wire:model.defer="form.email" {{ $isViewMode ? 'readonly' : '' }} class="block w-[250px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}" />
                                    @error('form.email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Row 4: Company Website, Status -->
                        <div class="flex gap-4" style="gap: 70px !important;">
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4" style="min-width: 140px;">Company Website</label>
                                <div class="flex-1">
                                    <input type="url" wire:model.defer="form.website" {{ $isViewMode ? 'readonly' : '' }} class="block w-[250px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}" />
                                    @error('form.website') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 " style="min-width: 70px;">Status <span class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <select wire:model.defer="form.status" {{ $isViewMode ? 'disabled' : '' }} class="block w-[250px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                    @error('form.status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Row 5: Note (Full Width) -->
                        <div class="flex ">
                            <div class="flex items-start " style="width: 100%;">
                                <label class="block text-sm font-medium text-gray-700 mb-1" style="min-width: 152px; padding-top: 6px;">Note</label>
                                <div class="flex-1">
                                    <textarea wire:model.defer="form.notes" rows="3" {{ $isViewMode ? 'readonly' : '' }} class="block w-[653px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}"></textarea>
                                    @error('form.notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                        </div>
                    </div>
                    @endif

                    <!-- Primary Contact Tab -->
                    @if($activeTab === 'contact')
                    <div class="space-y-3">
                        <!-- Row 1: First Name, Last Name -->
                        <div class="flex gap-4" style="gap: 70px !important;">
                            <div class="flex items-center gap-3 flex-1" >
                                <label class="block text-sm font-medium text-gray-700 mb-1 " style="min-width: 50px;">First Name</label>
                                <div class="flex-1">
                                    <input type="text" wire:model.defer="contactForm.first_name" {{ $isViewMode ? 'readonly' : '' }} class="block w-[300px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}" />
                                    @error('contactForm.first_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Last Name</label>
                                <div class="flex-1">
                                    <input type="text" wire:model.defer="contactForm.last_name" {{ $isViewMode ? 'readonly' : '' }} class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}" />
                                    @error('contactForm.last_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Row 2: Email, Phone Number -->
                        <div class="flex gap-4" style="gap: 70px !important;">
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 " style="min-width: 68px;">Email</label>
                                <div class="flex-1">
                                    <input type="email" wire:model.defer="contactForm.email" {{ $isViewMode ? 'readonly' : '' }} class="block w-[300px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}" />
                                    @error('contactForm.email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Phone Number</label>
                                <div class="flex-1">
                                    <input type="text" wire:model.defer="contactForm.phone" {{ $isViewMode ? 'readonly' : '' }} class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}" />
                                    @error('contactForm.phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Finance Tab -->
                    @if($activeTab === 'finance')
                    <div class="space-y-3">
                        <!-- Row 1: Payable Account, Tax -->
                        <div class="flex gap-4" style="gap: 100px !important;">
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 " style="min-width: 80px;">Payable Account</label>
                                <div class="flex-1">
                                    <input type="text" wire:model.defer="financeForm.payable_account" {{ $isViewMode ? 'readonly' : '' }} class="block w-[300px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}" />
                                </div>
                            </div>
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 " style="min-width: 30px;">Tax</label>
                                <div class="flex-1">
                                    <input type="text" wire:model.defer="financeForm.tax" {{ $isViewMode ? 'readonly' : '' }} class="block w-[300px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}" />
                                </div>
                            </div>
                        </div>

                        <!-- Row 2: Bank -->
                        <div class="flex gap-4">
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 " style="min-width: 80px;">Bank</label>
                                <div class="flex-1">
                                    <input type="text" wire:model.defer="financeForm.bank" {{ $isViewMode ? 'readonly' : '' }} class="block w-[300px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}" />
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Add Real Size Tab -->
                    @if($activeTab === 'reelsize')
                    <div class="space-y-4">
                        @if(!$isViewMode)
                        <div class="bg-gray-50 p-4 rounded-md">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Add Reel Size</h4>
                            <div class="flex gap-4 items-end">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Reel Size <span class="text-red-500">*</span></label>
                                    <input type="number" step="0.01" wire:model.defer="reelForm.reel_size"
                                           class="block w-full px-3 py-1 border border-gray-400 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Enter reel size (e.g., 13.50)" />
                                    @error('reelForm.reel_size')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <button wire:click="addReelSize"
                                            class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm font-medium">
                                        {{ $editingReelId ? 'Update' : 'Add' }} Reel Size
                                    </button>
                                </div>
                                @if($editingReelId)
                                <div>
                                    <button wire:click="resetReelForm"
                                            class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md text-sm font-medium">
                                        Cancel
                                    </button>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        @if(count($reelSizes) > 0)
                        <div>
                            <h4 class="text-md font-medium text-gray-900 mb-3">Reel Sizes List</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reel Size</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($reelSizes as $index => $reel)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ number_format($reel['reel_size'], 2) }}</td>
                                            @if(!$isViewMode)
                                            <td class="px-4 py-3 text-sm">
                                                <button wire:click="editReelSize({{ $index }})"
                                                        class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200 mr-2">
                                                    Edit
                                                </button>
                                                <button wire:click="deleteReelSize({{ $index }})"
                                                        class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200">
                                                    Delete
                                                </button>
                                            </td>
                                            @else
                                            <td class="px-4 py-3 text-sm text-gray-500">-</td>
                                            @endif
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @else
                        <div class="text-center py-8 text-gray-500">
                            <p>No reel sizes added yet. Add a reel size above.</p>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3 pt-6 mt-6 border-t border-gray-200">
                    <button wire:click="closeModal" class="px-4 py-2 border border-gray-400 rounded-md hover:bg-gray-50">
                        {{ $isViewMode ? 'Close' : 'Cancel' }}
                    </button>
                    @if(!$isViewMode)
                    <button wire:click="save" class="px-4 py-2 rounded-md text-white bg-blue-600 hover:bg-blue-700">Save Supplier</button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteConfirmModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-end mb-2">
                <button wire:click="closeDeleteConfirmModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div class="mt-3">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>

                <h3 class="text-lg font-medium text-gray-900 text-center mb-2">
                    Delete Supplier
                </h3>

                <div class="text-center text-sm text-gray-600 mb-6">
                    <p>Are you sure you want to delete this supplier?</p>
                    @if($supplierToDelete)
                        @php
                            $supplier = \App\Models\Supplier::find($supplierToDelete);
                        @endphp
                        @if($supplier)
                            <p class="font-semibold text-gray-900 mt-2">
                                {{ $supplier->name }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                This action cannot be undone.
                            </p>
                        @endif
                    @endif
                </div>

                <div class="flex space-x-3">
                    <button wire:click="closeDeleteConfirmModal"
                            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md transition-colors">
                        Cancel
                    </button>
                    @if($supplierToDelete)
                    <button wire:click="delete({{ $supplierToDelete }})"
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                        Delete
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>


