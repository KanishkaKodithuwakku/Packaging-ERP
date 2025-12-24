<!-- Add Box/Divider Modal -->
@if($showBoxDividerModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-[95vw] shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <!-- Modal Header -->
                <div class="flex justify-between items-center pb-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">
                        Add Box/Divider
                    </h3>
                    <button wire:click="closeBoxDividerModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Tab Navigation -->
                <div class="border-b border-gray-200 mt-4">
                    <nav class="-mb-px flex space-x-8">
                        <button wire:click="setActiveTab('boxes')"
                                class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'boxes' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Boxes ({{ count($boxes) }})
                        </button>
                        <button wire:click="setActiveTab('dividers')"
                                class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'dividers' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Dividers ({{ count($dividers) }})
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="mt-6">
                    @if($activeTab === 'boxes')
                        @include('livewire.job-orders.job-order-management.boxes-tab')
                    @elseif($activeTab === 'dividers')
                        @include('livewire.job-orders.job-order-management.dividers-tab')
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Edit Divider Modal -->
@if($showEditDividerModal)
    @include('livewire.job-orders.job-order-management.edit-divider-modal')
@endif
