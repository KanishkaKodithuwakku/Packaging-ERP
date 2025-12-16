<div>
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-800">Inventory Dashboard</h2>
            <div class="space-x-2">
                <button wire:click="refresh"
                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700"
                        title="Refresh dashboard data">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh
                </button>
                <button wire:click="resetTestData"
                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700"
                        title="Reset all transactional data (local env only)">
                    Reset Test Data
                </button>
            </div>
        </div>

        <div class="p-6">
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-blue-50 p-6 rounded-lg">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-lg font-semibold text-blue-800">Raw Materials</h3>
                        @if($availableRawMaterials->count() > 0)
                        <a href="#available-materials" 
                           class="text-xs text-blue-600 hover:text-blue-800 underline">
                            View Available →
                        </a>
                        @endif
                    </div>
                    <p class="text-3xl font-bold text-blue-600">
                        {{ number_format($balanceRawMaterialsQuantity, 0) }}
                    </p>
                    <div class="mt-3 pt-3 border-t border-blue-200 space-y-1">
                        <p class="text-xs text-blue-600">Total Received: <span class="font-semibold">{{ number_format($totalRawMaterialsReceived, 0) }}</span></p>
                        @if($totalRawMaterialsConsumed > 0)
                        <p class="text-xs text-blue-600">Consumed: <span class="font-semibold">{{ number_format($totalRawMaterialsConsumed, 0) }}</span></p>
                        @endif
                        <p class="text-sm text-blue-700 font-medium">Balance Available: <span class="text-base font-bold">{{ number_format($balanceRawMaterialsQuantity, 0) }}</span></p>
                        @if(isset($rawInventoryDetails) && $rawInventoryDetails->count() > 0)
                            <div class="mt-2 space-y-1">
                                @foreach($rawInventoryDetails->take(3) as $raw)
                                    <p class="text-xs text-blue-700">
                                        {{ $raw->item_code ?? 'RAW' }}:
                                        <span class="font-semibold">{{ number_format($raw->total_qty, 0) }} {{ $raw->uom ?? 'PCS' }}</span>
                                    </p>
                                @endforeach
                                @if($rawInventoryDetails->count() > 3)
                                    <p class="text-xs text-blue-500 italic">+{{ $rawInventoryDetails->count() - 3 }} more items</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
                
                <div class="bg-purple-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-purple-800">Consumables</h3>
                    <p class="text-3xl font-bold text-purple-600">
                        {{ number_format($consumablesQuantity, 0) }}
                    </p>
                    <div class="mt-3 pt-3 border-t border-purple-200 space-y-1">
                        @if($consumablesByType->count() > 0)
                            @foreach($consumablesByType->take(3) as $consumable)
                                <p class="text-xs text-purple-600">
                                    {{ $consumable->item_code }}: 
                                    <span class="font-semibold">{{ number_format($consumable->total_qty, 0) }} {{ $consumable->uom }}</span>
                                </p>
                            @endforeach
                            @if($consumablesByType->count() > 3)
                                <p class="text-xs text-purple-500 italic">+{{ $consumablesByType->count() - 3 }} more</p>
                            @endif
                        @else
                            <p class="text-xs text-purple-500">No consumables in stock</p>
                        @endif
                    </div>
                </div>
                
                <div class="bg-yellow-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-yellow-800">Work in Progress</h3>
                    <p class="text-3xl font-bold text-yellow-600">
                        {{ $workInProgressQuantity }}
                    </p>
                    @if(isset($wipDetails) && $wipDetails->count() > 0)
                        <div class="mt-3 pt-3 border-t border-yellow-200 space-y-1">
                            @foreach($wipDetails->take(3) as $wip)
                                @php
                                    $remaining = max(0, ($wip->quantity ?? 0) - ($wip->completed_quantity ?? 0));
                                @endphp
                                <p class="text-xs text-yellow-700">
                                    {{ method_exists($wip, 'getItemCode') ? $wip->getItemCode() : ($wip->item_type . '#' . $wip->item_id) }}:
                                    <span class="font-semibold">{{ number_format($remaining, 0) }} PCS</span>
                                </p>
                            @endforeach
                            @if($wipDetails->count() > 3)
                                <p class="text-xs text-yellow-500 italic">+{{ $wipDetails->count() - 3 }} more items</p>
                            @endif
                        </div>
                    @endif
                </div>
                
                <div class="bg-green-50 p-6 rounded-lg">
                    <h3 class="text-lg font-semibold text-green-800">Finished Goods</h3>
                    <p class="text-3xl font-bold text-green-600">
                        @php
                            $fgQty = $inventoryByCategory->where('category', 'FG')->first();
                            echo number_format($fgQty ? $fgQty->total_qty : 0, 0);
                        @endphp
                    </p>
                    @if(isset($fgInventoryDetails) && $fgInventoryDetails->count() > 0)
                        <div class="mt-3 pt-3 border-t border-green-200 space-y-1">
                            @foreach($fgInventoryDetails->take(3) as $fg)
                                <p class="text-xs text-green-700">
                                    {{ $fg->item_code ?? 'FG' }}:
                                    <span class="font-semibold">{{ number_format($fg->total_qty, 0) }} {{ $fg->uom ?? 'PCS' }}</span>
                                </p>
                            @endforeach
                            @if($fgInventoryDetails->count() > 3)
                                <p class="text-xs text-green-500 italic">+{{ $fgInventoryDetails->count() - 3 }} more items</p>
                            @endif
                        </div>
                    @endif
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
                                <span class="text-lg font-bold">{{ number_format($category->total_qty, 0) }}</span>
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
                                <span class="text-lg font-bold">{{ number_format($warehouse->total_qty, 0) }}</span>
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
                                    {{ number_format($item->qty_available, 0) }} {{ $item->uom }}
                                </span>
                            </div>
                        @empty
                            <p class="text-gray-500">No low stock items</p>
                        @endforelse
                    </div>
                </div>

                <div id="available-materials" class="bg-white border rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Available Raw Materials for Production</h3>
                    <div class="space-y-3">
                        @forelse($availableRawMaterials as $transaction)
                            <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between mb-1">
                                        <p class="font-medium text-gray-900">{{ $transaction->item_code }}</p>
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 font-semibold">
                                            {{ number_format($transaction->qty, 0) }} {{ $transaction->uom }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-gray-600 space-y-1">
                                        <p>Lot: <span class="font-mono">{{ $transaction->lot_code }}</span></p>
                                        <p>Date: {{ $transaction->txn_date->format('Y-m-d') }}</p>
                                        @if($transaction->getJobOrder())
                                            <p>Job Order: <span class="font-medium">{{ $transaction->getJobOrder()->job_number }}</span></p>
                                        @endif
                                        @if(isset($transaction->customerName) && $transaction->customerName !== 'N/A')
                                            <p>
                                                <span class="font-medium text-gray-900">Customer:</span>
                                                <span class="text-gray-700">{{ $transaction->customerName }}</span>
                                            </p>
                                        @endif
                                        @if(isset($transaction->dimensions) && $transaction->dimensions !== 'N/A')
                                            <p>
                                                <span class="font-medium text-gray-900">Dimensions:</span>
                                                <span class="text-gray-700">{{ $transaction->dimensions }}</span>
                                            </p>
                                        @endif
                                        @if(isset($transaction->hasProductionOrder) && $transaction->hasProductionOrder)
                                            <p class="mt-2">
                                                <span class="font-medium text-gray-900">Production Order:</span> 
                                                <span class="text-blue-600">{{ $transaction->productionOrderNumber ?? 'N/A' }}</span>
                                            </p>
                                            <p>
                                                <span class="font-medium text-gray-900">Status:</span>
                                                @if(isset($transaction->productionStatus))
                                                    @if($transaction->productionStatus === 'completed')
                                                        <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800">Completed</span>
                                                    @elseif($transaction->productionStatus === 'in_production')
                                                        <span class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-800">In Production</span>
                                                    @else
                                                        <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-800">{{ ucfirst($transaction->productionStatus) }}</span>
                                                    @endif
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                <div class="ml-4">
                                    @if(isset($transaction->hasProductionOrder) && $transaction->hasProductionOrder)
                                        <!-- Show Progress -->
                                        <div class="min-w-[120px]">
                                            <div class="text-xs text-gray-600 mb-1 text-center">
                                                Progress: {{ number_format($transaction->productionProgress ?? 0, 1) }}%
                                            </div>
                                            <div class="w-full bg-gray-200 rounded-full h-2.5">
                                                <div class="bg-green-600 h-2.5 rounded-full transition-all duration-300" 
                                                     style="width: {{ min(100, max(0, $transaction->productionProgress ?? 0)) }}%"></div>
                                            </div>
                                            @if(isset($transaction->productionOrder))
                                                @php
                                                    $totalQty = $transaction->productionOrder->getTotalQuantity();
                                                    $completedQty = $transaction->productionOrder->getCompletedQuantity();
                                                @endphp
                                                <div class="text-xs text-gray-500 mt-1 text-center">
                                                    {{ number_format($completedQty, 0) }} / {{ number_format($totalQty, 0) }} PCS
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <!-- Show Start Production Button -->
                                        <button wire:click="openProductionOrderModal({{ $transaction->id }})"
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                                title="Start Production Order">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            Start Production
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-gray-500">No raw materials available for production</p>
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
                                <span class="font-medium">{{ number_format($selectedTransaction->qty, 0) }} {{ $selectedTransaction->uom }}</span>
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
                            <p class="mt-1 text-sm text-gray-500">Maximum: {{ number_format($selectedTransaction->qty, 0) }} {{ $selectedTransaction->uom }}</p>
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
