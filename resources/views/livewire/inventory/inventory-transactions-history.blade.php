<div>
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Inventory Transactions History</h2>
        </div>

        <div class="p-6">
            <!-- Filters -->
            <div class="mb-6 bg-gray-50 p-4 rounded-lg">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lot Code</label>
                        <input type="text" wire:model.live="filters.lot_code" placeholder="Search lot code..." 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                        <select wire:model.live="filters.category" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                            <option value="">All Categories</option>
                            <option value="RAW">Raw Materials</option>
                            <option value="WIP">Work in Progress</option>
                            <option value="FG">Finished Goods</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Warehouse</label>
                        <select wire:model.live="filters.warehouse" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                            <option value="">All Warehouses</option>
                            <option value="MAIN">Main Warehouse</option>
                            <option value="PRODUCTION">Production</option>
                            <option value="FINISHED_GOODS">Finished Goods</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Transaction Type</label>
                        <select wire:model.live="filters.txn_type" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                            <option value="">All Types</option>
                            <option value="receipt">Receipt</option>
                            <option value="consume">Consume</option>
                            <option value="produce">Produce</option>
                            <option value="delivery">Delivery</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                        <input type="date" wire:model.live="filters.date_from" 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                        <input type="date" wire:model.live="filters.date_to" 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                    </div>
                </div>
                <div class="mt-4">
                    <button wire:click="clearFilters" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                        Clear Filters
                    </button>
                </div>
            </div>

            <!-- Transaction Stats -->
            @if($stats->count() > 0)
                <div class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                    @foreach($stats as $stat)
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="text-sm font-medium text-blue-800">{{ ucfirst($stat->txn_type) }}</h3>
                            <p class="text-2xl font-bold text-blue-600">{{ $stat->count }}</p>
                            <p class="text-sm text-blue-500">{{ $stat->total_qty }} units</p>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Transactions Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lot Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Warehouse</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($transactions as $transaction)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transaction->txn_date }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $transaction->lot_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transaction->item_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($transaction->category == 'RAW') bg-blue-100 text-blue-800
                                        @elseif($transaction->category == 'WIP') bg-yellow-100 text-yellow-800
                                        @else bg-green-100 text-green-800
                                        @endif">
                                        {{ $transaction->category }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($transaction->txn_type == 'receipt') bg-green-100 text-green-800
                                        @elseif($transaction->txn_type == 'consume') bg-red-100 text-red-800
                                        @elseif($transaction->txn_type == 'produce') bg-blue-100 text-blue-800
                                        @else bg-purple-100 text-purple-800
                                        @endif">
                                        {{ ucfirst($transaction->txn_type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transaction->qty }} {{ $transaction->uom }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transaction->warehouse }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $transaction->remarks }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">No transactions found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>
</div>
