<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Entry Search</h1>
            <p class="text-gray-600">Search and filter journal entries with advanced criteria</p>
        </div>
        <div class="text-right">
            <div class="text-sm text-gray-500">Kings Packaging ERP</div>
            <div class="text-sm text-gray-700">{{ $fromDate ? \Carbon\Carbon::parse($fromDate)->format('d-M-Y') : 'Start of Year' }} to {{ $toDate ? \Carbon\Carbon::parse($toDate)->format('d-M-Y') : 'End of Year' }}</div>
        </div>
    </div>

    <!-- Search Form -->
    <div class="bg-white p-6 rounded-lg shadow-sm border">
        <form wire:submit.prevent="search">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <!-- Ledgers -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ledgers</label>
                    <select wire:model="ledgerIds" multiple 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            size="4">
                        @foreach($ledgerOptions as $id => $name)
                            <option value="{{ $id }}" {{ $id == 0 ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Hold Ctrl/Cmd to select multiple ledgers</p>
                </div>

                <!-- Entrytypes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Entrytypes</label>
                    <select wire:model="entrytypeIds" multiple 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            size="4">
                        @foreach($entrytypeOptions as $id => $name)
                            <option value="{{ $id }}" {{ $id == 0 ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Entry Number -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Entry Number</label>
                    <div class="flex gap-2">
                        <select wire:model="entryNumberRestriction" 
                                class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            <option value="1">Equal to</option>
                            <option value="2">Less than or equal to</option>
                            <option value="3">Greater than or equal to</option>
                            <option value="4">In between</option>
                        </select>
                        <input type="text" wire:model="entryNumber1" 
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                               placeholder="Entry number">
                        @if($entryNumberRestriction == 4)
                            <span class="px-2 py-2 text-gray-500">and</span>
                            <input type="text" wire:model="entryNumber2" 
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                   placeholder="End number">
                        @endif
                    </div>
                </div>

                <!-- Dr or Cr -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Dr or Cr</label>
                    <select wire:model="amountDc" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                        <option value="0">(ANY)</option>
                        <option value="D">Dr</option>
                        <option value="C">Cr</option>
                    </select>
                </div>

                <!-- Amount -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                    <div class="flex gap-2">
                        <select wire:model="amountRestriction" 
                                class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            <option value="1">Equal to</option>
                            <option value="2">Less than or equal to</option>
                            <option value="3">Greater than or equal to</option>
                            <option value="4">In between</option>
                        </select>
                        <input type="number" step="0.01" wire:model="amount1" 
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                               placeholder="Amount">
                        @if($amountRestriction == 4)
                            <span class="px-2 py-2 text-gray-500">and</span>
                            <input type="number" step="0.01" wire:model="amount2" 
                                   class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                   placeholder="End amount">
                        @endif
                    </div>
                </div>

                <!-- From Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">From date</label>
                    <input type="date" wire:model="fromDate" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>

                <!-- To Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To date</label>
                    <input type="date" wire:model="toDate" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                </div>

                <!-- Tags -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tags</label>
                    <select wire:model="tagIds" multiple 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            size="4">
                        @foreach($tagOptions as $id => $name)
                            <option value="{{ $id }}" {{ $id == 0 ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Narration -->
                <div class="lg:col-span-3">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Narration contains</label>
                    <input type="text" wire:model="narration" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                           placeholder="Search in narration">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex gap-3">
                <button type="submit" 
                        class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Search
                </button>
                <button type="button" wire:click="clear"
                        class="inline-flex items-center px-6 py-3 bg-gray-300 border border-gray-300 rounded-md font-semibold text-sm text-gray-700 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Clear
                </button>
            </div>
        </form>
    </div>

    <!-- Search Results -->
    @if($showEntries)
        <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Search Results</h3>
                <p class="text-sm text-gray-500">Found {{ $entries->total() }} entries</p>
            </div>

            @if($entries->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ledger</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tag</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Debit Amount (Rs)</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Credit Amount (Rs)</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($entries as $entry)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $entry->date->format('d-M-Y') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $this->formatEntryNumber($entry->number, $entry->entrytype_id) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">
                                        <div class="space-y-1">
                                            @foreach($this->getLedgerDetails($entry->id, $entry->entryitem_id) as $detail)
                                                <div class="text-sm {{ $detail['is_current'] ? 'font-semibold text-blue-600' : 'text-gray-600' }}">
                                                    {{ $detail['dc'] }} [{{ $detail['ledger_code'] }}] {{ $detail['ledger_name'] }} ({{ $detail['dc'] }} {{ number_format((float)$detail['amount'], 2) }})
                                                </div>
                                            @endforeach
                                            @if($entry->narration)
                                                <div class="text-xs text-gray-500 italic">
                                                    {{ Str::limit($entry->narration, 60) }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $this->getEntryTypeName($entry->entrytype_id) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $this->getTagName($entry->tag_id) }}</td>
                                    
                                    @if($entry->entryitem_dc == 'D')
                                        <td class="px-4 py-3 text-sm text-right font-mono">Dr {{ number_format($entry->entryitem_amount, 2) }}</td>
                                        <td class="px-4 py-3 text-sm text-right font-mono"></td>
                                    @else
                                        <td class="px-4 py-3 text-sm text-right font-mono"></td>
                                        <td class="px-4 py-3 text-sm text-right font-mono">Cr {{ number_format($entry->entryitem_amount, 2) }}</td>
                                    @endif
                                    
                                    <td class="px-4 py-3 text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="#" class="text-blue-600 hover:text-blue-900">View</a>
                                            <a href="#" class="text-green-600 hover:text-green-900">Edit</a>
                                            <a href="#" class="text-red-600 hover:text-red-900">Delete</a>
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
                <div class="px-6 py-12 text-center">
                    <div class="text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No entries found</h3>
                        <p class="mt-1 text-sm text-gray-500">Try adjusting your search criteria.</p>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
