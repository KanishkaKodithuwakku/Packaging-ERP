<div>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Entry Type Management</h1>
                <p class="mt-1 text-sm text-gray-600">Manage voucher types for journal entries</p>
            </div>
            <button wire:click="openAddEntryTypeModal" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Entry Type
            </button>
        </div>

        <!-- Search and Filters -->
        <div class="bg-white p-4 rounded-lg shadow">
            <div class="flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <input type="text" 
                           wire:model.live="search" 
                           placeholder="Search entry types..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex gap-2">
                    <select wire:model.live="filterBaseType" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Base Types</option>
                        <option value="0">Journal</option>
                        <option value="1">Receipt</option>
                        <option value="2">Payment</option>
                        <option value="3">Contra</option>
                    </select>
                    <select wire:model.live="filterNumbering" class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Numbering</option>
                        <option value="1">Auto Numbering</option>
                        <option value="2">Manual Numbering</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Entry Types Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Entry Types</h2>
            </div>
            
            @if($entryTypes->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Label</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Base Type</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Numbering</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prefix/Suffix</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Restriction</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($entryTypes as $entryType)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $entryType->label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $entryType->name }}</div>
                                            @if($entryType->description)
                                                <div class="text-sm text-gray-500">{{ $entryType->description }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $baseTypes = [
                                                0 => ['name' => 'Journal', 'color' => 'blue'],
                                                1 => ['name' => 'Receipt', 'color' => 'green'],
                                                2 => ['name' => 'Payment', 'color' => 'red'],
                                                3 => ['name' => 'Contra', 'color' => 'purple']
                                            ];
                                            $baseType = $baseTypes[$entryType->base_type] ?? ['name' => 'Unknown', 'color' => 'gray'];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $baseType['color'] }}-100 text-{{ $baseType['color'] }}-800">
                                            {{ $baseType['name'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $entryType->numbering == 1 ? 'Auto' : 'Manual' }}
                                        @if($entryType->numbering == 1 && $entryType->zero_padding > 0)
                                            <div class="text-xs text-gray-500">Padding: {{ $entryType->zero_padding }} digits</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if($entryType->prefix || $entryType->suffix)
                                            <div class="text-xs">
                                                @if($entryType->prefix)
                                                    <div>Prefix: {{ $entryType->prefix }}</div>
                                                @endif
                                                @if($entryType->suffix)
                                                    <div>Suffix: {{ $entryType->suffix }}</div>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-400">None</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $restrictions = [
                                                1 => ['name' => 'None', 'color' => 'gray'],
                                                2 => ['name' => 'At least one Bank/Cash', 'color' => 'yellow'],
                                                3 => ['name' => 'Exactly one Bank/Cash', 'color' => 'orange']
                                            ];
                                            $restriction = $restrictions[$entryType->restriction_bankcash] ?? ['name' => 'Unknown', 'color' => 'gray'];
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $restriction['color'] }}-100 text-{{ $restriction['color'] }}-800">
                                            {{ $restriction['name'] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <button wire:click="editEntryType({{ $entryType->id }})" 
                                                    class="text-blue-600 hover:text-blue-900">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button wire:click="deleteEntryType({{ $entryType->id }})" 
                                                    class="text-red-600 hover:text-red-900"
                                                    onclick="return confirm('Are you sure you want to delete this entry type?')">
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
                    {{ $entryTypes->links() }}
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No entry types found</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating your first entry type.</p>
                    <div class="mt-6">
                        <button wire:click="openAddEntryTypeModal" 
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Entry Type
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Add/Edit Entry Type Modal -->
    <div x-data="{ open: @entangle('showModal') }" x-show="open" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>
            
            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form wire:submit="{{ $editingEntryType ? 'updateEntryType' : 'addEntryType' }}">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                            {{ $editingEntryType ? 'Edit Entry Type' : 'Add Entry Type' }}
                        </h3>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="entry_label" class="block text-sm font-medium text-gray-700">Label</label>
                                    <input type="text" wire:model="form.label" id="entry_label" 
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('form.label') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label for="entry_name" class="block text-sm font-medium text-gray-700">Name</label>
                                    <input type="text" wire:model="form.name" id="entry_name" 
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('form.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <div>
                                <label for="entry_description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea wire:model="form.description" id="entry_description" rows="2"
                                          class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                                @error('form.description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label for="base_type" class="block text-sm font-medium text-gray-700">Base Type</label>
                                <select wire:model="form.base_type" id="base_type" 
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="0">Journal</option>
                                    <option value="1">Receipt</option>
                                    <option value="2">Payment</option>
                                    <option value="3">Contra</option>
                                </select>
                                @error('form.base_type') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label for="numbering" class="block text-sm font-medium text-gray-700">Numbering</label>
                                <select wire:model="form.numbering" id="numbering" 
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="1">Auto Numbering</option>
                                    <option value="2">Manual Numbering</option>
                                </select>
                                @error('form.numbering') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                            
                            <div x-show="$wire.form.numbering == 1" class="grid grid-cols-3 gap-4">
                                <div>
                                    <label for="prefix" class="block text-sm font-medium text-gray-700">Prefix</label>
                                    <input type="text" wire:model="form.prefix" id="prefix" 
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('form.prefix') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label for="suffix" class="block text-sm font-medium text-gray-700">Suffix</label>
                                    <input type="text" wire:model="form.suffix" id="suffix" 
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('form.suffix') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label for="zero_padding" class="block text-sm font-medium text-gray-700">Zero Padding</label>
                                    <input type="number" wire:model="form.zero_padding" id="zero_padding" min="0" max="10"
                                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    @error('form.zero_padding') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                            <div>
                                <label for="restriction_bankcash" class="block text-sm font-medium text-gray-700">Bank/Cash Restriction</label>
                                <select wire:model="form.restriction_bankcash" id="restriction_bankcash" 
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="1">None</option>
                                    <option value="2">At least one Bank/Cash account</option>
                                    <option value="3">Exactly one Bank/Cash account</option>
                                </select>
                                @error('form.restriction_bankcash') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
</div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            {{ $editingEntryType ? 'Update Entry Type' : 'Add Entry Type' }}
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
</div>