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

    @if (session()->has('info'))
    <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
        {{ session('info') }}
    </div>
    @endif

    <!-- Header with Back Button -->
    <div class="mb-6">
        <div class="flex items-center space-x-4 mb-4">
            <a wire:navigate href="{{ route('production-order-management') }}"
                class="inline-flex items-center text-gray-600 hover:text-gray-900">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Production Orders
            </a>
        </div>

        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Production Order Details</h1>
                <p class="text-gray-600 mt-1">Production Order Number: {{ $productionOrder->production_order_number }}</p>
                <div class="mt-2 flex items-center space-x-2">
                    @if(!$hasAnyGRN)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">GRN Pending</span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            {{ $grnCount }} GRN{{ $grnCount > 1 ? 's' : '' }} Created
                        </span>
                        @if($totalBalanceQuantity > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ number_format($totalBalanceQuantity, 0) }} Balance Qty
                            </span>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Right side: Action buttons -->
            <div class="flex space-x-3">
                @if($isEditMode)
                <button wire:click="toggleEditMode"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                    Cancel Edit
                </button>
                <button wire:click="saveProductionOrder"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Save Changes
                </button>
                @else
                <!-- Status action buttons -->
                @if($productionOrder->status === 'in_production')
                <button wire:click="completeProduction" @disabled(!$canComplete)
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 {{ !$canComplete ? 'opacity-50 cursor-not-allowed' : '' }}"
                    title="{{ !$canComplete ? 'Complete all production items to enable this action' : '' }}">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Complete Production
                </button>
                @endif
                
                <button wire:click="toggleEditMode"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                        </path>
                    </svg>
                    Edit Production Order
                </button>
                
                <button wire:click="showPrintPreview"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                        </path>
                    </svg>
                    Print Production Order
                </button>

                <button wire:click="generateFGGRNFromCompleted"
                        @if($hasAnyGRN) disabled @endif
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium @if($hasAnyGRN) text-gray-400 bg-gray-300 cursor-not-allowed @else text-white bg-purple-600 hover:bg-purple-700 @endif"
                        @if($hasAnyGRN) title="GRN already created for this production order" @endif>
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    + Generate FG GRN
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Production Order Information -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Column 1 -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Production Order Number</label>
                    <div class="w-full px-3 py-1 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                        {{ $productionOrder->production_order_number }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Production Date</label>
                    @if($isEditMode)
                    <input type="date" wire:model="form.date"
                        class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('form.date') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    @else
                    <div class="w-full px-3 py-1 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                        {{ $productionOrder->date->format('M d, Y') }}
                    </div>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                    <div class="w-full px-3 py-1 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                        {{ $productionOrder->supplier->name }} ({{ $productionOrder->supplier->code }})
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Job Order</label>
                    <div class="w-full px-3 py-1 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                        {{ $productionOrder->jobOrder->supplier_po_number ?? 'N/A' }}
                    </div>
                </div>
            </div>

            <!-- Column 2 -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    @if($isEditMode)
                    <select wire:model="form.status"
                        class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    @error('form.status') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    @else
                    <div class="w-full px-3 py-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $productionOrder->status === 'pending' ? 'bg-gray-100 text-gray-800' : '' }}
                                {{ $productionOrder->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $productionOrder->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $productionOrder->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ ucfirst($productionOrder->status) }}
                        </span>
                    </div>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Items Count</label>
                    <div class="w-full px-3 py-1 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                        {{ $productionOrder->items->count() }} items
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">GRN Count</label>
                    <div class="w-full px-3 py-1 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                        {{ $grnCount }} GRN{{ $grnCount !== 1 ? 's' : '' }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Balance Quantity</label>
                    <div class="w-full px-3 py-1 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                        {{ number_format($totalBalanceQuantity, 0) }} units
                    </div>
                </div>
            </div>

            <!-- Column 3 -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Created At</label>
                    <div class="w-full px-3 py-1 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                        {{ $productionOrder->created_at->format('M d, Y H:i') }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Updated At</label>
                    <div class="w-full px-3 py-1 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                        {{ $productionOrder->updated_at->format('M d, Y H:i') }}
                    </div>
                </div>
            </div>

            <!-- Column 4 -->
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    @if($isEditMode)
                    <textarea wire:model="form.notes" rows="3"
                        class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Additional notes"></textarea>
                    @error('form.notes') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    @else
                    <div class="w-full px-3 py-1 border border-gray-300 rounded-md bg-gray-50 text-gray-900 min-h-[60px]">
                        {{ $productionOrder->notes ?: 'No notes' }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Related Job Order -->
    @if($productionOrder->jobOrder)
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-medium text-gray-900">Related Job Order</h3>
            <a wire:navigate href="{{ route('job-order-detail', $productionOrder->jobOrder->id) }}"
               class="text-blue-600 hover:text-blue-800 text-sm">View Job Order</a>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div>
                <label class="text-xs text-gray-500">Job Order Number</label>
                <div class="font-medium text-gray-900">{{ $productionOrder->jobOrder->job_number ?? 'N/A' }}</div>
            </div>
            <div>
                <label class="text-xs text-gray-500">Customer</label>
                <div class="font-medium text-gray-900">{{ $productionOrder->jobOrder->customer->name ?? 'N/A' }}</div>
                @php
                    // Get dimensions from first production order item
                    $firstItem = $productionOrder->items->first();
                    $dimensions = null;
                    if ($firstItem) {
                        $itemDetails = $firstItem->getItem();
                        if ($itemDetails) {
                            if ($firstItem->item_type === 'box' && isset($itemDetails->length, $itemDetails->width, $itemDetails->height)) {
                                $unit = $itemDetails->unit ?? 'CM';
                                $dimensions = number_format($itemDetails->length, 2) . ' x ' . 
                                             number_format($itemDetails->width, 2) . ' x ' . 
                                             number_format($itemDetails->height, 2) . ' ' . $unit;
                            } elseif ($firstItem->item_type === 'divider' && isset($itemDetails->ply)) {
                                $dimensions = $itemDetails->ply . ' PLY';
                            }
                        }
                    }
                @endphp
                @if($dimensions)
                    <div class="text-xs text-gray-500 mt-1">{{ $dimensions }}</div>
                @endif
            </div>
            <div>
                <label class="text-xs text-gray-500">Supplier PO</label>
                <div class="font-medium text-gray-900">{{ $productionOrder->jobOrder->supplier_po_number ?? 'N/A' }}</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Items Table -->
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Production Items</h3>
            </div>
        </div>

        @if($productionOrder->items->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completed</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($productionOrder->items as $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ ucfirst($item->item_type) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $item->description ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @php
                                $materialQty = $item->getMaterialQuantity();
                                $noOfUps = $item->getNoOfUps();
                                $expectedFromMaterial = $item->getExpectedFinishedGoodsFromMaterial();
                                $effectiveMaxQty = $item->getEffectiveMaxQuantity();
                                $jobOrderOrderQty = $item->getJobOrderOrderQuantity();
                                // Check if quantity is wrong (matches job order quantity but should be from transaction)
                                $quantityMismatch = $item->quantity == $jobOrderOrderQty && $item->completed_quantity == 0;
                            @endphp
                            @if($isEditMode)
                                <div class="space-y-2">
                                    <input type="number" 
                                           wire:model="itemQuantities.{{ $item->id }}"
                                           min="1" 
                                           max="{{ $jobOrderOrderQty }}"
                                           class="w-32 px-2 py-1 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 text-sm">
                                    @if($quantityMismatch)
                                        <div class="text-xs text-red-600">
                                            Was: {{ number_format($item->quantity) }} (incorrect - matches job order)
                                        </div>
                                    @endif
                                    <div class="text-xs text-gray-400">
                                        ({{ number_format($materialQty) }} × {{ $noOfUps }} = {{ number_format($expectedFromMaterial) }})
                                    </div>
                                </div>
                            @else
                                {{ number_format($item->quantity) }}
                                @if($quantityMismatch)
                                    <div class="text-xs text-red-600 mt-1">
                                        <button wire:click="toggleEditMode" 
                                                class="underline hover:text-red-800">
                                            Fix Quantity (currently shows job order qty)
                                        </button>
                                    </div>
                                @endif
                                <div class="text-xs text-gray-400 mt-1">
                                    ({{ number_format($materialQty) }} × {{ $noOfUps }} = {{ number_format($expectedFromMaterial) }})
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($item->completed_quantity) }}
                            @php
                                // Calculate material completed from finished goods completed
                                $materialCompleted = $item->getNoOfUps() > 0 ? ($item->completed_quantity / $item->getNoOfUps()) : 0;
                            @endphp
                            <div class="text-xs text-gray-400 mt-1">
                                ({{ number_format($materialCompleted, 1) }} × {{ $item->getNoOfUps() }})
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @php 
                                $materialQty = $item->getMaterialQuantity();
                                $noOfUps = $item->getNoOfUps();
                                $expectedFromMaterial = $item->getExpectedFinishedGoodsFromMaterial();
                                $jobOrderOrderQty = $item->getJobOrderOrderQuantity();
                                $effectiveMaxQty = $item->getEffectiveMaxQuantity();
                                $maxCanComplete = $effectiveMaxQty - $item->completed_quantity;
                                $hasReachedLimit = $item->completed_quantity >= $effectiveMaxQty;
                            @endphp
                            @if(!$hasReachedLimit && $maxCanComplete > 0)
                                <div class="flex items-center space-x-2">
                                    <input type="number" min="1" max="{{ $maxCanComplete }}" step="1"
                                           wire:model.defer="completeQty.{{ $item->id }}"
                                           class="w-24 px-2 py-1 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Qty"
                                           title="Max: {{ $maxCanComplete }} (Effective Max: {{ $effectiveMaxQty }})">
                                    <button wire:click="completeItemQuantity({{ $item->id }})"
                                            class="inline-flex items-center px-3 py-1 border border-transparent rounded-md text-xs font-medium text-white bg-green-600 hover:bg-green-700">
                                        Complete
                                    </button>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    <div>Remaining: {{ number_format($maxCanComplete) }} / Max: {{ number_format($effectiveMaxQty) }}</div>
                                    <div class="text-gray-400">
                                        Material: {{ number_format($materialQty) }} × UPS: {{ $noOfUps }} = {{ number_format($expectedFromMaterial) }} | Production Order: {{ number_format($item->quantity) }} | Job Order: {{ number_format($jobOrderOrderQty) }}
                                    </div>
                                </div>
                            @else
                                <span class="text-xs text-green-700">Fully completed ({{ number_format($item->completed_quantity) }} / {{ number_format($effectiveMaxQty) }})</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $item->status === 'pending' ? 'bg-gray-100 text-gray-800' : '' }}
                                {{ $item->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $item->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}">
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-6 py-12 text-center text-gray-500">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No production items</h3>
            <p class="mt-1 text-sm text-gray-500">This production order has no items to display.</p>
        </div>
        @endif
    </div>

    <!-- GRN Details Section -->
    @if($hasAnyGRN)
    <div class="bg-white rounded-lg shadow-sm border mt-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">GRN Details</h3>
                <span class="text-sm text-gray-500">{{ $grnCount }} GRN{{ $grnCount > 1 ? 's' : '' }} • {{ number_format($totalBalanceQuantity, 0) }} Balance Qty</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GRN No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lot Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Qty</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Processed</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $grns = \App\Models\GRN::where('production_order_id', $productionOrder->id)->with('items')->get();
                    @endphp
                    @foreach($grns as $grn)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <a href="{{ route('grn-detail', $grn->id) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $grn->grn_no }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $grn->lot_code }}
                            @if($grn->isMultiItemGRN())
                                <span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    Multi
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $category = $grn->isFromProductionOrder() ? 'FG' : 'RAW';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $category === 'FG' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">
                                {{ $category }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $grn->received_date ? $grn->received_date->format('M d, Y') : 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($grn->getTotalQuantity(), 0) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($grn->getTotalProcessedQuantity(), 0) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($grn->getBalanceQuantity(), 0) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($grn->status === 'processed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Processed
                                </span>
                            @elseif($grn->status === 'cancelled')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Cancelled
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <a href="{{ route('grn-detail', $grn->id) }}" 
                               class="text-blue-600 hover:text-blue-800 font-medium">
                                View Details
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Print Preview Modal -->
    @if($showPrintPreviewModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Print Preview - Production Order</h3>
                    <button wire:click="closePrintPreviewModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Print Preview Content -->
                <div class="border border-gray-300 rounded-lg p-6 bg-white print-preview" id="printContent">
                    <!-- Production Order Header -->
                    <div class="text-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">PRODUCTION ORDER</h2>
                        <p class="text-lg text-gray-700">Production Order Number: {{ $productionOrder->production_order_number }}</p>
                        <p class="text-sm text-gray-600">Date: {{ $productionOrder->date ? $productionOrder->date->format('M d, Y') : 'N/A' }}</p>
                    </div>

                    <!-- Production Order Details -->
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Supplier Information</h4>
                            <p class="text-sm text-gray-700"><strong>Supplier:</strong> {{ $productionOrder->supplier->name ?? 'N/A' }} ({{ $productionOrder->supplier->code ?? 'N/A' }})</p>
                            <p class="text-sm text-gray-700"><strong>Job Order:</strong> {{ $productionOrder->jobOrder->supplier_po_number ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Production Information</h4>
                            <p class="text-sm text-gray-700"><strong>Status:</strong> {{ ucfirst($productionOrder->status) }}</p>
                            <p class="text-sm text-gray-700"><strong>Items:</strong> {{ $productionOrder->items->count() }} items</p>
                        </div>
                    </div>

                    <!-- Items Table -->
                    @if($productionOrder->items->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-300">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">No</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Item Type</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Description</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Quantity</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Completed</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productionOrder->items as $index => $item)
                                <tr>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">{{ $index + 1 }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">{{ ucfirst($item->item_type) }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">{{ $item->description ?? 'N/A' }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">{{ number_format($item->quantity) }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">{{ number_format($item->completed_quantity) }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">{{ ucfirst($item->status) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                    <!-- Notes -->
                    @if($productionOrder->notes)
                    <div class="mt-6">
                        <h4 class="font-semibold text-gray-900 mb-2">Notes:</h4>
                        <p class="text-sm text-gray-700">{{ $productionOrder->notes }}</p>
                    </div>
                    @endif

                    <!-- Footer -->
                    <div class="mt-8 text-sm text-gray-600">
                        <p>Prepared By: ________________</p>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t mt-6">
                    <button wire:click="closePrintPreviewModal" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition-colors">
                        Cancel
                    </button>
                    <button wire:click="printProductionOrder" 
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors">
                        Print
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- GRN Generation Modal -->
    @if($showGRNModal)
    <!-- Debug: Modal should be visible -->
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Generate GRN from Production Order</h3>
                    <div class="flex items-center space-x-4">
                        <button wire:click="toggleMultiItemMode" 
                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            {{ $multiItemMode ? 'Single Item' : 'Multi Item' }}
                        </button>
                        <button wire:click="closeGRNModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="mt-6 space-y-4">
                    @if(count($availableQuantities) > 0)
                        @if(!$multiItemMode)
                            <!-- Single Item Mode -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Select Production Item</label>
                                <select wire:model="selectedItemId" 
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select an item to generate GRN for</option>
                                    @foreach($availableQuantities as $available)
                                        <option value="{{ $available['item']->id }}">
                                            {{ ucfirst($available['item']->item_type) }} - 
                                            Remaining: {{ $available['remaining_quantity'] }} 
                                            @if($available['item_details'])
                                                ({{ $available['item_details']->length ?? 'N/A' }}x{{ $available['item_details']->width ?? 'N/A' }}x{{ $available['item_details']->height ?? 'N/A' }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('selectedItemId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity to Generate GRN For</label>
                                <input type="number" wire:model="grnQuantity" min="1"
                                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Enter quantity">
                                @error('grnQuantity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        @else
                            <!-- Multi Item Mode -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Select Multiple Items</label>
                                <div class="space-y-3 max-h-60 overflow-y-auto">
                                    @foreach($availableQuantities as $available)
                                        <div class="flex items-center space-x-3 p-3 border border-gray-200 rounded-md hover:bg-gray-50">
                                            <input type="checkbox" 
                                                   wire:click="toggleItemSelection({{ $available['item']->id }})"
                                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                            <div class="flex-1">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ ucfirst($available['item']->item_type) }}
                                                    @if($available['item_details'])
                                                        - {{ $available['item_details']->length ?? 'N/A' }}x{{ $available['item_details']->width ?? 'N/A' }}x{{ $available['item_details']->height ?? 'N/A' }}
                                                    @endif
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    Remaining: {{ $available['remaining_quantity'] }}
                                                </div>
                                            </div>
                                            @if(in_array($available['item']->id, $selectedItems))
                                                <div class="w-24">
                                                    <input type="number" 
                                                           wire:model="itemQuantities.{{ $available['item']->id }}"
                                                           wire:change="updateItemQuantity({{ $available['item']->id }}, $event.target.value)"
                                                           min="1" max="{{ $available['remaining_quantity'] }}"
                                                           class="w-full text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                                           placeholder="Qty">
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                @error('selectedItems') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Lot Code (Optional)</label>
                            <input type="text" wire:model="grnLotCode"
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Enter lot code or leave blank for auto-generation">
                            @error('grnLotCode') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">All items completed</h3>
                            <p class="mt-1 text-sm text-gray-500">All production items have been completed. No GRNs can be generated.</p>
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t mt-6">
                    <button wire:click="closeGRNModal" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition-colors">
                        Cancel
                    </button>
                    @if(count($availableQuantities) > 0)
                        @if(!$multiItemMode)
                            <button wire:click="generateGRN" 
                                    class="px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-md transition-colors">
                                Generate GRN
                            </button>
                        @else
                            <button wire:click="generateMultiItemGRN" 
                                    class="px-4 py-2 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-md transition-colors">
                                Generate Multi-Item GRN
                            </button>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Complete Item Confirmation Modal -->
    @if($showCompleteConfirmModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-blue-100 rounded-full mb-4">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>

                <h3 class="text-lg font-medium text-gray-900 text-center mb-2">
                    Complete Production Item
                </h3>

                <div class="text-center text-sm text-gray-600 mb-4">
                    <p>You are about to complete <strong>{{ number_format($pendingCompleteQty, 0) }} units</strong>.</p>
                </div>

                <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="bypassGRN" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2 text-sm text-gray-700">
                            <strong>Bypass GRN process</strong> - Add finished goods directly to stock and create GRN automatically
                        </span>
                    </label>
                    <p class="text-xs text-gray-500 mt-2 ml-6">
                        <strong>If checked:</strong> GRN will be created and processed automatically.<br>
                        <strong>If unchecked:</strong> GRN will be created as pending and you'll need to process it to stock manually.
                    </p>
                </div>

                <div class="flex space-x-3">
                    <button wire:click="closeCompleteConfirmModal"
                            class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md transition-colors">
                        Cancel
                    </button>
                    <button wire:click="confirmCompleteItemQuantity"
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                        Confirm Complete
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            .print-preview {
                box-shadow: none !important;
                border: none !important;
            }
        }
    </style>

    @script
    <script>
        // Print functionality
        window.printProductionOrder = function() {
            console.log('Print function called'); // Debug log
            
            var printContent = document.getElementById('printContent');
            if (!printContent) {
                console.error('Print content not found');
                return;
            }
            
            var originalContents = document.body.innerHTML;
            
            // Create a new window for printing
            var printWindow = window.open('', '_blank', 'width=800,height=600');
            printWindow.document.write('<html><head><title>Production Order Print</title>');
            printWindow.document.write('<style>body{font-family:Arial,sans-serif;margin:20px;}table{border-collapse:collapse;width:100%;}th,td{border:1px solid #000;padding:8px;text-align:left;}</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(printContent.innerHTML);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            
            // Wait for content to load then print
            setTimeout(function() {
                printWindow.print();
                printWindow.close();
            }, 500);
        };
    </script>
    @endscript
</div>
