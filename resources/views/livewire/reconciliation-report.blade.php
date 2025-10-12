<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Ledger Reconciliation</h1>
                    <p class="text-gray-600">Reconcile bank and cash account transactions with bank statements</p>
                </div>

                <!-- Ledger Selection Form -->
                <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                    <form wire:submit.prevent="generateReconciliationReport">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Ledger Account</label>
                                <select wire:model.live="selectedLedgerId" 
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <option value="">Select a ledger...</option>
                                    @foreach($availableLedgers as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                <input type="date" 
                                       wire:model.live="startDate"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                <input type="date" 
                                       wire:model.live="endDate"
                                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            </div>

                            <div class="flex items-center">
                                <div class="flex items-center h-full">
                                    <input type="checkbox" 
                                           wire:model.live="showAll"
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label class="ml-2 block text-sm text-gray-700">Show all entries</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4 flex gap-2">
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Submit
                            </button>
                            <button type="button" 
                                    wire:click="$set('selectedLedgerId', null)"
                                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Clear
                            </button>
                        </div>
                    </form>
                </div>

                @if($showEntries && $ledger)
                    <!-- Action Buttons -->
                    <div class="mb-6 flex gap-2">
                        <button class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            DOWNLOAD .CSV
                        </button>
                        <button class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            DOWNLOAD .XLS
                        </button>
                        <button class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                            </svg>
                            PRINT
                        </button>
                    </div>

                    <!-- Company Name and Subtitle -->
                    <div class="text-center mb-6">
                        <div class="text-xl font-bold text-gray-900">{{ $companyName }}</div>
                        <div class="text-lg text-gray-700">{{ $subtitle }}</div>
                    </div>

                    <!-- Ledger Information -->
                    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <table class="w-full text-sm">
                                <tr>
                                    <td class="font-medium text-gray-700 py-1">Bank or cash account:</td>
                                    <td class="text-gray-900">{{ $ledger->type == 1 ? 'Yes' : 'No' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-medium text-gray-700 py-1">Notes:</td>
                                    <td class="text-gray-900">{{ $ledger->notes ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <table class="w-full text-sm">
                                <tr>
                                    <td class="font-medium text-gray-700 py-1">{{ $openingTitle }}:</td>
                                    <td class="text-gray-900 font-mono">{{ $this->formatCurrency($openingBalance['dc'], $openingBalance['amount']) }}</td>
                                </tr>
                                <tr>
                                    <td class="font-medium text-gray-700 py-1">{{ $closingTitle }}:</td>
                                    <td class="text-gray-900 font-mono">{{ $this->formatCurrency($closingBalance['dc'], $closingBalance['amount']) }}</td>
                                </tr>
                                <tr>
                                    <td class="font-medium text-gray-700 py-1">Debit {{ $recPendingTitle }}:</td>
                                    <td class="text-gray-900 font-mono">Dr {{ number_format($reconciliationPending['dr_total'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="font-medium text-gray-700 py-1">Credit {{ $recPendingTitle }}:</td>
                                    <td class="text-gray-900 font-mono">Cr {{ number_format($reconciliationPending['cr_total'], 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Reconciliation Form -->
                    <div class="mb-6">
                        <form wire:submit.prevent="reconcile">
                            <!-- Reconciliation Entries Table -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Number</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ledger</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tag</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Debit Amount (Rs)</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Credit Amount (Rs)</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reconciliation Date</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($entries as $index => $entryItem)
                                            <tr class="{{ $entryItem->reconciliation_date ? 'bg-green-50' : '' }}">
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $entryItem->entry->date->format('d-M-Y') }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $this->formatEntryNumber($entryItem->entry->number, $entryItem->entry->entrytype_id) }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-700">
                                                    <div class="space-y-1">
                                                        @foreach($entryItem->entry->entryItems as $item)
                                                            <div class="text-sm {{ $item->id == $entryItem->id ? 'font-semibold text-blue-600' : 'text-gray-600' }}">
                                                                {{ $item->dc }} [{{ $item->ledger->code }}] {{ $item->ledger->name }} ({{ $item->dc }} {{ number_format($item->amount, 2) }})
                                                            </div>
                                                        @endforeach
                                                        @if($entryItem->entry->narration)
                                                            <div class="text-xs text-gray-500 italic">
                                                                {{ Str::limit($entryItem->entry->narration, 60) }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $this->getEntryTypeName($entryItem->entry->entrytype_id) }}</td>
                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $this->getTagName($entryItem->entry->tag_id) }}</td>
                                                
                                                @if($entryItem->dc == 'D')
                                                    <td class="px-4 py-3 text-sm text-right font-mono">Dr {{ number_format($entryItem->amount, 2) }}</td>
                                                    <td class="px-4 py-3 text-sm text-right font-mono"></td>
                                                @else
                                                    <td class="px-4 py-3 text-sm text-right font-mono"></td>
                                                    <td class="px-4 py-3 text-sm text-right font-mono">Cr {{ number_format($entryItem->amount, 2) }}</td>
                                                @endif
                                                
                                                <td class="px-4 py-3 text-sm">
                                                    <input type="hidden" wire:model="reconciliationData.{{ $index }}.id">
                                                    <input type="date" 
                                                           wire:model="reconciliationData.{{ $index }}.recdate"
                                                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 text-sm"
                                                           placeholder="Reconciliation date">
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Submit Button -->
                            <div class="mt-4 flex justify-end">
                                <button type="submit" 
                                        class="inline-flex items-center px-6 py-3 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Reconcile
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Summary -->
                    <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                        <div class="text-center">
                            <h3 class="text-lg font-semibold text-blue-900 mb-2">Reconciliation Summary</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-medium">Opening Balance:</span>
                                    <span class="ml-2 font-mono">{{ $this->formatCurrency($openingBalance['dc'], $openingBalance['amount']) }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Closing Balance:</span>
                                    <span class="ml-2 font-mono">{{ $this->formatCurrency($closingBalance['dc'], $closingBalance['amount']) }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Unreconciled Debit:</span>
                                    <span class="ml-2 font-mono">Dr {{ number_format($reconciliationPending['dr_total'], 2) }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Unreconciled Credit:</span>
                                    <span class="ml-2 font-mono">Cr {{ number_format($reconciliationPending['cr_total'], 2) }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Total Entries:</span>
                                    <span class="ml-2 font-semibold">{{ count($entries) }}</span>
                                </div>
                                <div>
                                    <span class="font-medium">Show All:</span>
                                    <span class="ml-2">{{ $showAll ? 'Yes' : 'No (Unreconciled only)' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Flash Messages -->
                @if (session()->has('message'))
                    <div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                        {{ session('message') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
