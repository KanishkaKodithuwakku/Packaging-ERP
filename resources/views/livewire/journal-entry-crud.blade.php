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
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Journal Entries</h1>
                <p class="mt-1 text-sm text-gray-600">Manage accounting journal entries</p>
            </div>
            <button wire:click="openAddEntryModal" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Journal Entry
            </button>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white p-4 rounded-lg shadow">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" 
                           wire:model.live="search" 
                           placeholder="Search journal entries..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex gap-2">
                    <select wire:model.live="filterEntryType" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Entry Types</option>
                        @foreach($entryTypes as $entryType)
                            <option value="{{ $entryType->id }}">{{ $entryType->name }}</option>
                        @endforeach
                    </select>
                    <select wire:model.live="filterTag" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Tags</option>
                        @foreach($tags as $tag)
                            <option value="{{ $tag->id }}">{{ $tag->title }}</option>
                        @endforeach
                    </select>
                    <input type="date" 
                           wire:model.live="filterDateFrom" 
                           class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="date" 
                           wire:model.live="filterDateTo" 
                           class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <!-- Journal Entries Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Journal Entries</h2>
            </div>
            
            @if($entries->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entry #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entry Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tag</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Debit Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Credit Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($entries as $entry)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $entry->number ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $entry->date->format('Y-m-d') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($entry->dr_total, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ number_format($entry->cr_total, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $balance = $entry->dr_total - $entry->cr_total;
                                        @endphp
                                        @if($balance == 0)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Balanced
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Unbalanced
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button wire:click="viewEntry({{ $entry->id }})" 
                                                    class="text-green-600 hover:text-green-900">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </button>
                                            <button wire:click="editEntry({{ $entry->id }})" 
                                                    class="text-blue-600 hover:text-blue-900">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button wire:click="deleteEntry({{ $entry->id }})" 
                                                    class="text-red-600 hover:text-red-900"
                                                    onclick="return confirm('Are you sure you want to delete this journal entry?')">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $entries->links() }}
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No journal entries found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating your first journal entry.</p>
                    <div class="mt-6">
                <button wire:click="openAddEntryModal" 
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Journal Entry
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Add/Edit Journal Entry Modal -->
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                <form wire:submit="{{ $editingEntry ? 'updateEntry' : 'addEntry' }}">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            {{ $editingEntry ? 'Edit Journal Entry' : 'Add Journal Entry' }}
                        </h3>
                        
                        <div class="space-y-4">
                            <!-- Entry Header -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="entry_date" class="block text-sm font-medium text-gray-700">Entry Date</label>
                                    <input type="date" wire:model="form.date" id="entry_date" 
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('form.date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label for="entry_type" class="block text-sm font-medium text-gray-700">Entry Type</label>
                                    <select wire:model="form.entrytype_id" id="entry_type" 
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="">Select Entry Type</option>
                                        @foreach($entryTypes as $entryType)
                                            <option value="{{ $entryType->id }}">{{ $entryType->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('form.entrytype_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="entry_number" class="block text-sm font-medium text-gray-700">Entry Number</label>
                                    <input type="number" wire:model="form.number" id="entry_number" 
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('form.number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label for="entry_tag" class="block text-sm font-medium text-gray-700">Tag (Optional)</label>
                                    <select wire:model="form.tag_id" id="entry_tag" 
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="">Select Tag</option>
                                        @foreach($tags as $tag)
                                            <option value="{{ $tag->id }}">{{ $tag->title }}</option>
                                        @endforeach
                                    </select>
                                    @error('form.tag_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <div>
                                <label for="entry_narration" class="block text-sm font-medium text-gray-700">Narration</label>
                                <textarea wire:model="form.narration" id="entry_narration" rows="2"
                                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                                @error('form.narration') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <!-- Entry Items -->
                            <div class="border-t pt-4">
                                <div class="flex justify-between items-center mb-4">
                                    <h4 class="text-md font-medium text-gray-900">Entry Items</h4>
                                    <button type="button" wire:click="addEntryItem" 
                                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-md text-sm">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Add Item
                                    </button>
                                </div>
                                
                                @if(count($form['entry_items']) > 0)
                                    <div class="space-y-3">
                                        @foreach($form['entry_items'] as $index => $item)
                                            <div class="grid grid-cols-12 gap-2 items-end p-3 border border-gray-200 rounded-md">
                                                <div class="col-span-4">
                                                    <label class="block text-xs font-medium text-gray-700">Ledger</label>
                                                    <select wire:model="form.entry_items.{{ $index }}.ledger_id" 
                                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                                                        <option value="">Select Ledger</option>
                                                        @foreach($ledgers as $ledger)
                                                            <option value="{{ $ledger->id }}">{{ $ledger->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                
                                                <div class="col-span-2">
                                                    <label class="block text-xs font-medium text-gray-700">Amount</label>
                                                    <input type="number" step="0.01" wire:model="form.entry_items.{{ $index }}.amount" 
                                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                                                </div>
                                                
                                                <div class="col-span-2">
                                                    <label class="block text-xs font-medium text-gray-700">Type</label>
                                                    <select wire:model="form.entry_items.{{ $index }}.dc" 
                                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                                                        <option value="D">Debit</option>
                                                        <option value="C">Credit</option>
                                                    </select>
                                                </div>
                                                
                                                <div class="col-span-2">
                                                    <label class="block text-xs font-medium text-gray-700">Reconciliation Date</label>
                                                    <input type="date" wire:model="form.entry_items.{{ $index }}.reconciliation_date" 
                                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                                                </div>
                                                
                                                <div class="col-span-2 flex justify-end">
                                                    <button type="button" wire:click="removeEntryItem({{ $index }})" 
                                                            class="text-red-600 hover:text-red-900">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                    <!-- Balance Summary -->
                                    <div class="mt-4 p-3 bg-gray-50 rounded-md">
                                        <div class="grid grid-cols-3 gap-4 text-sm">
                                            <div>
                                                <span class="font-medium text-gray-700">Total Debit:</span>
                                                <span class="ml-2 font-bold text-gray-900">{{ number_format($this->drTotal, 2) }}</span>
                                            </div>
                                            <div>
                                                <span class="font-medium text-gray-700">Total Credit:</span>
                                                <span class="ml-2 font-bold text-gray-900">{{ number_format($this->crTotal, 2) }}</span>
                                            </div>
                                            <div>
                                                <span class="font-medium text-gray-700">Difference:</span>
                                                @php
                                                    $difference = $this->drTotal - $this->crTotal;
                                                @endphp
                                                <span class="ml-2 font-bold {{ $difference == 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ number_format($difference, 2) }}
                                                </span>
                                            </div>
                                        </div>
                                        
                                        @if($difference != 0)
                                            <div class="mt-2 text-sm text-red-600">
                                                ⚠️ Entry is not balanced. Debit and Credit totals must be equal.
                                            </div>
                                        @else
                                            <div class="mt-2 text-sm text-green-600">
                                                ✅ Entry is balanced.
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-center py-4 text-gray-500">
                                        No entry items added yet. Click "Add Item" to start.
                                    </div>
                                @endif
                            </div>
                        </div>
</div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm {{ $this->drTotal != $this->crTotal ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ $this->drTotal != $this->crTotal ? 'disabled' : '' }}>
                            {{ $editingEntry ? 'Update Entry' : 'Add Entry' }}
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