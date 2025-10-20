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
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <button wire:click="viewPurchaseOrder({{ $po->id }})"
                                    class="text-blue-600 hover:text-blue-900" title="View Purchase Order">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg>
                                </button>

                                @if($po->status === 'draft')
                                <button wire:click="confirmPurchaseOrder({{ $po->id }})"
                                    wire:confirm="Confirm this Purchase Order? This will make it ready for production."
                                    class="text-green-600 hover:text-green-900" title="Confirm Purchase Order">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </button>
                                <button wire:click="openPhoneConfirmModal({{ $po->id }})"
                                    class="text-yellow-600 hover:text-yellow-900" title="Confirm (Update Prices)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                        </path>
                                    </svg>
                                </button>
                                @endif

                                @if($po->status === 'draft')
                                <button wire:click="cancelPurchaseOrder({{ $po->id }})"
                                    wire:confirm="Cancel this Purchase Order?" class="text-red-600 hover:text-red-900"
                                    title="Cancel Purchase Order">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                @endif

                                <button wire:click="downloadPurchaseOrder({{ $po->id }})"
                                    class="text-purple-600 hover:text-purple-900" title="Download PDF">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No purchase orders</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by generating a purchase order from a job
                                order.</p>
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
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Reel Size</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cut Size</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Quantity</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Unit Price</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total</th>
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $item->reel_size ? $item->reel_size . '"' : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $item->cut_size ? $item->cut_size . '"' : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->quantity
                                            }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs. {{
                                            number_format($item->unit_price, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs. {{
                                            number_format($item->total_price, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
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
                    <button wire:click="downloadPurchaseOrder({{ $selectedPurchaseOrder->id }})"
                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md transition-colors">
                        Download PDF
                    </button>
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
                        <label class="block text-sm font-medium text-gray-700">Phone Confirmation Notes</label>
                        <textarea wire:model="phoneConfirmForm.notes" rows="3"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Any notes from the phone conversation..."></textarea>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t mt-6">
                    <button wire:click="closePhoneConfirmModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition-colors">
                        Cancel
                    </button>
                    <button wire:click="testModal"
                        class="px-4 py-2 text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700 rounded-md transition-colors">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Phone Confirmation Confirmation Modal -->
    @if($showPhoneConfirmConfirmModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Confirm Purchase Order</h3>
                    <button wire:click="closePhoneConfirmConfirmModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="mt-6">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-yellow-100 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                            </path>
                        </svg>
                    </div>
                    <div class="mt-4 text-center">
                        <h4 class="text-lg font-medium text-gray-900">Confirm Purchase Order</h4>
                        <p class="mt-2 text-sm text-gray-600">
                            Confirm this Purchase Order? This will make it ready for production.
                        </p>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t mt-6">
                    <button wire:click="closePhoneConfirmConfirmModal"
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
    </script>
    @endscript
</div>