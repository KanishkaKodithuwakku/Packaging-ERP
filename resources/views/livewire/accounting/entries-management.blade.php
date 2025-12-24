<div>
    <!-- Success/Error Messages -->
    @if (session()->has('message'))
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="space-y-6">
        <!-- Header with Add Entry Dropdown -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Entries</h1>
            </div>
            
            <!-- Add Entry Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Entry
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                
                <div x-show="open" @click.away="open = false" 
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200">
                    <div class="py-1">
                        @foreach($entryTypes as $entryType)
                            <button wire:click="openAddEntryModal({{ $entryType->id }})" 
                                    @click="open = false"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                {{ $entryType->name }}
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Filters -->
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                <div class="lg:col-span-2">
                    <input type="text" 
                           wire:model.live="search" 
                           placeholder="Search entries..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <select wire:model.live="filterEntryType" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        <option value="">Entry Type</option>
                        @foreach($entryTypes as $entryType)
                            <option value="{{ $entryType->id }}">{{ $entryType->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select wire:model.live="filterTag" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        <option value="">Tag</option>
                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}">{{ $tag->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <input type="date" 
                           wire:model.live="filterDateFrom" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                           placeholder="Date From">
                </div>
                <div>
                    <input type="date" 
                           wire:model.live="filterDateTo" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                           placeholder="Date To">
                </div>
            </div>
            <div class="mt-4 flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <select wire:model.live="showAll" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        <option value="all">Show All</option>
                        <option value="today">Today</option>
                        <option value="week">This Week</option>
                        <option value="month">This Month</option>
                    </select>
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        Filter
                    </button>
                </div>
            </div>
        </div>

        <!-- Entries Table -->
        <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
            @if($entries->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ledger</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tag</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Narration</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Debit (Rs)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credit (Rs)</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($entries as $entry)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $entry->entryType->prefix ?? '' }}{{ $entry->number ?? 'N/A' }}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            {{ $entry->date->format('Y-m-d') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            @foreach($entry->entryItems as $item)
                                                <div class="flex items-center mb-1">
                                                    <span class="text-xs font-medium {{ $item->dc == 'D' ? 'text-red-600' : 'text-green-600' }}">
                                                        {{ $item->dc == 'D' ? 'Dr.' : 'Cr.' }}
                                                    </span>
                                                    <span class="ml-2">{{ number_format($item->amount, 2) }}</span>
                                                    <span class="ml-2 text-gray-600">{{ $item->ledger->name }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $typeColors = [
                                                'Journal Voucher' => 'bg-blue-100 text-blue-800',
                                                'Receipt Voucher' => 'bg-green-100 text-green-800',
                                                'Payment Voucher' => 'bg-red-100 text-red-800',
                                                'Contra Voucher' => 'bg-yellow-100 text-yellow-800',
                                                'Sales Voucher' => 'bg-purple-100 text-purple-800',
                                                'Purchase Voucher' => 'bg-orange-100 text-orange-800',
                                            ];
                                            $colorClass = $typeColors[$entry->entryType->name] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                            {{ $entry->entryType->name }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($entry->tag)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                                  style="background-color: #{{ $entry->tag->background }}; color: #{{ $entry->tag->color }}">
                                                {{ $entry->tag->title }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">None</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            {{ $entry->narration ? Str::limit($entry->narration, 50) : '-' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="font-medium">
                                            {{ number_format($entry->dr_total, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="font-medium">
                                            {{ number_format($entry->cr_total, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-1">
                                            <button wire:click="editEntry({{ $entry->id }})" 
                                                    class="text-gray-400 hover:text-blue-600 p-1"
                                                    title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button wire:click="deleteEntry({{ $entry->id }})" 
                                                    class="text-gray-400 hover:text-red-600 p-1"
                                                    title="Delete"
                                                    onclick="return confirm('Are you sure you want to delete this entry?')">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr class="font-medium">
                                <td colspan="5" class="px-6 py-3 text-right text-sm text-gray-900">
                                    Total
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-900">
                                    {{ number_format($entries->sum('dr_total'), 2) }}
                                </td>
                                <td class="px-6 py-3 text-sm text-gray-900">
                                    {{ number_format($entries->sum('cr_total'), 2) }}
                                </td>
                                <td class="px-6 py-3"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200 flex justify-between items-center">
                    <div class="text-sm text-gray-700">
                        Showing {{ $entries->firstItem() }} to {{ $entries->lastItem() }} of {{ $entries->total() }} results
                    </div>
                    <div>
                        {{ $entries->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No entries found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating your first entry.</p>
                    <div class="mt-6">
                        <button wire:click="openAddEntryModal" 
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Entry
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Add/Edit Entry Modal -->
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" wire:key="entry-modal">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-6xl sm:w-full">
                <form wire:submit="{{ $editingEntry ? 'updateEntry' : 'addEntry' }}">
                    <div class="bg-white px-6 pt-6 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">
                            {{ $editingEntry ? 'Edit Entry' : 'Add Entry' }}
                        </h3>
                        
                        <div class="space-y-6">
                            <!-- Entry Header -->
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Number</label>
                                    <input type="text" wire:model="form.number" 
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Auto-generated">
                                    @error('form.number') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                    <input type="date" wire:model="form.date" 
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    @error('form.date') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Entry Type</label>
                                    <div class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                                        @if($form['entrytype_id'])
                                            @php $selectedEntryType = $entryTypes->find($form['entrytype_id']) @endphp
                                            {{ $selectedEntryType ? $selectedEntryType->name : 'Unknown Entry Type' }}
                                        @else
                                            Please select an entry type from the dropdown above
                                        @endif
                                    </div>
                                    @error('form.entrytype_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tag</label>
                                    <select wire:model="form.tag_id" 
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">(None)</option>
                                        @foreach($tags as $tag)
                                            <option value="{{ $tag->id }}">{{ $tag->title }}</option>
                                        @endforeach
                                    </select>
                                    @error('form.tag_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <!-- Entry Items Table -->
                            <div class="border-t pt-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-md font-medium text-gray-900">Entry Items</h4>
                                    <button type="button" wire:click="addEntryItem" 
                                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-md text-sm">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        + Add
                                    </button>
                                </div>
                                
                                @if(count($form['entry_items']) > 0)
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Dr/Cr</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Ledger</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Dr Amount (Rs)</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cr Amount (Rs)</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cur Balance (Rs)</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                @foreach($form['entry_items'] as $index => $item)
                                                    <tr>
                                                        <td class="px-3 py-2">
                                                            <select wire:model.live="form.entry_items.{{ $index }}.dc" 
                                                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                                                                <option value="D">Dr</option>
                                                                <option value="C">Cr</option>
                                                            </select>
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <select wire:model="form.entry_items.{{ $index }}.ledger_id" 
                                                                    class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                                                                <option value="">Please select...</option>
                                                                @foreach($ledgers as $ledger)
                                                                    <option value="{{ $ledger->id }}">{{ $ledger->name }}</option>
                                                                @endforeach
                                                            </select>
                                                            @error('form.entry_items.'.$index.'.ledger_id') 
                                                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                                                            @enderror
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <input type="number" step="0.01" 
                                                                   wire:model.live="form.entry_items.{{ $index }}.dr_amount" 
                                                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                                                                   @if($item['dc'] == 'C') disabled @endif
                                                                   placeholder="0.00">
                                                            @error('form.entry_items.'.$index.'.dr_amount') 
                                                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                                                            @enderror
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <input type="number" step="0.01" 
                                                                   wire:model.live="form.entry_items.{{ $index }}.cr_amount" 
                                                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm"
                                                                   @if($item['dc'] == 'D') disabled @endif
                                                                   placeholder="0.00">
                                                            @error('form.entry_items.'.$index.'.cr_amount') 
                                                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                                                            @enderror
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <div class="flex items-center space-x-2">
                                                                <button type="button" wire:click="addEntryItem" 
                                                                        class="text-green-600 hover:text-green-800 text-sm">
                                                                    + Add
                                                                </button>
                                                                <button type="button" wire:click="removeEntryItem({{ $index }})" 
                                                                        class="text-red-600 hover:text-red-800">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </td>
                                                        <td class="px-3 py-2 text-sm text-gray-500">
                                                            <!-- Current balance would go here -->
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <!-- Totals Row -->
                                    <div class="mt-4 bg-yellow-50 p-3 rounded-md">
                                        <div class="grid grid-cols-3 gap-4 text-sm">
                                            <div class="text-center">
                                                <span class="font-medium text-gray-700">Total</span>
                                                <div class="text-lg font-bold text-gray-900">{{ number_format($this->drTotal, 2) }}</div>
                                            </div>
                                            <div class="text-center">
                                                <span class="font-medium text-gray-700">Total</span>
                                                <div class="text-lg font-bold text-gray-900">{{ number_format($this->crTotal, 2) }}</div>
                                            </div>
                                            <div class="text-center">
                                                <span class="font-medium text-gray-700">Difference</span>
                                                @php
                                                    $difference = $this->drTotal - $this->crTotal;
                                                @endphp
                                                <div class="text-lg font-bold {{ $difference == 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $difference == 0 ? '0.00' : number_format($difference, 2) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center py-4 text-gray-500">
                                        No entry items added yet. Click "+ Add" to start.
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Narration -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Narration</label>
                                <textarea wire:model="form.narration" 
                                          rows="3"
                                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="Enter narration..."></textarea>
                                @error('form.narration') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-4 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" wire:click="testSubmit" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Test Submit
                        </button>
                        <button type="submit" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm {{ $this->drTotal != $this->crTotal ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ $this->drTotal != $this->crTotal ? 'disabled' : '' }}>
                            Submit
                        </button>
                        <button type="button" wire:click="closeModal" 
                                class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
