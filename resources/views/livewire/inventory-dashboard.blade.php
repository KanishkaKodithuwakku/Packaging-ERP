<div>
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Inventory Dashboard</h2>
            <div class="space-x-2">
                <button wire:click="resetTestData"
                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700"
                        title="Reset all transactional data (local env only)">
                    Reset Test Data
                </button>
            </div>
        </div>

        <div class="p-6">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-blue-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-blue-800">Raw Materials</h3>
                    <p class="text-3xl font-bold text-blue-600">
                        {{ number_format($balanceRawMaterialsQuantity, 2) }}
                    </p>
                    <div class="mt-3 pt-3 border-t border-blue-200 space-y-1">
                        <p class="text-xs text-blue-600">Total Received: <span class="font-semibold">{{ number_format($totalRawMaterialsReceived, 2) }}</span></p>
                        @if($totalRawMaterialsConsumed > 0)
                        <p class="text-xs text-blue-600">Consumed: <span class="font-semibold">{{ number_format($totalRawMaterialsConsumed, 2) }}</span></p>
                        @endif
                        <p class="text-sm text-blue-700 font-medium">Balance Available: <span class="text-base font-bold">{{ number_format($balanceRawMaterialsQuantity, 2) }}</span></p>
                    </div>
                </div>
                
                <div class="bg-yellow-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-yellow-800">Work in Progress</h3>
                    <p class="text-3xl font-bold text-yellow-600">
                        {{ $workInProgressQuantity }}
                    </p>
                </div>
                
                <div class="bg-green-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-green-800">Finished Goods</h3>
                    <p class="text-3xl font-bold text-green-600">
                        @php
                            $fgQty = $inventoryByCategory->where('category', 'FG')->first();
                            echo number_format($fgQty ? $fgQty->total_qty : 0, 2);
                        @endphp
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
                        {{-- Temporarily disabled to fix memory issue --}}
                        @if(false && count($recentTransactions) > 0)
                        @foreach($recentTransactions as $transaction)
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded hover:bg-gray-100 transition-colors">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2">
                                        <p class="font-medium text-gray-900">{{ $transaction->lot_code }}</p>
                                        @if($transaction->category)
                                            @php
                                                $categoryColors = [
                                                    'FG' => 'bg-green-100 text-green-800',
                                                    'RAW' => 'bg-blue-100 text-blue-800',
                                                    'WIP' => 'bg-yellow-100 text-yellow-800',
                                                ];
                                                $categoryColor = $categoryColors[$transaction->category] ?? 'bg-gray-100 text-gray-800';
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $categoryColor }}">
                                                {{ $transaction->category }}
                                            </span>
                                        @endif
                                        @if($transaction->getJobOrder())
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                📋 {{ $transaction->getJobOrder()->job_number }}
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">{{ ucfirst($transaction->txn_type) }}</p>
                                    @if($transaction->getJobOrder())
                                        <div class="mt-1 space-y-1">
                                            <p class="text-xs text-blue-600 font-medium">
                                                🏭 {{ $transaction->getJobOrder()->supplier->name ?? 'Unknown Supplier' }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                👤 {{ $transaction->getJobOrder()->customer->name ?? 'Unknown Customer' }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                                <div class="text-right ml-4">
                                    <span class="text-sm font-medium text-gray-900">{{ $transaction->qty }} {{ $transaction->uom }}</span>
                                    <p class="text-xs text-gray-500 mt-1">{{ $transaction->txn_date }}</p>
                                    @if($transaction->getJobOrder() && $transaction->txn_type === 'receipt')
                                        @php
                                            // Use pre-loaded hasProductionOrder flag to avoid N+1 queries
                                            $hasProductionOrder = $transaction->hasProductionOrder ?? false;
                                            $jobOrder = $transaction->getJobOrder();
                                        @endphp
                                        
                                        @if(!$hasProductionOrder)
                                            <button wire:click="openProductionOrderModal({{ $transaction->id }})" 
                                                    class="mt-2 inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                                Start Production
                                            </button>
                                        @else
                                            @if($transaction->productionOrderId ?? null)
                                            <a href="{{ route('production-order-detail', $transaction->productionOrderId) }}" 
                                               wire:navigate
                                               class="mt-2 inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded-md text-blue-600 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                In Production
                                            </a>
                                        @endif
                                    @endif
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

    <!-- Production Order Modal -->
    @if($showProductionOrderModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click.self="closeProductionOrderModal">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Start Production Order</h3>
                    <button wire:click="closeProductionOrderModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                @if($selectedTransaction)
                <div class="space-y-4">
                    <!-- Transaction Details -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-2">Transaction Details</h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-600">Transaction ID:</span>
                                <span class="font-medium">{{ $selectedTransaction->lot_code }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Quantity:</span>
                                <span class="font-medium">{{ $selectedTransaction->qty }} {{ $selectedTransaction->uom }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Type:</span>
                                <span class="font-medium">{{ ucfirst($selectedTransaction->txn_type) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-600">Date:</span>
                                <span class="font-medium">{{ $selectedTransaction->txn_date }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Job Order Details -->
                    @if($selectedTransaction->getJobOrder())
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h4 class="font-medium text-blue-900 mb-2">Job Order Details</h4>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-blue-600">Job Order:</span>
                                <span class="font-medium text-blue-900">{{ $selectedTransaction->getJobOrder()->job_number }}</span>
                            </div>
                            <div>
                                <span class="text-blue-600">Supplier:</span>
                                <span class="font-medium text-blue-900">{{ $selectedTransaction->getJobOrder()->supplier->name ?? 'Unknown' }}</span>
                            </div>
                            <div>
                                <span class="text-blue-600">Customer:</span>
                                <span class="font-medium text-blue-900">{{ $selectedTransaction->getJobOrder()->customer->name ?? 'Unknown' }}</span>
                            </div>
                            <div>
                                <span class="text-blue-600">Status:</span>
                                <span class="font-medium text-blue-900">{{ ucfirst($selectedTransaction->getJobOrder()->status) }}</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Production Order Form -->
                    <div class="space-y-4">
                        <h4 class="font-medium text-gray-900">Production Order Details</h4>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Production Order Number</label>
                                <input type="text" wire:model="productionOrderForm.production_order_number" 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Start Date</label>
                                <input type="date" wire:model="productionOrderForm.date" 
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Production Quantity</label>
                            <input type="number" wire:model="productionOrderForm.quantity" 
                                   step="0.01" min="0" max="{{ $selectedTransaction->qty }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            <p class="mt-1 text-sm text-gray-500">Maximum: {{ $selectedTransaction->qty }} {{ $selectedTransaction->uom }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea wire:model="productionOrderForm.notes" rows="3"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm"></textarea>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <button wire:click="closeProductionOrderModal" 
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                            Cancel
                        </button>
                        <button wire:click="createProductionOrder" 
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Create Production Order
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
