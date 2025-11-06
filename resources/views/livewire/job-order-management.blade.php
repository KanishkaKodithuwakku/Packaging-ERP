<div>
    <!-- Success/Error Messages -->
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
        <!-- Header with Add Job Order Button -->
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Job Orders</h1>
                <p class="text-gray-600">Manage packaging job orders from creation to production</p>
            </div>

            <div class="flex items-center space-x-4">
                <input type="text" wire:model.live="search" placeholder="Search job orders..."
                    class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 min-w-[200px]">
                @if($filterSupplier || $filterCustomer || $filterStatus || $filterDateFrom || $filterDateTo)
                <button wire:click="resetFilters"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 0 1-9.201 2.466l-.312-.311h2.433a.75.75 0 0 0 0-1.5H3.989a.75.75 0 0 0-.75.75v4.242a.75.75 0 0 0 1.5 0v-2.43l.31.31a7 7 0 0 0 11.712-3.138.75.75 0 0 0-1.449-.39Zm1.23-3.723a.75.75 0 0 0 .219-.53V2.929a.75.75 0 0 0-1.5 0V5.36l-.31-.31A7 7 0 0 0 3.239 8.188a.75.75 0 1 0 1.448.389A5.5 5.5 0 0 1 13.89 6.11l.311.31h-2.432a.75.75 0 0 0 0 1.5h4.243a.75.75 0 0 0 .53-.219Z" clip-rule="evenodd" />
                    </svg>
                    Reset Filter
                </button>
                @else
                <button wire:click="openFilterModal"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                        </path>
                    </svg>
                    Filter
                </button>
                @endif
                <button wire:click="openCreateModal"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Job Order
                </button>
            </div>
        </div>

        <!-- Job Orders Table -->
        <div class="bg-white rounded-lg shadow-sm border">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Job #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Items & Progress</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($jobOrders as $jobOrder)
                        <tr class="hover:bg-gray-50 cursor-pointer" >
                            <td class="px-6 py-4 whitespace-nowrap" wire:navigate
                            href="{{ route('job-order-detail', $jobOrder->id) }}">
                                <div class="text-sm font-medium text-gray-900">{{ $jobOrder->supplier_po_number }}</div>
                                <div class="text-xs text-gray-500">JO #{{ $jobOrder->job_number }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" wire:navigate
                            href="{{ route('job-order-detail', $jobOrder->id) }}">
                                {{ $jobOrder->date->format('Y-m-d') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" wire:navigate
                            href="{{ route('job-order-detail', $jobOrder->id) }}">
                                <div class="text-sm text-gray-900">{{ $jobOrder->supplier->name }}</div>
                                <div class="text-xs text-gray-500">{{ $jobOrder->supplier->code }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" wire:navigate
                            href="{{ route('job-order-detail', $jobOrder->id) }}">
                                <div class="text-sm text-gray-900">{{ $jobOrder->customer->name }}</div>
                                <div class="text-xs text-gray-500">{{ $jobOrder->customer->code }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" wire:navigate
                            href="{{ route('job-order-detail', $jobOrder->id) }}">
                                <div class="flex flex-col space-y-1">
                                    @if($jobOrder->boxes->count() > 0)
                                    @foreach($jobOrder->boxes as $box)
                                    @php
                                    $purchasedQty = \App\Models\PurchaseOrderItem::where('item_type', 'box')
                                    ->where('item_id', $box->id)
                                    ->whereHas('purchaseOrder', function($query) {
                                        $query->where('status', '!=', 'cancelled');
                                    })
                                    ->sum('quantity');
                                    $progress = $box->order_qty > 0 ? min(100, ($purchasedQty / $box->order_qty) * 100)
                                    : 0;

                                    // Get GRN received quantities from Purchase Orders
                                    $grnReceivedQty = \App\Models\GRNItem::whereHas('grn', function($q) use ($jobOrder)
                                    {
                                        $q->whereHas('purchaseOrder', function($po) use ($jobOrder) {
                                            $po->where('job_order_id', $jobOrder->id);
                                        });
                                    })
                                    ->where('item_type', 'box')
                                    ->where('item_id', $box->id)
                                    ->sum('qty_received_partial');

                                    $grnProgress = $box->order_qty > 0 ? min(100, ($grnReceivedQty / $box->order_qty) *
                                    100) : 0;
                                    @endphp
                                    <div class="flex flex-col space-y-1">
                                        <div class="flex items-center space-x-2">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $box->order_qty }} Boxes
                                            </span>
                                            <div class="flex-1 bg-gray-200 rounded-full h-2 max-w-20">
                                                <div class="bg-green-500 h-2 rounded-full"
                                                    style="width: {{ $progress }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-600">{{ $purchasedQty }}/{{ $box->order_qty
                                                }}</span>
                                        </div>
                                        <div class="flex items-center space-x-2 ml-2">
                                            <span class="text-xs text-gray-500">GRN:</span>
                                            <div class="flex-1 bg-gray-200 rounded-full h-1.5 max-w-16">
                                                <div class="bg-orange-500 h-1.5 rounded-full"
                                                    style="width: {{ $grnProgress }}%"></div>
                                            </div>
                                            <span class="text-xs {{ $grnReceivedQty > 0 ? 'text-orange-600' : 'text-gray-400' }}">
                                                {{ $grnReceivedQty }}/{{ $box->order_qty }}
                                            </span>
                                            @if($grnReceivedQty > 0)
                                                <span class="text-xs text-orange-500 font-medium">{{ number_format($grnProgress, 1) }}%</span>
                                            @else
                                                <span class="text-xs text-gray-400">Not Received</span>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                    @endif
                                    @if($jobOrder->dividers->count() > 0)
                                    @foreach($jobOrder->dividers as $divider)
                                    @php
                                    $purchasedQty = \App\Models\PurchaseOrderItem::where('item_type', 'divider')
                                    ->where('item_id', $divider->id)
                                    ->whereHas('purchaseOrder', function($query) {
                                        $query->where('status', '!=', 'cancelled');
                                    })
                                    ->sum('quantity');
                                    $progress = $divider->quantity > 0 ? min(100, ($purchasedQty / $divider->quantity) *
                                    100) : 0;

                                    // Get GRN received quantities from Purchase Orders
                                    $grnReceivedQty = \App\Models\GRNItem::whereHas('grn', function($q) use ($jobOrder)
                                    {
                                        $q->whereHas('purchaseOrder', function($po) use ($jobOrder) {
                                            $po->where('job_order_id', $jobOrder->id);
                                        });
                                    })
                                    ->where('item_type', 'divider')
                                    ->where('item_id', $divider->id)
                                    ->sum('qty_received_partial');

                                    $grnProgress = $divider->quantity > 0 ? min(100, ($grnReceivedQty /
                                    $divider->quantity) * 100) : 0;
                                    @endphp
                                    <div class="flex flex-col space-y-1">
                                        <div class="flex items-center space-x-2">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ $divider->quantity }} Dividers
                                            </span>
                                            <div class="flex-1 bg-gray-200 rounded-full h-2 max-w-20">
                                                <div class="bg-green-500 h-2 rounded-full"
                                                    style="width: {{ $progress }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-600">{{ $purchasedQty }}/{{
                                                $divider->quantity }}</span>
                                        </div>
                                        <div class="flex items-center space-x-2 ml-2">
                                            <span class="text-xs text-gray-500">GRN:</span>
                                            <div class="flex-1 bg-gray-200 rounded-full h-1.5 max-w-16">
                                                <div class="bg-orange-500 h-1.5 rounded-full"
                                                    style="width: {{ $grnProgress }}%"></div>
                                            </div>
                                            <span class="text-xs {{ $grnReceivedQty > 0 ? 'text-orange-600' : 'text-gray-400' }}">
                                                {{ $grnReceivedQty }}/{{ $divider->quantity }}
                                            </span>
                                            @if($grnReceivedQty > 0)
                                                <span class="text-xs text-orange-500 font-medium">{{ number_format($grnProgress, 1) }}%</span>
                                            @else
                                                <span class="text-xs text-gray-400">Not Received</span>
                                            @endif
                                        </div>
                                    </div>
                                    @endforeach
                                    @endif
                                    @if($jobOrder->boxes->count() == 0 && $jobOrder->dividers->count() == 0)
                                    <span class="text-gray-400 text-xs">No items</span>
                                    @endif

                                    @php
                                    // Calculate overall GRN progress for this job order
                                    $totalOrderedQty = $jobOrder->boxes->sum('order_qty') + $jobOrder->dividers->sum('quantity');
                                    $totalGRNReceivedQty = \App\Models\GRNItem::whereHas('grn', function($q) use ($jobOrder) {
                                        $q->whereHas('purchaseOrder', function($po) use ($jobOrder) {
                                            $po->where('job_order_id', $jobOrder->id);
                                        });
                                    })->sum('qty_received_partial');
                                    $overallGRNProgress = $totalOrderedQty > 0 ? min(100, ($totalGRNReceivedQty / $totalOrderedQty) * 100) : 0;
                                    @endphp

                                    @if($totalOrderedQty > 0)
                                    <div class="mt-2 pt-2 border-t border-gray-200">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-medium text-gray-700">Overall GRN Progress:</span>
                                            <span class="text-xs {{ $totalGRNReceivedQty > 0 ? 'text-orange-600' : 'text-gray-400' }}">
                                                {{ number_format($overallGRNProgress, 1) }}%
                                            </span>
                                        </div>
                                        <div class="mt-1 bg-gray-200 rounded-full h-2">
                                            <div class="bg-orange-500 h-2 rounded-full" style="width: {{ $overallGRNProgress }}%"></div>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ number_format($totalGRNReceivedQty, 0) }}/{{ number_format($totalOrderedQty, 0) }} received
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" wire:navigate
                            href="{{ route('job-order-detail', $jobOrder->id) }}">
                                @php
                                $statusColors = [
                                'draft' => 'bg-gray-100 text-gray-800',
                                'confirmed' => 'bg-yellow-100 text-yellow-800',
                                'in_production' => 'bg-blue-100 text-blue-800',
                                'completed' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800'
                                ];
                                @endphp
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$jobOrder->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ ucfirst(str_replace('_', ' ', $jobOrder->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <button wire:click.stop="openDispatchModal({{ $jobOrder->id }})"
                                        class="text-green-600 hover:text-green-900" title="View Dispatch Status">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                            </path>
                                        </svg>
                                    </button>
                                    <button wire:click.stop="editJobOrder({{ $jobOrder->id }})"
                                        class="text-blue-600 hover:text-blue-900" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                            </path>
                                        </svg>
                                    </button>
                                    <button wire:click.stop="openDeleteConfirmModal({{ $jobOrder->id }})"
                                        onclick="event.stopPropagation(); event.preventDefault(); return false;"
                                        class="text-red-600 hover:text-red-900" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
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
            <div class="px-6 py-3 border-t border-gray-200">
                {{ $jobOrders->links() }}
            </div>
        </div>
    </div>

    <!-- Create Job Order Modal -->
    @include('livewire.job-order-management.create-job-order-modal')

    <!-- Dispatch Comparison Modal -->
    @if($showDispatchModal && $selectedJobOrderForDispatch)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click.self="closeDispatchModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-5xl shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <!-- Modal Header -->
                    <div class="flex justify-between items-center pb-4 border-b">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">
                                Dispatch Status - Job Order {{ $selectedJobOrderForDispatch->job_number }}
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ optional($selectedJobOrderForDispatch->supplier)->name ?? 'N/A' }} →
                                {{ optional($selectedJobOrderForDispatch->customer)->name ?? 'N/A' }}
                            </p>
                        </div>
                        <button wire:click="closeDispatchModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Comparison Table -->
                    <div class="mt-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Material Code</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Order Qty</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Dispatched Qty</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Remaining</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Progress</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($dispatchComparison as $item)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                    {{ $item['type'] === 'box' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                                    {{ ucfirst($item['type']) }}
                                                </span>
                                                <div class="mt-1 text-xs text-gray-500">{{ $item['description'] }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['material_code'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                                {{ number_format($item['order_qty'], 2) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                                <span class="font-medium {{ $item['dispatched_qty'] > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                                    {{ number_format($item['dispatched_qty'], 2) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                                <span class="{{ $item['remaining_qty'] > 0 ? 'text-orange-600' : 'text-gray-400' }}">
                                                    {{ number_format($item['remaining_qty'], 2) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center space-x-2">
                                                    <div class="flex-1 bg-gray-200 rounded-full h-2 max-w-32">
                                                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $item['progress'] }}%"></div>
                                                    </div>
                                                    <span class="text-xs text-gray-600 min-w-[45px] text-right">
                                                        {{ number_format($item['progress'], 1) }}%
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                                No items found in this job order
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if(count($dispatchComparison) > 0)
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" colspan="2">Total</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                            {{ number_format(collect($dispatchComparison)->sum('order_qty'), 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600 text-right">
                                            {{ number_format(collect($dispatchComparison)->sum('dispatched_qty'), 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-orange-600 text-right">
                                            {{ number_format(collect($dispatchComparison)->sum('remaining_qty'), 2) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $totalOrdered = collect($dispatchComparison)->sum('order_qty');
                                                $totalDispatched = collect($dispatchComparison)->sum('dispatched_qty');
                                                $overallProgress = $totalOrdered > 0 ? min(100, ($totalDispatched / $totalOrdered) * 100) : 0;
                                            @endphp
                                            <div class="flex items-center space-x-2">
                                                <div class="flex-1 bg-gray-200 rounded-full h-2 max-w-32">
                                                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ $overallProgress }}%"></div>
                                                </div>
                                                <span class="text-xs font-medium text-gray-900 min-w-[45px] text-right">
                                                    {{ number_format($overallProgress, 1) }}%
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-3 mt-8 pt-6 border-t">
                        <button type="button"
                                wire:click="closeDispatchModal"
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Filter Modal -->
    @if($showFilterModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click.self="closeFilterModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <!-- Modal Header -->
                    <div class="flex justify-between items-center pb-4 border-b">
                        <h3 class="text-lg font-medium text-gray-900">
                            Filter Job Orders
                        </h3>
                        <button wire:click="closeFilterModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Filter Form -->
                    <div class="mt-6">
                        <div class="space-y-3">
                            <!-- Row 1: Start Date, End Date -->
                            <div class="flex gap-3">
                                <div class="flex items-center gap-3 flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">Start Date</label>
                                    <div class="flex-1">
                                        <input type="date" wire:model.live="filterDateFrom"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">End Date</label>
                                    <div class="flex-1">
                                        <input type="date" wire:model.live="filterDateTo"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                            </div>

                            <!-- Row 2: Supplier, Customer -->
                            <div class="flex gap-3">
                                <div class="flex items-center gap-3 flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">Supplier</label>
                                    <div class="flex-1">
                                        <select wire:model.live="filterSupplier"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">All Suppliers</option>
                                            @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">Customer</label>
                                    <div class="flex-1">
                                        <select wire:model.live="filterCustomer"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">All Customers</option>
                                            @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Row 3: Status -->
                            <div class="flex gap-3">
                                <div class="flex items-center gap-3" style="width: calc(51% - 0.75rem);">
                                    <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">Status</label>
                                    <div class="flex-1">
                                        <select wire:model.live="filterStatus"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">All Status</option>
                                            <option value="draft">Draft</option>
                                            <option value="confirmed">Confirmed</option>
                                            <option value="in_production">In Production</option>
                                            <option value="completed">Completed</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-3 mt-8 pt-6 border-t">
                            <button type="button"
                                    wire:click="closeFilterModal"
                                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteConfirmModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>

                    <h3 class="text-lg font-medium text-gray-900 text-center mb-2">
                        Delete Job Order
                    </h3>

                    <div class="text-center text-sm text-gray-600 mb-6">
                        <p>Are you sure you want to delete this job order?</p>
                        @if($jobOrderToDelete)
                            @php
                                $jobOrder = \App\Models\JobOrder::find($jobOrderToDelete);
                            @endphp
                            @if($jobOrder)
                                <p class="font-semibold text-gray-900 mt-2">
                                    {{ $jobOrder->supplier_po_number }}
                                </p>
                                @if($jobOrder->job_number)
                                    <p class="text-xs text-gray-500 mt-1">
                                        Job #{{ $jobOrder->job_number }}
                                    </p>
                                @endif
                                <p class="text-xs text-gray-500 mt-1">
                                    This action cannot be undone.
                                </p>
                            @endif
                        @endif
                    </div>

                    <div class="flex space-x-3">
                        <button wire:click="closeDeleteConfirmModal"
                                class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md transition-colors">
                            Cancel
                        </button>
                        @if($jobOrderToDelete)
                        <button wire:click="deleteJobOrder({{ $jobOrderToDelete }})"
                                class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                            Delete
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
