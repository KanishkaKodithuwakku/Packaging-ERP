<div>
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Inventory Dashboard</h2>
        </div>

        <div class="p-6">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-blue-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-blue-800">Raw Materials</h3>
                    <p class="text-3xl font-bold text-blue-600">
                        {{ $inventoryByCategory->where('category', 'RAW')->sum('total_qty') }}
                    </p>
                </div>
                
                <div class="bg-yellow-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-yellow-800">Work in Progress</h3>
                    <p class="text-3xl font-bold text-yellow-600">
                        {{ $inventoryByCategory->where('category', 'WIP')->sum('total_qty') }}
                    </p>
                </div>
                
                <div class="bg-green-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-green-800">Finished Goods</h3>
                    <p class="text-3xl font-bold text-green-600">
                        {{ $inventoryByCategory->where('category', 'FG')->sum('total_qty') }}
                    </p>
                </div>
            </div>

            <!-- Inventory by Category Chart -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <div class="bg-white border rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Inventory by Category</h3>
                    <div class="space-y-3">
                        @foreach($inventoryByCategory as $category)
                            <div class="flex justify-between items-center">
                                <span class="font-medium">{{ $category->category }}</span>
                                <span class="text-lg font-bold">{{ $category->total_qty }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white border rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Inventory by Warehouse</h3>
                    <div class="space-y-3">
                        @foreach($inventoryByWarehouse as $warehouse)
                            <div class="flex justify-between items-center">
                                <span class="font-medium">{{ $warehouse->warehouse }}</span>
                                <span class="text-lg font-bold">{{ $warehouse->total_qty }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Low Stock Items -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white border rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-red-600">Low Stock Items</h3>
                    <div class="space-y-3">
                        @forelse($lowStockItems as $item)
                            <div class="flex justify-between items-center p-3 bg-red-50 rounded">
                                <div>
                                    <p class="font-medium">{{ $item->item_code }}</p>
                                    <p class="text-sm text-gray-600">{{ $item->lot_code }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                    {{ $item->qty_available }} {{ $item->uom }}
                                </span>
                            </div>
                        @empty
                            <p class="text-gray-500">No low stock items</p>
                        @endforelse
                    </div>
                </div>

                <div class="bg-white border rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Recent Transactions</h3>
                    <div class="space-y-3">
                        @forelse($recentTransactions as $transaction)
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                                <div>
                                    <p class="font-medium">{{ $transaction->lot_code }}</p>
                                    <p class="text-sm text-gray-600">{{ ucfirst($transaction->txn_type) }}</p>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-medium">{{ $transaction->qty }} {{ $transaction->uom }}</span>
                                    <p class="text-xs text-gray-500">{{ $transaction->txn_date }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500">No recent transactions</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
