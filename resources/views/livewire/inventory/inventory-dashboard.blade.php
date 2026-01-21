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

            <!-- Available Raw Materials for Production -->
            <div class="grid grid-cols-1 gap-6">
                <div id="available-materials" class="bg-white border rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Available Raw Materials for Production</h3>
                        <div class="flex items-center space-x-2">
                            @if($filterDateFrom || $filterDateTo || ($filterStatus && $filterStatus !== 'not_completed') || $filterJobOrder || $filterCustomer)
                            <button wire:click="resetFilters"
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                Reset Filter
                            </button>
                            @endif
                            <button wire:click="openFilterModal"
                                    class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                </svg>
                                Filter
                            </button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material Code</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lot</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Order</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dimensions</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($availableRawMaterials as $transaction)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="flex items-center space-x-2">
                                                <span class="text-sm font-medium text-gray-900">{{ $transaction->item_code }}</span>
                                                @if(isset($transaction->itemType))
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ ($transaction->itemType === 'Box' || $transaction->itemType === 'Boards') ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                                        {{ $transaction->itemType }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="text-sm text-gray-900 font-mono">{{ $transaction->lot_code }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="text-sm text-gray-900">{{ $transaction->txn_date->format('Y-m-d') }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="text-sm text-gray-900">{{ $transaction->jobOrderNumber ?? 'N/A' }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="text-sm text-gray-900">{{ $transaction->customerName ?? 'N/A' }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="text-sm text-gray-900">{{ $transaction->dimensions ?? 'N/A' }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 font-semibold">
                                                {{ number_format($transaction->qty, 0) }} {{ $transaction->uom }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if(isset($transaction->hasProductionOrder) && $transaction->hasProductionOrder)
                                                <div class="text-xs">
                                                    <div class="text-gray-600 mb-1">Progress: {{ number_format($transaction->productionProgress ?? 0, 1) }}%</div>
                                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                                        <div class="bg-green-600 h-2 rounded-full" style="width: {{ min(100, max(0, $transaction->productionProgress ?? 0)) }}%"></div>
                                                    </div>
                                                    @if(isset($transaction->productionStatus))
                                                        <div class="mt-1 space-y-1">
                                                            @if($transaction->productionStatus === 'completed')
                                                                <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800">Completed</span>
                                                            @elseif($transaction->productionStatus === 'in_production')
                                                                <span class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-800">In Production</span>
                                                            @else
                                                                <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-800">{{ ucfirst($transaction->productionStatus) }}</span>
                                                            @endif

                                                            @if(!empty($transaction->hasPendingGRNToProcess))
                                                                <div>
                                                                    <span class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-700">
                                                                        GRN pending to process to stock
                                                                    </span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-500">Ready</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            @if(isset($transaction->hasProductionOrder) && $transaction->hasProductionOrder)
                                                @if(isset($transaction->productionOrder))
                                                    @php
                                                        $totalQty = $transaction->productionOrder->getTotalQuantity();
                                                        $completedQty = $transaction->productionOrder->getCompletedQuantity();
                                                        // Check if production order quantity matches transaction quantity
                                                        $qtyMismatch = $totalQty != $transaction->qty && $completedQty == 0;
                                                        $isCompleted = $transaction->productionOrder->status === 'completed';
                                                    @endphp
                                                    <div class="space-y-2">
                                                        <div class="text-xs text-gray-500">
                                                            {{ number_format($completedQty, 0) }} / {{ number_format($totalQty, 0) }} PCS
                                                        </div>
                                                        @if($isCompleted)
                                                            <button wire:click="openArchiveModal({{ $transaction->productionOrder->id }}, {{ $transaction->id }})"
                                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                                                                    title="Archive Production Order">
                                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                                                </svg>
                                                                Archive
                                                            </button>
                                                        @elseif($qtyMismatch)
                                                            <button wire:click="deleteProductionOrder({{ $transaction->productionOrder->id }}, {{ $transaction->id }})"
                                                                    wire:confirm="Delete this production order and create a new one with correct quantity ({{ number_format($transaction->qty, 0) }} PCS)?"
                                                                    class="text-xs text-red-600 hover:text-red-800 underline">
                                                                Fix Quantity
                                                            </button>
                                                        @endif
                                                    </div>
                                                @endif
                                            @else
                                                <button wire:click="openProductionOrderModal({{ $transaction->id }})"
                                                        class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                                        title="Start Production Order">
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                    </svg>
                                                    Start Production
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">No raw materials available for production</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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
                    <!-- Success/Error Messages -->
                    @if (session()->has('success'))
                        <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">
                            {{ session()->pull('success') }}
                        </div>
                    @endif
                    @if (session()->has('error'))
                        <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                            {{ session()->pull('error') }}
                        </div>
                    @endif
                    
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
                    @php
                        $jobOrder = $selectedTransaction->getJobOrder();
                        $jobOrderItem = null;
                        $itemType = null;
                        $noOfUps = null;
                        $expectedBoxes = null;
                        $rawMaterialQty = (int) $selectedTransaction->qty;
                        
                        // Find the job order item that matches this transaction
                        if ($selectedTransaction->grn && $selectedTransaction->item_code) {
                            $grnItem = $selectedTransaction->grn->items->firstWhere('material_code', $selectedTransaction->item_code);
                            if ($grnItem && $grnItem->item_id) {
                                $itemType = $grnItem->item_type;
                                if ($grnItem->item_type === 'box') {
                                    $jobOrderBox = $jobOrder->boxes()->where('id', $grnItem->item_id)->first();
                                    if ($jobOrderBox) {
                                        $jobOrderItem = $jobOrderBox;
                                        $noOfUps = (int) ($jobOrderBox->no_of_ups ?? 1);
                                        // Expected boxes = raw material quantity (boards) * no_of_ups
                                        $expectedBoxes = $rawMaterialQty * $noOfUps;
                                    }
                                } elseif ($grnItem->item_type === 'divider') {
                                    $jobOrderDivider = $jobOrder->dividers()->where('id', $grnItem->item_id)->first();
                                    if ($jobOrderDivider) {
                                        $jobOrderItem = $jobOrderDivider;
                                        // Dividers typically don't have no_of_ups, so expected = raw material qty
                                        $noOfUps = 1;
                                        $expectedBoxes = $rawMaterialQty;
                                    }
                                }
                            }
                        }
                        
                        // If no specific item found, use the first available item
                        if (!$jobOrderItem) {
                            $jobOrderBox = $jobOrder->boxes()->first();
                            if ($jobOrderBox) {
                                $jobOrderItem = $jobOrderBox;
                                $itemType = 'box';
                                $noOfUps = (int) ($jobOrderBox->no_of_ups ?? 1);
                                $expectedBoxes = $rawMaterialQty * $noOfUps;
                            } else {
                                $jobOrderDivider = $jobOrder->dividers()->first();
                                if ($jobOrderDivider) {
                                    $jobOrderItem = $jobOrderDivider;
                                    $itemType = 'divider';
                                    $noOfUps = 1;
                                    $expectedBoxes = $rawMaterialQty;
                                }
                            }
                        }
                    @endphp
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
                            @if($jobOrderItem && $itemType === 'box' && $noOfUps)
                            <div>
                                <span class="text-blue-600">No of UPS:</span>
                                <span class="font-medium text-blue-900">{{ number_format($noOfUps, 0) }}</span>
                            </div>
                            <div>
                                <span class="text-blue-600">Expected Boxes by Processing Boards:</span>
                                <span class="font-medium text-blue-900">{{ number_format($expectedBoxes, 0) }} PCS</span>
                            </div>
                            @endif
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
                            @php
                                $jobOrder = $selectedTransaction->getJobOrder();
                                $maxOrderQty = (int) $selectedTransaction->qty; // Default to transaction quantity
                                $itemType = null;
                                $jobOrderItem = null;
                                
                                if ($jobOrder) {
                                    // Try to find the job order item that matches this transaction via GRN item
                                    if ($selectedTransaction->grn && $selectedTransaction->item_code) {
                                        // Get GRN item that matches this transaction's material code
                                        $grnItem = $selectedTransaction->grn->items->firstWhere('material_code', $selectedTransaction->item_code);
                                        if ($grnItem && $grnItem->item_id) {
                                            // Use the item_id and item_type from GRN item
                                            $itemType = $grnItem->item_type;
                                            if ($grnItem->item_type === 'box') {
                                                $jobOrderBox = $jobOrder->boxes()->where('id', $grnItem->item_id)->first();
                                                if ($jobOrderBox) {
                                                    $jobOrderItem = $jobOrderBox;
                                                    $maxOrderQty = (int) $jobOrderBox->order_qty;
                                                }
                                            } elseif ($grnItem->item_type === 'divider') {
                                                $jobOrderDivider = $jobOrder->dividers()->where('id', $grnItem->item_id)->first();
                                                if ($jobOrderDivider) {
                                                    $jobOrderItem = $jobOrderDivider;
                                                    $maxOrderQty = (int) $jobOrderDivider->quantity;
                                                }
                                            }
                                        }
                                    }
                                    
                                    // If no specific item found, use the first available item from the job order
                                    if (!$jobOrderItem) {
                                        $jobOrderBox = $jobOrder->boxes()->first();
                                        if ($jobOrderBox) {
                                            $jobOrderItem = $jobOrderBox;
                                            $itemType = 'box';
                                            $maxOrderQty = (int) $jobOrderBox->order_qty;
                                        } else {
                                            $jobOrderDivider = $jobOrder->dividers()->first();
                                            if ($jobOrderDivider) {
                                                $jobOrderItem = $jobOrderDivider;
                                                $itemType = 'divider';
                                                $maxOrderQty = (int) $jobOrderDivider->quantity;
                                            }
                                        }
                                    }
                                    
                                    // Calculate already completed production quantity for this job order item
                                    $alreadyCompletedQty = 0;
                                    if ($jobOrderItem && $itemType) {
                                        $alreadyCompletedQty = (int) \App\Models\ProductionOrderItem::whereHas('productionOrder', function($query) use ($jobOrder) {
                                                $query->where('job_order_id', $jobOrder->id);
                                            })
                                            ->where('item_type', $itemType)
                                            ->where('item_id', $jobOrderItem->id)
                                            ->sum('completed_quantity');
                                    }
                                    
                                    // Calculate remaining available quantity (job order order qty - already completed)
                                    $remainingAvailableQty = max(0, $maxOrderQty - $alreadyCompletedQty);
                                    
                                    // The effective maximum for the input is the minimum of transaction qty, job order order qty, and remaining available qty
                                    $effectiveMaxQty = min((int) $selectedTransaction->qty, $maxOrderQty, $remainingAvailableQty);
                                    
                                    // Show additional info if there's already completed production
                                    $showCompletedInfo = $alreadyCompletedQty > 0;
                                } else {
                                    $showCompletedInfo = false;
                                    $alreadyCompletedQty = 0;
                                    $remainingAvailableQty = $maxOrderQty;
                                    $effectiveMaxQty = $maxOrderQty;
                                }
                            @endphp
                            <input type="number" wire:model="productionOrderForm.quantity" 
                                   step="0.01" min="0" max="{{ $effectiveMaxQty }}"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                            <p class="mt-1 text-sm text-gray-500">
                                Maximum: {{ number_format($effectiveMaxQty, 0) }} PCS 
                                @if($showCompletedInfo)
                                    ({{ number_format($remainingAvailableQty, 0) }} remaining out of {{ number_format($maxOrderQty, 0) }} total - {{ number_format($alreadyCompletedQty, 0) }} already completed)
                                @else
                                    (Job Order Order Quantity: {{ number_format($maxOrderQty, 0) }} PCS)
                                @endif
                            </p>
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
                                wire:loading.attr="disabled"
                                wire:target="createProductionOrder"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="createProductionOrder">
                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Create Production Order
                            </span>
                            <span wire:loading wire:target="createProductionOrder">
                                Creating...
                            </span>
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Archive Confirmation Modal -->
    @if($showArchiveModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click.self="closeArchiveModal">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-yellow-100 rounded-full mb-4">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>

                <h3 class="text-lg font-medium text-gray-900 text-center mb-2">
                    Archive Production Order
                </h3>

                <div class="text-center text-sm text-gray-600 mb-4">
                    <p>Are you sure you want to archive this completed production order?</p>
                    <p class="mt-2 font-medium">It will be hidden from the dashboard.</p>
                </div>

                <div class="flex space-x-3">
                    <button wire:click="closeArchiveModal"
                            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md transition-colors">
                        Cancel
                    </button>
                    <button wire:click="archiveProductionOrder"
                            class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                        Archive
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filter Modal -->
    @if($showFilterModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click.self="closeFilterModal">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center pb-4 border-b mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Filter Raw Materials</h3>
                    <button wire:click="closeFilterModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <!-- Date Range -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                            <input type="date" wire:model="filterDateFrom" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                            <input type="date" wire:model="filterDateTo" 
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select wire:model="filterStatus" 
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Status</option>
                            <option value="not_completed">In Production & Ready (Default)</option>
                            <option value="ready">Ready Only</option>
                            <option value="in_production">In Production Only</option>
                            <option value="completed">Completed Only</option>
                        </select>
                    </div>

                    <!-- Job Order Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Job Order</label>
                        <select wire:model="filterJobOrder" 
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Job Orders</option>
                            @foreach($jobOrders as $jobOrder)
                                <option value="{{ $jobOrder->id }}">{{ $jobOrder->job_number }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Customer Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Customer</label>
                        <select wire:model="filterCustomer" 
                                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Customers</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                    <button wire:click="resetFilters"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Reset
                    </button>
                    <button wire:click="closeFilterModal"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                        Apply Filter
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
