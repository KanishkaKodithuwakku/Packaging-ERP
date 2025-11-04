<div>
    <!-- Header -->
    <div class="px-6 py-4 mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Purchase Orders</h1>
        <p class="mt-2 text-gray-600">Manage supplier purchase orders generated from job orders.</p>
    </div>

    <!-- Success/Error Messages -->
    @if (session()->has('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {!! session('success') !!}
    </div>
    @endif

    @if (session()->has('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ session('error') }}
    </div>
    @endif

    @if (session()->has('info'))
    <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
        {{ session('info') }}
    </div>
    @endif

    <!-- Purchase Orders Table -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">All Purchase Orders</h3>
                <button wire:click="openCreateModal({{ $selectedJobOrderId ?? '' }})"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Generate Purchase Order
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO
                            Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job
                            Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            GRN Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($purchaseOrders as $po)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $po->po_number }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $po->date->format('Y-m-d') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $po->supplier->name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $po->supplier->code ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $po->jobOrder->supplier_po_number ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $po->jobOrder->job_number ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $po->items->count() }} items
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            Rs. {{ number_format($po->getTotalAmount(), 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                            $statusColors = [
                            'draft' => 'bg-gray-100 text-gray-800',
                            'confirmed' => 'bg-green-100 text-green-800',
                            'cancelled' => 'bg-red-100 text-red-800',
                            ];
                            @endphp
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$po->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($po->status) }}
                            </span>
                            @if($po->status === 'cancelled' && $po->cancellation_reason)
                                <div class="mt-1 text-xs text-gray-500">
                                    Reason: {{ Str::limit($po->cancellation_reason, 50) }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @php
                            // Get GRN status for this purchase order
                            $grn = \App\Models\GRN::where('purchase_order_id', $po->id)->first();
                            if ($grn) {
                            $totalExpected = $grn->getTotalExpectedQuantity();
                            $totalReceived = $grn->getTotalPartiallyReceivedQuantity();
                            $totalPending = $grn->getTotalPendingQuantity();
                            $receivingPercentage = $totalExpected > 0 ? ($totalReceived / $totalExpected) * 100 : 0;

                            if ($grn->isFullyReceived()) {
                            $grnStatus = 'Fully Received';
                            $grnColor = 'bg-green-100 text-green-800';
                            $progressColor = 'bg-green-500';
                            $progressWidth = 100;
                            } elseif ($grn->hasPartialReceiving()) {
                            $grnStatus = 'Partial (' . number_format($receivingPercentage, 1) . '%)';
                            $grnColor = 'bg-yellow-100 text-yellow-800';
                            $progressColor = 'bg-yellow-500';
                            $progressWidth = $receivingPercentage;
                            } else {
                            $grnStatus = 'Not Received';
                            $grnColor = 'bg-gray-100 text-gray-800';
                            $progressColor = 'bg-gray-300';
                            $progressWidth = 0;
                            }
                            } else {
                            $grnStatus = 'No GRN';
                            $grnColor = 'bg-gray-100 text-gray-800';
                            $progressColor = 'bg-gray-300';
                            $progressWidth = 0;
                            }
                            @endphp
                            <div class="flex flex-col space-y-2">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $grnColor }}">
                                    {{ $grnStatus }}
                                </span>
                                @if($grn)
                                <div class="flex items-center space-x-2">
                                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                                        <div class="{{ $progressColor }} h-2 rounded-full transition-all duration-300"
                                            style="width: {{ $progressWidth }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-600 min-w-0">
                                        {{ number_format($totalReceived, 0) }}/{{ number_format($totalExpected, 0) }}
                                    </span>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <button wire:click="viewPurchaseOrder({{ $po->id }})"
                                    class="text-gray-400 hover:text-gray-600" title="View Purchase Order">
                                    <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-width="2" d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z"/>
                                        <path stroke="currentColor" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                      </svg>

                                </button>

                                @if($po->status === 'draft')
                                <button wire:click="openPhoneConfirmModal({{ $po->id }})"
                                    class="text-green-600 hover:text-green-900" title="Confirm Purchase Order">
                                    <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 3v4a1 1 0 0 1-1 1H5m4 6 2 2 4-4m4-8v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1Z"/>
                                      </svg>

                                </button>
                                {{-- <button wire:click="openPhoneConfirmModal({{ $po->id }})"
                                    class="text-yellow-600 hover:text-yellow-900" title="Confirm (Update Prices)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                        </path>
                                    </svg>
                                </button> --}}
                                @endif

                                {{-- @if($po->status === 'draft')
                                <button wire:click="openCancelConfirmModal({{ $po->id }})"
                                    class="text-red-600 hover:text-red-900"
                                    title="Cancel Purchase Order">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                @endif --}}

                                @if($po->status === 'confirmed')
                                <button wire:click="openGRNConfirmModal({{ $po->id }})"
                                    class="{{ $po->grn->isNotEmpty() ? 'text-gray-300 cursor-not-allowed' : 'text-gray-400 hover:text-gray-600' }}"
                                    title="{{ $po->grn->isNotEmpty() ? 'GRN Already Created' : 'Create GRN' }}"
                                    {{ $po->grn->isNotEmpty() ? 'disabled' : '' }}>
                                    <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17 20v-5h2v6.988H3V15h1.98v5H17Z"/>
                                        <path d="m6.84 14.522 8.73 1.825.369-1.755-8.73-1.825-.369 1.755Zm1.155-4.323 8.083 3.764.739-1.617-8.083-3.787-.739 1.64Zm3.372-5.481L10.235 6.08l6.859 5.704 1.132-1.362-6.859-5.704ZM15.57 17H6.655v2h8.915v-2ZM12.861 3.111l6.193 6.415 1.414-1.415-6.43-6.177-1.177 1.177Z"/>
                                      </svg>

                                </button>
                                @endif

                                @if($po->status === 'confirmed' || $po->status === 'draft')
                                <button wire:click="openCancelConfirmModal({{ $po->id }})"
                                    class="text-red-600 hover:text-red-900"
                                    title="Cancel Purchase Order">
                                    <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/>
                                      </svg>

                                </button>
                                @endif

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 text-center">No purchase orders</h3>
                                <p class="mt-1 text-sm text-gray-500 text-center">Get started by generating a purchase order from a job
                                    order.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Purchase Order Modal -->
    @if($showCreateModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Generate Purchase Order</h3>
                    <button wire:click="closeCreateModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="mt-6 space-y-4">
                    @if($selectedJobOrder)
                    <!-- Selected Job Order Info -->
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h4 class="font-medium text-blue-900 mb-2">Selected Job Order</h4>
                        <div class="text-sm text-blue-700">
                            <p><strong>Job Number:</strong> {{ $selectedJobOrder->supplier_po_number }}</p>
                            <p><strong>Supplier:</strong> {{ $selectedJobOrder->supplier->name ?? 'N/A' }}</p>
                            <p><strong>Customer:</strong> {{ $selectedJobOrder->customer->name ?? 'N/A' }}</p>
                            <p><strong>Items:</strong> {{ $selectedJobOrder->boxes->count() +
                                $selectedJobOrder->dividers->count() }} items</p>
                        </div>
                    </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Job Order</label>
                        <select wire:model.live="form.job_order_id"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Job Order</option>
                            @forelse($jobOrders as $jobOrder)
                            <option value="{{ $jobOrder->id }}">
                                {{ $jobOrder->supplier_po_number }} - {{ $jobOrder->supplier->name ?? 'N/A' }}
                            </option>
                            @empty
                            <option value="" disabled>No confirmed job orders available</option>
                            @endforelse
                        </select>
                        @error('form.job_order_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        @if($jobOrders->count() === 0)
                        <p class="mt-1 text-sm text-yellow-600">No confirmed job orders available. Please confirm a job
                            order first.</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Supplier</label>
                        <select wire:model="form.supplier_id"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">
                                {{ $supplier->name }} ({{ $supplier->code }})
                            </option>
                            @endforeach
                        </select>
                        @error('form.supplier_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">PO Date</label>
                        <input type="date" wire:model="form.date"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('form.date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea wire:model="form.notes" rows="3"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Additional notes for the purchase order..."></textarea>
                        @error('form.notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t mt-6">
                    <button wire:click="closeCreateModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition-colors">
                        Cancel
                    </button>
                    <button wire:click="generatePurchaseOrder"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors">
                        Generate Purchase Order
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- View Purchase Order Modal -->
    @if($showViewModal && $selectedPurchaseOrder)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Purchase Order Details</h3>
                    <button wire:click="closeViewModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Display Format Selection -->
                <div class="mt-6">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Choose Display Format:</h4>
                    <div class="flex space-x-4 mb-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" wire:model.live="displayFormat" value="reel_cuts" class="mr-2">
                            <span class="text-sm text-gray-700">Reel and Cuts</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" wire:model.live="displayFormat" value="dimensions" class="mr-2">
                            <span class="text-sm text-gray-700">Dimensions</span>
                        </label>
                    </div>

                    <!-- Format indicator -->
                    <div class="mb-4 p-2 bg-blue-50 border border-blue-200 rounded text-sm text-blue-700">
                        <strong>Current Format:</strong> {{ $displayFormat === 'reel_cuts' ? 'Reel and Cuts' : 'Dimensions' }}
                    </div>
                </div>

                <!-- Print Content (Hidden, shown only when printing) -->
                <div id="purchaseOrderPrintContent" class="hidden print:block print:p-6" style="display: none;">
                    <!-- Print Header -->
                    <div class="text-center mb-6 border-b-2 border-gray-800 pb-4">
                        <h2 class="text-3xl font-bold text-gray-900">PURCHASE ORDER</h2>
                        <p class="text-lg text-gray-700 mt-2">PO Number: {{ $selectedPurchaseOrder->po_number }}</p>
                        <p class="text-sm text-gray-600">Date: {{ $selectedPurchaseOrder->date->format('M d, Y') }}</p>
                    </div>

                    <!-- Print Details -->
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Supplier Information</h4>
                            <p class="text-sm text-gray-700"><strong>Supplier:</strong> {{ $selectedPurchaseOrder->supplier->name ?? 'N/A' }} ({{ $selectedPurchaseOrder->supplier->code ?? 'N/A' }})</p>
                            <p class="text-sm text-gray-700"><strong>Status:</strong> {{ ucfirst($selectedPurchaseOrder->status) }}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Order Information</h4>
                            <p class="text-sm text-gray-700"><strong>Job Order:</strong> {{ $selectedPurchaseOrder->jobOrder->supplier_po_number ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-700"><strong>Total Amount:</strong> Rs. {{ number_format($selectedPurchaseOrder->getTotalAmount(), 2) }}</p>
                        </div>
                    </div>

                    <!-- Print Items Table -->
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-3">Purchase Order Items</h4>
                        <table class="min-w-full border border-gray-300" style="border-collapse: collapse;">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Item Type</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Description</th>
                                    @if($displayFormat === 'reel_cuts')
                                    <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Reel Size</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Cut Size</th>
                                    @else
                                    <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Dimensions</th>
                                    @endif
                                    <th class="border border-gray-300 px-4 py-2 text-right text-xs font-medium text-gray-700 uppercase">Quantity</th>
                                    <th class="border border-gray-300 px-4 py-2 text-right text-xs font-medium text-gray-700 uppercase">Unit Price</th>
                                    <th class="border border-gray-300 px-4 py-2 text-right text-xs font-medium text-gray-700 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedPurchaseOrder->items as $item)
                                <tr>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">{{ ucfirst($item->item_type) }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">{{ $item->description }}</td>
                                    @if($displayFormat === 'reel_cuts')
                                    @php
                                        $reelSize = $item->reel_size;
                                        $cutSize = $item->cut_size;
                                        if (($reelSize === null || $cutSize === null || $reelSize == 0 || $cutSize == 0) && $item->item_type === 'box' && $selectedPurchaseOrder->jobOrder) {
                                            $box = $selectedPurchaseOrder->jobOrder->boxes->firstWhere('id', $item->item_id);
                                            if ($box) {
                                                $reelSize = $box['reel_size'] ?? 0;
                                                $cutSize = $box['cut_size'] ?? 0;
                                            }
                                        }
                                    @endphp
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">{{ $reelSize && $reelSize > 0 ? number_format($reelSize, 2) . '"' : '-' }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">{{ $cutSize && $cutSize > 0 ? number_format($cutSize, 2) . '"' : '-' }}</td>
                                    @else
                                    @php
                                        $dimensions = '-';
                                        if ($item->item_type === 'box' && $selectedPurchaseOrder->jobOrder) {
                                            $box = $selectedPurchaseOrder->jobOrder->boxes->firstWhere('id', $item->item_id);
                                            if ($box) {
                                                $dimensions = number_format($box['length'], 2) . ' x ' . number_format($box['width'], 2) . ' x ' . number_format($box['height'], 2) . ' ' . ($box['unit'] ?? 'CM');
                                            }
                                        }
                                    @endphp
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">{{ $dimensions }}</td>
                                    @endif
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900 text-right">{{ number_format($item->quantity) }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900 text-right">Rs. {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900 text-right">Rs. {{ number_format($item->total_price, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="{{ $displayFormat === 'reel_cuts' ? 6 : 5 }}" class="border border-gray-300 px-4 py-2 text-right font-semibold text-gray-900">Total Amount:</td>
                                    <td class="border border-gray-300 px-4 py-2 text-right font-semibold text-gray-900">Rs. {{ number_format($selectedPurchaseOrder->getTotalAmount(), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if($selectedPurchaseOrder->notes)
                    <div class="mt-6">
                        <h4 class="font-semibold text-gray-900 mb-2">Notes:</h4>
                        <p class="text-sm text-gray-700">{{ $selectedPurchaseOrder->notes }}</p>
                    </div>
                    @endif
                </div>

                <!-- Modal Body -->
                <div class="mt-6 space-y-6">
                    <!-- PO Header Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">PO Number</label>
                                <div class="mt-1 text-lg font-semibold text-gray-900">{{
                                    $selectedPurchaseOrder->po_number }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Date</label>
                                <div class="mt-1 text-gray-900">{{ $selectedPurchaseOrder->date->format('Y-m-d') }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Status</label>
                                <div class="mt-1">
                                    @php
                                    $statusColors = [
                                    'draft' => 'bg-gray-100 text-gray-800',
                                    'confirmed' => 'bg-green-100 text-green-800',
                                    'cancelled' => 'bg-red-100 text-red-800',
                                    ];
                                    @endphp
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$selectedPurchaseOrder->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($selectedPurchaseOrder->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Supplier</label>
                                <div class="mt-1 text-gray-900">{{ $selectedPurchaseOrder->supplier->name ?? 'N/A' }}
                                </div>
                                <div class="text-sm text-gray-500">{{ $selectedPurchaseOrder->supplier->code ?? '' }}
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Job Order</label>
                                <div class="mt-1 text-gray-900">{{ $selectedPurchaseOrder->jobOrder->supplier_po_number
                                    ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ $selectedPurchaseOrder->jobOrder->job_number ?? ''
                                    }}</div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Total Amount</label>
                                <div class="mt-1 text-lg font-semibold text-gray-900">Rs. {{
                                    number_format($selectedPurchaseOrder->getTotalAmount(), 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- PO Items Table -->
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Purchase Order Items</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Item Type</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Description</th>
                                        @if($displayFormat === 'reel_cuts')
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Reel Size</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cut Size</th>
                                        @else
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Length</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Width</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Height</th>
                                        @endif
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Quantity</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Unit Price</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total</th>
                                        @if($selectedPurchaseOrder && $selectedPurchaseOrder->status === 'draft')
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($selectedPurchaseOrder->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->item_type === 'box' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                                {{ ucfirst($item->item_type) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $item->description }}</td>
                                        @if($displayFormat === 'reel_cuts')
                                        @php
                                            // Get reel_size and cut_size from item
                                            $reelSize = $item->reel_size;
                                            $cutSize = $item->cut_size;

                                            // If values are not stored, try to calculate from the related box
                                            if (($reelSize === null || $cutSize === null || $reelSize == 0 || $cutSize == 0) && $item->item_type === 'box' && $selectedPurchaseOrder->jobOrder) {
                                                $box = $selectedPurchaseOrder->jobOrder->boxes->firstWhere('id', $item->item_id);
                                                if ($box) {
                                                    // Use the calculated values from the box
                                                    $reelSize = $box['reel_size'] ?? 0;
                                                    $cutSize = $box['cut_size'] ?? 0;
                                                }
                                            }
                                        @endphp
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $reelSize && $reelSize > 0 ? number_format($reelSize, 2) . '"' : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $cutSize && $cutSize > 0 ? number_format($cutSize, 2) . '"' : '-' }}
                                        </td>
                                        @else
                                        @php
                                            // Try to extract dimensions from description for boxes
                                            $length = '-';
                                            $width = '-';
                                            $height = '-';

                                            if ($item->item_type === 'box' && $selectedPurchaseOrder->jobOrder) {
                                                // Try to find the box in job order
                                                $box = $selectedPurchaseOrder->jobOrder->boxes->firstWhere('id', $item->item_id);
                                                if ($box) {
                                                    $length = number_format($box['length'], 2) . ' ' . ($box['unit'] ?? 'CM');
                                                    $width = number_format($box['width'], 2) . ' ' . ($box['unit'] ?? 'CM');
                                                    $height = number_format($box['height'], 2) . ' ' . ($box['unit'] ?? 'CM');
                                                }
                                            }
                                        @endphp
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $length }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $width }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $height }}</td>
                                        @endif
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($selectedPurchaseOrder->status === 'draft')
                                                <input type="number"
                                                       wire:model.live="itemQuantities.{{ $item->id }}"
                                                       wire:change="updateItemQuantity({{ $item->id }})"
                                                       min="1"
                                                       class="w-24 px-2 py-1 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                                            @else
                                                {{ $item->quantity }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs. {{
                                            number_format($item->unit_price, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs. {{
                                            number_format($item->total_price, 2) }}</td>
                                        @if($selectedPurchaseOrder->status === 'draft')
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button wire:click="deletePurchaseOrderItem({{ $item->id }})"
                                                    class="text-red-600 hover:text-red-900"
                                                    title="Delete Item"
                                                    onclick="return confirm('Are you sure you want to delete this item?')">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </td>
                                        @endif
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="{{ ($displayFormat === 'reel_cuts' ? 7 : 9) + ($selectedPurchaseOrder && $selectedPurchaseOrder->status === 'draft' ? 1 : 0) }}" class="px-6 py-12 text-center text-gray-500">
                                            No items found
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Notes -->
                    @if($selectedPurchaseOrder->notes)
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <div class="mt-1 p-3 bg-gray-50 rounded-md text-gray-900">{{ $selectedPurchaseOrder->notes }}
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t mt-6">
                    <button wire:click="closeViewModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition-colors">
                        Close
                    </button>
                    <button wire:click="printPurchaseOrder"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print
                    </button>
                    @if($selectedPurchaseOrder && $selectedPurchaseOrder->status === 'draft')
                    <button wire:click="savePurchaseOrder"
                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md transition-colors">
                        Save
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Phone Confirmation Modal -->
    @if($showPhoneConfirmModal && $selectedPurchaseOrder)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Confirm Purchase Order</h3>
                    <button wire:click="closePhoneConfirmModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="mt-6 space-y-6">
                    <!-- PO Header Information -->
                    <div class="bg-yellow-50 p-4 rounded-lg">
                        <h4 class="font-medium text-yellow-900 mb-2">Purchase Order: {{
                            $selectedPurchaseOrder->po_number }}</h4>
                        <p class="text-sm text-yellow-700">Update supplier prices and confirm this Purchase Order after
                            confirmation.</p>
                    </div>

                    <!-- Price Update Table -->
                    <div>
                        <h4 class="text-lg font-medium text-gray-900 mb-4">Update Supplier Prices</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Item Type</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Description</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Quantity</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Current Price</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            New Unit Price</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($selectedPurchaseOrder->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item->item_type === 'box' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                                {{ ucfirst($item->item_type) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $item->description }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->quantity
                                            }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs. {{
                                            number_format($item->unit_price, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="number" step="0.01"
                                                wire:model="phoneConfirmForm.item_{{ $item->id }}_unit_price"
                                                class="w-24 px-2 py-1 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            Rs. {{ number_format(($phoneConfirmForm["item_{$item->id}_unit_price"] ??
                                            $item->unit_price) * $item->quantity, 2) }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Confirmation Notes</label>
                        <textarea wire:model="phoneConfirmForm.notes" rows="3"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Any notes from the conversation..."></textarea>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t mt-6">
                    <button wire:click="closePhoneConfirmModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition-colors">
                        Cancel
                    </button>
                    <button wire:click="confirmPurchaseOrderOverPhoneFinal"
                        class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 rounded-md transition-colors">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif



    <!-- GRN Confirmation Modal -->
    @if($showGRNConfirmModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-orange-100 rounded-full mb-4">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>

                    <h3 class="text-lg font-medium text-gray-900 text-center mb-2">
                        Create GRN from Purchase Order
                    </h3>

                    <div class="text-center text-sm text-gray-600 mb-6">
                        <p>Are you sure you want to create a GRN from:</p>
                        <p class="font-semibold text-gray-900 mt-1">
                            {{ $selectedPurchaseOrder->po_number ?? '' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-2">
                            This will create a Goods Received Note with {{ $selectedPurchaseOrder->items->count() ?? 0 }} items.
                        </p>
                    </div>

                    <div class="flex space-x-3">
                        <button wire:click="closeGRNConfirmModal"
                                class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md transition-colors">
                            Cancel
                        </button>
                        <button wire:click="createGRNFromPurchaseOrder({{ $selectedPurchaseOrder->id ?? '' }})"
                                class="flex-1 bg-orange-600 hover:bg-orange-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                            Create GRN
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Cancel Purchase Order Confirmation Modal -->
    @if($showCancelConfirmModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>

                    <h3 class="text-lg font-medium text-gray-900 text-center mb-2">
                        Cancel Purchase Order
                    </h3>

                    <div class="text-center text-sm text-gray-600 mb-6">
                        <p>Are you sure you want to cancel:</p>
                        <p class="font-semibold text-gray-900 mt-1">
                            {{ $selectedPurchaseOrder->po_number ?? '' }}
                        </p>
                        <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                            <p class="text-xs text-yellow-800 font-medium">⚠️ Important:</p>
                            <p class="text-xs text-yellow-700 mt-1">
                                @if($selectedPurchaseOrder && $selectedPurchaseOrder->grn->isNotEmpty())
                                    This will cancel the GRN and reverse all received quantities from inventory.
                                @else
                                    This will cancel the purchase order and cannot be undone.
                                @endif
                            </p>
                        </div>

                        <div class="mt-4">
                            <label for="cancellation_reason" class="block text-sm font-medium text-gray-700 mb-2">
                                Cancellation Reason (Optional)
                            </label>
                            <textarea wire:model="cancellationReason"
                                    id="cancellation_reason"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    placeholder="Enter reason for cancellation..."></textarea>
                        </div>
                    </div>

                    <div class="flex space-x-3">
                        <button wire:click="closeCancelConfirmModal"
                                class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md transition-colors">
                            Keep Order
                        </button>
                        <button wire:click="cancelPurchaseOrder({{ $selectedPurchaseOrder->id ?? '' }})"
                                class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                            Cancel Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @script
    <script>
        $wire.on('redirectToProductionOrder', function(url) {
            window.location.href = url;
        });

        // Handle redirect after production order creation
        @if($redirectToProductionOrder)
            setTimeout(function() {
                window.location.href = '{{ $redirectToProductionOrder }}';
            }, 1000);
        @endif

        // Print Purchase Order functionality
        window.printPurchaseOrder = function() {
            var printContent = document.getElementById('purchaseOrderPrintContent');
            if (!printContent) {
                console.error('Print content not found');
                return;
            }

            // Show print content and hide modal
            printContent.style.display = 'block';

            // Create a new window for printing
            var printWindow = window.open('', '_blank', 'width=800,height=600');
            printWindow.document.write('<html><head><title>Purchase Order - {{ $selectedPurchaseOrder->po_number ?? "" }}</title>');
            printWindow.document.write('<style>');
            printWindow.document.write('body{font-family:Arial,sans-serif;margin:20px;padding:20px;}');
            printWindow.document.write('table{border-collapse:collapse;width:100%;margin-top:20px;}');
            printWindow.document.write('th,td{border:1px solid #000;padding:8px;text-align:left;}');
            printWindow.document.write('th{background-color:#f3f4f6;font-weight:bold;}');
            printWindow.document.write('.text-center{text-align:center;}');
            printWindow.document.write('.text-right{text-align:right;}');
            printWindow.document.write('@media print { @page { margin: 0.5cm; } body { margin: 0; } }');
            printWindow.document.write('</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(printContent.innerHTML);
            printWindow.document.write('</body></html>');
            printWindow.document.close();

            // Wait for content to load then print
            setTimeout(function() {
                printWindow.print();
                printWindow.close();
                printContent.style.display = 'none';
            }, 500);
        };
    </script>
    @endscript

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</div>
