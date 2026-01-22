<div>
    <!-- Flash Messages -->
    @if (session()->has('success'))
    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ session('success') }}
        </div>
    </div>
    @endif
    @if (session()->has('error'))
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ session('error') }}
        </div>
    </div>
    @endif

    <!-- Header -->
    <div class="px-6 py-4 mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Customers</h1>
        <p class="mt-2 text-gray-600">Manage customer information and details for order generation.</p>
    </div>

    <!-- Customers Table -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">All Customers</h3>
                <div class="flex items-center space-x-4">
                    <input type="text" wire:model.live="search" placeholder="Search customers..."
                        class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 min-w-[200px]">
                    <button wire:click="openModal"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Customer
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Contact Person</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Currency</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($customers as $customer)
                    <tr wire:click="viewCustomer({{ $customer->id }})" class="cursor-pointer hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-500">
                                <a wire:click.stop="viewCustomer({{ $customer->id }})"
                                    class="text-blue-600 hover:underline">{{ $customer->name }}</a>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $customer->code ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @php
                            $contactName = '';
                            if ($customer->contact_first_name || $customer->contact_last_name) {
                            $contactName = trim(($customer->contact_first_name ?? '') . ' ' .
                            ($customer->contact_last_name ?? ''));
                            } elseif ($customer->contact_person) {
                            $contactName = $customer->contact_person;
                            }
                            @endphp
                            {{ $contactName ?: '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $customer->phone ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $customer->email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $customer->currency ?? 'LKR' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                            $statusColors = [
                            'active' => 'bg-green-100 text-green-800',
                            'inactive' => 'bg-red-100 text-red-800',
                            ];
                            $statusColor = $statusColors[$customer->status ?? 'active'] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                {{ ucfirst($customer->status ?? 'active') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm" wire:click.stop>
                            <div class="flex items-center space-x-2">
                                <button wire:click="openModal({{ $customer->id }})"
                                    class="text-indigo-600 hover:text-indigo-900 flex items-center" title="Edit">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                </button>
                                @if(isset($customer->is_in_use) && $customer->is_in_use)
                                <button disabled class="text-gray-400 cursor-not-allowed flex items-center"
                                    title="This customer is in use">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                                <span class="text-xs text-orange-600" title="This customer is in use"></span>
                                @else
                                <button wire:click.stop="openDeleteConfirmModal({{ $customer->id }})"
                                    class="text-red-600 hover:text-red-900 flex items-center" title="Delete">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 text-center">No customers</h3>
                                <p class="mt-1 text-sm text-gray-500 text-center">Get started by adding a new customer.
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $customers->links() }}
        </div>
        @endif
    </div>

    @if($showModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div
            class="relative top-10 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">
                    @if($isViewMode)
                    View Customer
                    @elseif($editingId)
                    Edit Customer
                    @else
                    Add Customer
                    @endif
                </h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
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
                        <button wire:click="setActiveTab('creditlimit')"
                            class="@if($activeTab === 'creditlimit') border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-400 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Credit Limit
                        </button>
                        <button wire:click="setActiveTab('finance')"
                            class="@if($activeTab === 'finance') border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-400 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Finance
                        </button>
                        <button wire:click="setActiveTab('tax')"
                            class="@if($activeTab === 'tax') border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-400 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Tax
                        </button>
                        <button wire:click="setActiveTab('discount')"
                            class="@if($activeTab === 'discount') border-blue-500 text-blue-600 @else border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-400 @endif whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Discount
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="mt-4">
                    <!-- General Info Tab -->
                    @if($activeTab === 'general')
                    <div class="space-y-3">
                        <!-- Row 1: Company Name (Full Width) -->
                        <div class="flex gap-4" style="gap: 70px !important;">
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 "
                                    style="min-width: 140px;">Company Name <span class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <input type="text" wire:model.defer="form.name" {{ $isViewMode ? 'readonly' : '' }}
                                        class="block w-[250px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}" />
                                    @error('form.name') <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Row 2: Address (Full Width) -->
                        <div class="flex ">
                            <div class="flex items-start " style="width: 100%;">
                                <label class="block text-sm font-medium text-gray-700 mb-1"
                                    style="min-width: 152px; padding-top: 6px;">Address <span
                                        class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <textarea wire:model.defer="form.address" rows="3" {{ $isViewMode ? 'readonly' : ''
                                        }}
                                        class="block w-[250px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}"></textarea>
                                    @error('form.address') <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Row 3: Company Phone Number, Company Email -->
                        <div class="flex gap-4" style="gap: 70px !important;">
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4"
                                    style="min-width: 140px;">Company Phone Number <span
                                        class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <input type="text" wire:model.defer="form.phone" {{ $isViewMode ? 'readonly' : '' }}
                                        class="block w-[250px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}" />
                                    @error('form.phone') <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 "
                                    style="min-width: 70px;">Company <br> Email</label>
                                <div class="flex-1">
                                    <input type="email" wire:model.defer="form.email" {{ $isViewMode ? 'readonly' : ''
                                        }}
                                        class="block w-[250px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}" />
                                    @error('form.email') <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Row 4: Company Website, Status -->
                        <div class="flex gap-4" style="gap: 70px !important;">
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4"
                                    style="min-width: 140px;">Company Website</label>
                                <div class="flex-1">
                                    <input type="url" wire:model.defer="form.website" {{ $isViewMode ? 'readonly' : ''
                                        }}
                                        class="block w-[250px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}" />
                                    @error('form.website') <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 "
                                    style="min-width: 70px;">Status <span class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <select wire:model.defer="form.status" {{ $isViewMode ? 'disabled' : '' }}
                                        class="block w-[250px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                    @error('form.status') <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Row 5: Note (Full Width) -->
                        <div class="flex ">
                            <div class="flex items-start " style="width: 100%;">
                                <label class="block text-sm font-medium text-gray-700 mb-1"
                                    style="min-width: 152px; padding-top: 6px;">Note</label>
                                <div class="flex-1">
                                    <textarea wire:model.defer="form.notes" rows="3" {{ $isViewMode ? 'readonly' : '' }}
                                        class="block w-[653px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}"></textarea>
                                    @error('form.notes') <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
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
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 "
                                    style="min-width: 50px;">First Name</label>
                                <div class="flex-1">
                                    <input type="text" wire:model.defer="contactForm.first_name" {{ $isViewMode
                                        ? 'readonly' : '' }}
                                        class="block w-[300px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}" />
                                    @error('contactForm.first_name') <span class="text-red-500 text-sm">{{ $message
                                        }}</span> @enderror
                                </div>
                            </div>
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Last Name</label>
                                <div class="flex-1">
                                    <input type="text" wire:model.defer="contactForm.last_name" {{ $isViewMode
                                        ? 'readonly' : '' }}
                                        class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}" />
                                    @error('contactForm.last_name') <span class="text-red-500 text-sm">{{ $message
                                        }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Row 2: Email, Phone Number -->
                        <div class="flex gap-4" style="gap: 70px !important;">
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 "
                                    style="min-width: 68px;">Email</label>
                                <div class="flex-1">
                                    <input type="email" wire:model.defer="contactForm.email" {{ $isViewMode ? 'readonly'
                                        : '' }}
                                        class="block w-[300px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}" />
                                    @error('contactForm.email') <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Phone Number</label>
                                <div class="flex-1">
                                    <input type="text" wire:model.defer="contactForm.phone" {{ $isViewMode ? 'readonly'
                                        : '' }}
                                        class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}" />
                                    @error('contactForm.phone') <span class="text-red-500 text-sm">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Row 3: Mobile Number -->
                        <div class="flex gap-4" style="gap: 70px !important;">
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 "
                                    style="min-width: 68px;">Mobile<br> Number</label>
                                <div class="flex-1">
                                    <input type="text" wire:model.defer="contactForm.mobile" {{ $isViewMode ? 'readonly'
                                        : '' }}
                                        class="block w-[300px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}" />
                                    @error('contactForm.mobile') <span class="text-red-500 text-sm">{{ $message
                                        }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Credit Limit Tab -->
                    @if($activeTab === 'creditlimit')
                    <div class="space-y-3">
                        <!-- Row 1: Credit limit Period, Credit limit Amount -->
                        <div class="flex gap-4" style="gap: 70px !important;">
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 "
                                    style="min-width: 80px;">Credit limit <br>Period</label>
                                <div class="flex-1">
                                    <input type="text" wire:model.defer="creditLimitForm.credit_limit_period" {{
                                        $isViewMode ? 'readonly' : '' }}
                                        class="block w-[300px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}" />
                                    @error('creditLimitForm.credit_limit_period') <span class="text-red-500 text-sm">{{
                                        $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 "
                                    style="min-width: 80px;">Credit limit<br> Amount</label>
                                <div class="flex-1">
                                    <input type="number" step="0.01"
                                        wire:model.defer="creditLimitForm.credit_limit_amount" {{ $isViewMode
                                        ? 'readonly' : '' }}
                                        class="block w-[300px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}" />
                                    @error('creditLimitForm.credit_limit_amount') <span class="text-red-500 text-sm">{{
                                        $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Finance Tab -->
                    @if($activeTab === 'finance')
                    <div class="space-y-3">
                        <!-- Row 1: Account Receivable, Sales Revenue -->
                        <div class="flex gap-4" style="gap: 70px !important;">
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 "
                                    style="min-width: 80px;">Account Receivable</label>
                                <div class="flex-1">
                                    <select wire:model.defer="financeForm.account_receivable" {{ $isViewMode
                                        ? 'disabled' : '' }}
                                        class="block w-[300px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}">
                                        <option value="">-- Select Ledger --</option>
                                        @foreach($this->ledgers as $ledger)
                                        <option value="{{ $ledger->name }}">{{ $ledger->name }} @if($ledger->code)({{
                                            $ledger->code }})@endif</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 "
                                    style="min-width: 20px;">Sales Revenue</label>
                                <div class="flex-1">
                                    <select wire:model.defer="financeForm.sales_revenue" {{ $isViewMode ? 'disabled'
                                        : '' }}
                                        class="block w-[320px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}">
                                        <option value="">-- Select Ledger --</option>
                                        @foreach($this->ledgers as $ledger)
                                        <option value="{{ $ledger->name }}">{{ $ledger->name }} @if($ledger->code)({{
                                            $ledger->code }})@endif</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Row 2: Currency, Tax -->
                        <div class="flex gap-4" style="gap: 70px !important;">
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 "
                                    style="min-width: 80px;">Currency</label>
                                <div class="flex-1">
                                    <select wire:model.defer="financeForm.currency" {{ $isViewMode ? 'disabled' : '' }}
                                        class="block w-[300px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}">
                                        <option value="LKR">LKR</option>
                                        <option value="USD">USD</option>
                                        <option value="EUR">EUR</option>
                                        <option value="GBP">GBP</option>
                                        <option value="INR">INR</option>
                                    </select>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 "
                                    style="min-width: 60px;">Tax</label>
                                <div class="flex-1">
                                    <input type="text" wire:model.defer="financeForm.tax" {{ $isViewMode ? 'readonly'
                                        : '' }}
                                        class="block w-[320px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}" />
                                </div>
                            </div>
                        </div>

                        <!-- Row 3: Bank -->
                        <div class="flex gap-4">
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 "
                                    style="min-width: 80px;">Bank</label>
                                <div class="flex-1">
                                    <input type="text" wire:model.defer="financeForm.bank" {{ $isViewMode ? 'readonly'
                                        : '' }}
                                        class="block w-[300px] px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}" />
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Tax Tab -->
                    @if($activeTab === 'tax')
                    <div class="space-y-4">

                        {{ $taxForm['tax_mode'] }}
                        <!-- Customer Type -->
                        <div class="flex items-center gap-4">
                            <label class="block text-sm font-medium text-gray-700 min-w-[120px]">Customer Type</label>
                            <select wire:model.live="taxForm.customer_type" {{ $isViewMode ? 'disabled' : '' }}
                                class="w-64 px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}">
                                <option value="non_tax_customer">Non Tax Customer</option>
                                <option value="tax_customer">Tax Customer</option>
                            </select>
                        </div>

                        <!-- Non Tax Customer Options (With / Without Tax) -->
                        @if($taxForm['customer_type'] === 'non_tax_customer')
                        <div class="flex items-start gap-4">
                            <label class="block text-sm font-medium text-gray-700 min-w-[120px] pt-1">Select
                                Options</label>
                            <div class="flex flex-col gap-2">
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" wire:model.live="taxForm.tax_mode" value="with_tax" {{
                                        $isViewMode ? 'disabled' : '' }}
                                        class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500 {{ $isViewMode ? 'cursor-not-allowed' : '' }}">
                                    <span class="text-sm text-gray-700">With Tax</span>
                                </label>
                                <label class="inline-flex items-center gap-2">
                                    <input type="radio" wire:model.live="taxForm.tax_mode" value="without_tax" {{
                                        $isViewMode ? 'disabled' : '' }}
                                        class="h-4 w-4 text-blue-600 border-gray-300 focus:ring-blue-500 {{ $isViewMode ? 'cursor-not-allowed' : '' }}">
                                    <span class="text-sm text-gray-700">Without Tax</span>
                                </label>
                            </div>
                        </div>
                        @endif

                        <!-- Select Taxes (shown for Tax Customer OR Non Tax Customer when "With Tax" selected) -->
                        @if($taxForm['customer_type'] === 'tax_customer' || ($taxForm['customer_type'] ===
                        'non_tax_customer' && ($taxForm['tax_mode'] ?? 'without_tax') === 'with_tax'))
                        <div class="flex items-start gap-4">
                            <label class="block text-sm font-medium text-gray-700 min-w-[120px] pt-2">Select
                                Taxes:</label>
                            <div class="flex flex-col gap-2 bg-gray-50 p-4 rounded-md border border-gray-200">
                                @foreach($taxes as $tax)
                                <div class="flex items-center">
                                    <input type="checkbox" wire:model.live="taxForm.selected_taxes" id="tax_{{ $tax->id }}"
                                        value="{{ $tax->id }}" {{ $isViewMode ? 'disabled' : '' }}
                                        class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 {{ $isViewMode ? 'cursor-not-allowed' : '' }}">
                                    <label for="tax_{{ $tax->id }}" class="ml-2 text-sm text-gray-700">
                                        {{ $tax->description }} ({{ $tax->abbreviation }} - {{
                                        number_format($tax->percentage, 2) }}%)
                                    </label>
                                </div>
                                @endforeach
                                @error('taxForm.selected_taxes')
                                <span class="text-red-500 text-sm">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        @endif
                        @if(isset($taxForm['selected_taxes']) && in_array(1, (array)$taxForm['selected_taxes']) && $taxForm['tax_mode'] !== 'without_tax')
                        <!-- VAT Number (shown when "With Tax" applies and Value Added Tax is among selected taxes) -->
                        <div class="flex items-center gap-4">
                            <label class="block text-sm font-medium text-gray-700 min-w-[120px]">VAT Number<span
                                    class="text-red-500">*</span></label>
                            <input type="text" wire:model.defer="taxForm.vat_number" placeholder="Enter VAT number" {{
                                $isViewMode ? 'readonly' : '' }}
                                class="w-64 px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 {{ $isViewMode ? 'bg-gray-100 cursor-not-allowed' : '' }}" />
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Discount Tab -->
                    @if($activeTab === 'discount')
                    <div class="space-y-3">
                        <!-- Row 1: Accept Discount -->
                        <div class="flex gap-4" style="gap: 70px !important;">
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1"
                                    style="min-width: 120px;">Accept Discount</label>
                                <div class="flex-1">
                                    <div class="flex items-center h-9">
                                        <input type="checkbox" wire:model.defer="discountForm.accept_discount" {{
                                            $isViewMode ? 'disabled' : '' }}
                                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 {{ $isViewMode ? 'cursor-not-allowed bg-gray-100' : '' }}">
                                        <label class="ml-2 text-sm text-gray-700">Yes</label>
                                    </div>
                                    @error('discountForm.accept_discount') <span class="text-red-500 text-sm">{{
                                        $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3 pt-6 mt-6 border-t border-gray-200">
                    <button wire:click="closeModal"
                        class="px-4 py-2 border border-gray-400 rounded-md hover:bg-gray-50">
                        {{ $isViewMode ? 'Close' : 'Cancel' }}
                    </button>
                    @if(!$isViewMode)
                    <button wire:click="save" class="px-4 py-2 rounded-md text-white bg-blue-600 hover:bg-blue-700">Save
                        Customer</button>
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
                <button wire:click="closeDeleteConfirmModal"
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <div class="mt-3">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                        </path>
                    </svg>
                </div>

                <h3 class="text-lg font-medium text-gray-900 text-center mb-2">
                    Delete Customer
                </h3>

                <div class="text-center text-sm text-gray-600 mb-6">
                    <p>Are you sure you want to delete this customer?</p>
                    @if($customerToDelete)
                    @php
                    $customer = \App\Models\Customer::find($customerToDelete);
                    @endphp
                    @if($customer)
                    <p class="font-semibold text-gray-900 mt-2">
                        {{ $customer->name }}
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
                    @if($customerToDelete)
                    <button wire:click="delete({{ $customerToDelete }})"
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