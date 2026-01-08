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

    <!-- Header with Back Button -->
    <div class="mb-6">
        <div class="flex items-center space-x-4 mb-4">
            <a wire:navigate href="{{ route('job-order-management') }}"
                class="inline-flex items-center text-gray-600 hover:text-gray-900">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Back to Job Orders
            </a>
        </div>

        <div class="flex justify-between items-start">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Job Order Details</h1>
                <p class="text-gray-600 mt-1">Job Number: {{ $jobOrder->job_number }}</p>
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
                <button wire:click="saveJobOrder"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Save Changes
                </button>
                @else
                <!-- Status action buttons -->
                @if($jobOrder->status === 'pending' && (count($boxes) > 0 || count($dividers) > 0))
                <button wire:click="confirmJobOrder"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Confirm Job Order
                </button>
                @endif

                <!-- Generate Purchase Order button - only show when job order is confirmed and has items, and no purchase order exists yet -->
                @if($jobOrder->status === 'confirmed' && (count($boxes) > 0 || count($dividers) > 0) && !$hasPurchaseOrder)
                <button wire:click="generatePurchaseOrder"
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    Generate Purchase Order
                </button>
                @endif

                @if($jobOrder->status === 'confirmed')
                <button disabled
                    class="inline-flex items-center px-4 py-2 border border-gray-200 rounded-md shadow-sm text-sm font-medium text-gray-400 bg-gray-100 cursor-not-allowed"
                    title="Cannot edit confirmed job orders">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                        </path>
                    </svg>
                    Edit Job Order
                </button>
                @else
                <button wire:click="toggleEditMode"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                        </path>
                    </svg>
                    Edit Job Order
                </button>
                @endif

                @php
                    // Always check purchase orders directly from database for accurate state
                    // This ensures button state is correct regardless of component state
                    $hasAnyPO = false;
                    $currentJobOrderId = null;
                    
                    // Try multiple ways to get the job order ID
                    if (isset($jobOrderId) && $jobOrderId) {
                        $currentJobOrderId = $jobOrderId;
                    } elseif (isset($jobOrder) && $jobOrder && isset($jobOrder->id)) {
                        $currentJobOrderId = $jobOrder->id;
                    } elseif (request()->route('id')) {
                        $currentJobOrderId = request()->route('id');
                    }
                    
                    if ($currentJobOrderId) {
                        // Purchase orders are linked to job orders through purchase_order_items table
                        $hasAnyPO = \App\Models\PurchaseOrder::where('status', '!=', 'cancelled')
                            ->whereHas('items', function($query) use ($currentJobOrderId) {
                                $query->where('job_order_id', $currentJobOrderId);
                            })
                            ->exists();
                    }
                @endphp
                @if($hasAnyPO || $hasPurchaseOrder || $poCount > 0)
                <button wire:click="showPrintPreview"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                        </path>
                    </svg>
                    Print Job Order
                </button>
                @else
                <button disabled
                    class="inline-flex items-center px-4 py-2 border border-gray-200 rounded-md shadow-sm text-sm font-medium text-gray-400 bg-gray-100 cursor-not-allowed"
                    title="Print is available after creating a purchase order">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                        </path>
                    </svg>
                    Print Job Order
                </button>
                @endif
                @endif
            </div>
        </div>
    </div>

    <!-- Job Order Information -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Column 1 -->
            <div>

                <div class="space-y-3">
                    <!-- Job Number -->
                    <div class="flex items-center gap-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">Job Number</label>
                        <div class="flex-1">
                            <div class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-900">
                                {{ $jobOrder->job_number }}
                            </div>
                        </div>
                    </div>

                    <!-- Date -->
                    <div class="flex items-center gap-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">Date <span class="text-red-500">*</span></label>
                        <div class="flex-1">
                            @if($isEditMode)
                            <input type="date" wire:model="form.date"
                                class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('form.date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            @else
                            <div class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-900">
                                {{ $jobOrder->date->format('M d, Y') }}
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Supplier -->
                    <div class="flex items-center gap-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">Supplier <span class="text-red-500">*</span></label>
                        <div class="flex-1">
                            @if($isEditMode)
                            <select wire:model.live="form.supplier_id"
                                class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }} ({{ $supplier->code }})</option>
                                @endforeach
                            </select>
                            @error('form.supplier_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            @else
                            <div class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-900">
                                {{ $jobOrder->supplier->name ?? 'N/A' }} ({{ $jobOrder->supplier->code ?? 'N/A' }})
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Supplier PO Number -->
                    <div class="flex items-center gap-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">Supplier <br> PO Number</label>
                        <div class="flex-1">
                            <div class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-900">
                                {{ $jobOrder->supplier_po_number }}
                            </div>
                        </div>
                    </div>

                    <!-- Purchase Order Number -->
                    <div class="flex items-center gap-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">Purchase <br> OrderNo</label>
                        <div class="flex-1">
                            @if($isEditMode)
                            <input type="text" wire:model="form.purchase_order_no"
                                class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Enter PO Number">
                            @error('form.purchase_order_no') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            @else
                            <div class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-900">
                                {{ $jobOrder->purchase_order_no ?: 'N/A' }}
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- PO Date -->
                    <div class="flex items-center gap-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">PO Date</label>
                        <div class="flex-1">
                            @if($isEditMode)
                            <input type="date" wire:model="form.po_date"
                                class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('form.po_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            @else
                            <div class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-900">
                                {{ $jobOrder->po_date ? $jobOrder->po_date->format('M d, Y') : 'N/A' }}
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column 2 -->
            <div>

                <div class="space-y-3">
                    <!-- Customer -->
                    <div class="flex items-center gap-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">Customer <span class="text-red-500">*</span></label>
                        <div class="flex-1">
                            @if($isEditMode)
                            <select wire:model.live="form.customer_id"
                                class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->code }})</option>
                                @endforeach
                            </select>
                            @error('form.customer_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            @else
                            <div class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-900">
                                {{ $jobOrder->customer->name ?? 'N/A' }} ({{ $jobOrder->customer->code ?? 'N/A' }})
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Customer Address -->
                    <div class="flex items-center gap-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">Customer Address</label>
                        <div class="flex-1">
                            @if($isEditMode)
                            <textarea wire:model="form.customer_address" rows="3"
                                class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 min-h-[90px]"
                                placeholder="Enter customer address..."></textarea>
                            @error('form.customer_address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            @else
                            <div class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-900 min-h-[90px]">
                                {{ $jobOrder->customer_address }}
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="flex items-center gap-3 " style="margin-top: 15px !important;">
                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">Status</label>
                        <div class="flex-1">
                            <div class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-900">
                                {{ ucfirst(str_replace('_', ' ', $jobOrder->status)) }}
                            </div>
                        </div>
                    </div>

                    <!-- Items Count -->
                    <div class="flex items-center gap-3" style="margin-top: 18px !important;">
                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">Items Count</label>
                        <div class="flex-1">
                            <div class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-900">
                                {{ count($boxes) + count($dividers) }} items
                            </div>
                        </div>
                    </div>

                    <!-- Boards -->
                    <div class="flex items-center gap-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">Boards</label>
                        <div class="flex-1">
                            <div class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-900">
                                {{ count($boxes) }} boards
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column 3 -->
            <div>

                <div class="space-y-3">
                    <!-- Dividers -->
                    <div class="flex items-center gap-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">Dividers</label>
                        <div class="flex-1">
                            <div class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-900">
                                {{ count($dividers) }} dividers
                            </div>
                        </div>
                    </div>

                    <!-- Created At -->
                    <div class="flex items-center gap-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">Created At</label>
                        <div class="flex-1">
                            <div class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-900">
                                {{ $jobOrder->created_at->format('M d, Y H:i') }}
                            </div>
                        </div>
                    </div>

                    <!-- Purchase Orders -->
                    <div class="flex items-center gap-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">Purchase Orders</label>
                        <div class="flex-1">
                            <div class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-900">
                                <div class="flex items-center justify-between text-sm">
                                    <span>{{ $poStatusText }}</span>
                                    <span>{{ number_format($poProcessedCount) }} / {{ number_format($poCount) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- GRNs -->
                    <div class="flex items-center gap-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">GRNs</label>
                        <div class="flex-1">
                            <div class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-900">
                                <div class="flex items-center justify-between text-sm">
                                    <span>{{ number_format($grnProcessedCount) }} / {{ number_format($grnCount) }} processed</span>
                                    <span></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dispatched Quantity -->
                    <div class="flex items-center gap-3" style="margin-top: 18px !important;">
                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">Dispatched <br> Quantity</label>
                        <div class="flex-1">
                            <div class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-900">
                                <div class="flex items-center justify-between text-sm">
                                    <span>{{ number_format($dispatchedQuantity, 0) }} units</span>
                                    <span class="text-xs text-gray-500">{{ $deliveryCount }} DN(s)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Updated At -->
                    <div class="flex items-center gap-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">Updated At</label>
                        <div class="flex-1">
                            <div class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-900">
                                {{ $jobOrder->updated_at->format('M d, Y H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Section: Production Status and Notes -->
        <div class="flex gap-4 mt-6">
            <!-- Production Status -->
            <div class="flex-1">
                <div class="items-center gap-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">Production Status</label>
                    <div class="flex-1">
                        <div class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-900">
                            <div class="flex items-center justify-between text-sm mb-2">
                                <span>{{ $productionStatusText }}</span>
                                <span>{{ number_format($productionPercent, 1) }}%</span>
                            </div>
                            <div class="mt-2 h-4 bg-gray-200 rounded-full overflow-hidden relative">
                                @php
                                    $completedPercent = $productionTotal > 0 ? round(($productionFullyCompleted / $productionTotal) * 100, 1) : 0;
                                    $inProgressPercent = $productionTotal > 0 ? round(($productionInProgressQty / $productionTotal) * 100, 1) : 0;
                                @endphp
                                {{-- Fully Completed (Green) --}}
                                @if($completedPercent > 0)
                                <div class="h-full bg-green-500 absolute left-0 top-0 transition-all duration-300"
                                     style="width: {{ $completedPercent }}%; z-index: 2;"
                                     title="Completed: {{ number_format($productionFullyCompleted) }}">
                                </div>
                                @endif
                                {{-- In Progress (Yellow/Orange) --}}
                                @if($inProgressPercent > 0)
                                <div class="h-full bg-yellow-500 absolute top-0 transition-all duration-300"
                                     style="left: {{ $completedPercent }}%; width: {{ $inProgressPercent }}%; z-index: 1;"
                                     title="In Progress: {{ number_format($productionInProgressQty) }}">
                                </div>
                                @endif
                            </div>
                            <div class="mt-2 space-y-1">
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-3 h-3 bg-green-500 rounded"></div>
                                        <span class="text-gray-600">Completed:</span>
                                        <span class="font-medium text-gray-900">{{ number_format($productionFullyCompleted) }}</span>
                                    </div>
                                    @if($productionInProgressQty > 0)
                                    <div class="flex items-center space-x-2">
                                        <div class="w-3 h-3 bg-yellow-500 rounded"></div>
                                        <span class="text-gray-600">In Progress:</span>
                                        <span class="font-medium text-gray-900">{{ number_format($productionInProgressQty) }}</span>
                                    </div>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 text-right">
                                    Total: {{ number_format($productionTotal) }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="flex-1">
                <div class=" items-center gap-3">
                    <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">Notes</label>
                    <div class="flex-1">
                        @if($isEditMode)
                        <textarea wire:model="form.notes" rows="3"
                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 min-h-[100px]"
                            placeholder="Enter any additional notes or remarks..."></textarea>
                        @error('form.notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        @else
                        <div class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-900 min-h-[100px]">
                            {{ $jobOrder->notes ?: 'No notes' }}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Items Table -->
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Job Order Items</h3>
                @if(count($boxes) > 0 || count($dividers) > 0)
                @if($jobOrder->status === 'confirmed')
                <button disabled
                        class="bg-gray-400 text-white px-4 py-2 rounded-md text-sm font-medium cursor-not-allowed flex items-center"
                        title="Cannot add items to confirmed job orders">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Box/Divider
                    </button>
                @else
                <button wire:click="openBoxDividerModal"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Box/Divider
                    </button>
                @endif
                @endif
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session()->has('success'))
        <div class="px-6 py-3 bg-green-50 border-b border-green-200">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        @if(session()->has('error'))
        <div class="px-6 py-3 bg-red-50 border-b border-red-200">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        </div>
        @endif

        @if(count($boxes) > 0 || count($dividers) > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dimensions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PLY</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reel Size</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cut Size</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Board Qty</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($boxes as $index => $box)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Box
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($box['length'], 3) }}x{{ number_format($box['width'], 3) }}x{{
                            number_format($box['height'], 3) }} {{ $box['unit'] }}
                            <div class="text-xs text-gray-500">{{ $box['dimension_type'] }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{
                            number_format($box['order_qty'] ?? 0, 0) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $box['ply'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($box['reel_size'] ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($box['cut_size'] ?? 0, 2) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($box['board_qty'] ?? 0, 0) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="flex items-center space-x-2">
                                <button wire:click="viewBox({{ $index }})"
                                        class="text-green-600 hover:text-green-900"
                                        title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                                @if($jobOrder->status === 'draft' || $jobOrder->status === 'pending')
                                    <button wire:click="editBox({{ $index }})"
                                            class="text-blue-600 hover:text-blue-900"
                                            title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button wire:click="removeBox({{ $index }})"
                                            class="text-red-600 hover:text-red-900"
                                            title="Remove Box">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                @else
                                    <span class="text-gray-400 cursor-not-allowed" title="Cannot edit items from confirmed job orders">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach

                    @foreach($dividers as $index => $divider)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Divider
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="text-xs text-gray-500">Divider</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{
                            number_format($divider['quantity']) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $divider['ply'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <div class="flex items-center gap-2">
                                <button wire:click="viewDivider({{ $index }})" class="text-green-600 hover:text-green-900" title="View Divider">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                                @if($jobOrder->status === 'draft' || $jobOrder->status === 'pending')
                                    <button wire:click="editDivider({{ $index }})" class="text-blue-600 hover:text-blue-900" title="Edit Divider">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button wire:click="removeDivider({{ $index }})" class="text-red-600 hover:text-red-900" title="Remove Divider">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </button>
                                @else
                                    <span class="text-gray-400 cursor-not-allowed" title="Cannot delete items from confirmed job orders">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                            </path>
                                        </svg>
                                    </span>
                                @endif
                            </div>
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
            <h3 class="mt-2 text-sm font-medium text-gray-900">No job order items</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by adding boards or dividers to this job order.</p>

            <div class="mt-6">
                @if($jobOrder->status === 'confirmed')
                <button disabled
                        class="bg-gray-400 text-white px-4 py-2 rounded-md text-sm font-medium cursor-not-allowed flex items-center mx-auto"
                        title="Cannot add items to confirmed job orders">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Box/Divider
                    </button>
                @else
                <button wire:click="openBoxDividerModal"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center mx-auto">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Add Box/Divider
                    </button>
                @endif
            </div>
        </div>
        @endif
    </div>

    <!-- Purchase Order Details Section -->
    @php
        // Direct database query to ensure purchase orders are always displayed
        // This ensures data is accurate even if component properties aren't refreshed
        // Note: Purchase orders are linked to job orders through purchase_order_items table
        $directJobOrderId = isset($jobOrderId) && $jobOrderId ? $jobOrderId : (isset($jobOrder) && $jobOrder ? $jobOrder->id : request()->route('id'));
        $directPurchaseOrders = $directJobOrderId ? \App\Models\PurchaseOrder::where('status', '!=', 'cancelled')
            ->whereHas('items', function($query) use ($directJobOrderId) {
                $query->where('job_order_id', $directJobOrderId);
            })
            ->with(['items' => function($query) use ($directJobOrderId) {
                $query->where('job_order_id', $directJobOrderId);
            }, 'supplier'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($po) {
                return [
                    'id' => $po->id,
                    'po_number' => $po->po_number,
                    'date' => $po->date ? $po->date->format('Y-m-d') : null,
                    'date_formatted' => $po->date ? $po->date->format('M d, Y') : 'N/A',
                    'status' => $po->status,
                    'notes' => $po->notes,
                    'supplier' => $po->supplier ? [
                        'name' => $po->supplier->name,
                        'code' => $po->supplier->code,
                        'address' => $po->supplier->address,
                    ] : null,
                    'items' => $po->items->map(function($item) {
                        return [
                            'id' => $item->id,
                            'item_type' => $item->item_type,
                            'description' => $item->description,
                            'reel_size' => $item->reel_size,
                            'cut_size' => $item->cut_size,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'total_price' => $item->total_price,
                        ];
                    })->toArray(),
                    'total_amount' => $po->getTotalAmount(),
                ];
            })->toArray() : [];
        // Use direct query result if component property is empty, otherwise use component property
        $displayPurchaseOrders = !empty($purchaseOrders) ? $purchaseOrders : $directPurchaseOrders;
    @endphp
    <div class="bg-white rounded-lg shadow-sm border mt-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Purchase Order Details</h3>
                @if(count($displayPurchaseOrders) > 0)
                <span class="text-sm text-gray-500">{{ count($displayPurchaseOrders) }} Purchase Order{{ count($displayPurchaseOrders) > 1 ? 's' : '' }}</span>
                @endif
            </div>
        </div>

        <div class="p-6">
            @if(count($displayPurchaseOrders) > 0)
            @foreach($displayPurchaseOrders as $po)
            <div class="mb-8 last:mb-0 {{ !$loop->last ? 'border-b border-gray-200 pb-8' : '' }}">
                <!-- Purchase Order Header -->
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <h4 class="text-md font-semibold text-gray-900">{{ $po['po_number'] }}</h4>
                            <p class="text-sm text-gray-600">
                                Date: {{ $po['date_formatted'] }}
                                @if($po['supplier'])
                                    | Supplier: {{ $po['supplier']['name'] }} ({{ $po['supplier']['code'] }})
                                @endif
                            </p>
                        </div>
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $po['status'] === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                                {{ $po['status'] === 'sent' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $po['status'] === 'confirmed' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $po['status'] === 'received' ? 'bg-purple-100 text-purple-800' : '' }}">
                                {{ ucfirst($po['status']) }}
                            </span>
                        </div>
                    </div>
                    @if($po['notes'])
                    <p class="text-sm text-gray-600 mt-2">
                        <strong>Notes:</strong> {{ $po['notes'] }}
                    </p>
                    @endif
                </div>

                <!-- Purchase Order Items Table -->
                @if(count($po['items']) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Type</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($po['items'] as $item)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $item['item_type'] === 'box' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                        {{ ucfirst($item['item_type']) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $item['description'] }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right">{{ number_format($item['quantity']) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 text-right">Rs. {{ number_format($item['unit_price'], 2) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 text-right">Rs. {{ number_format($item['total_price'], 2) }}</td>
                            </tr>
                            @endforeach
                            <!-- Total Row -->
                            <tr class="bg-gray-50">
                                <td colspan="4" class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">Total Amount:</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-semibold text-gray-900 text-right">Rs. {{ number_format($po['total_amount'], 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                @else
                <p class="text-sm text-gray-500">No items in this purchase order.</p>
                @endif
            </div>
            @endforeach
            @else
            <div class="text-center py-8">
                <p class="text-gray-500 text-sm">No purchase orders found for this job order.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- FG GRN Details Section -->
    @if(count($fgGrns) > 0)
    <div class="bg-white rounded-lg shadow-sm border mt-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Finished Goods GRNs</h3>
                <span class="text-sm text-gray-500">{{ count($fgGrns) }} GRN{{ count($fgGrns) > 1 ? 's' : '' }}</span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GRN No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lot Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Production Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Qty</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Processed</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waste/Conserve</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($fgGrns as $grn)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            <a href="{{ route('grn-detail', $grn['id']) }}" class="text-blue-600 hover:text-blue-900">
                                {{ $grn['grn_no'] }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $grn['lot_code'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $grn['production_order_number'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $grn['received_date_formatted'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($grn['total_quantity'], 0) }} PCS
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($grn['total_processed'], 0) }} PCS
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @php
                                $wasteQty = $grn['waste_quantity'] ?? 0;
                            @endphp
                            @if($wasteQty > 0)
                                <span class="text-red-600 font-medium">Waste: {{ number_format($wasteQty, 0) }} boards</span>
                            @elseif($wasteQty < 0)
                                <span class="text-green-600 font-medium">Conserve: {{ number_format(abs($wasteQty), 0) }} boards</span>
                            @else
                                <span class="text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($grn['status'] === 'processed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Processed
                                </span>
                            @elseif($grn['status'] === 'pending')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    Pending
                                </span>
                            @elseif($grn['status'] === 'partially_processed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Partially Processed
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ ucfirst($grn['status']) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('grn-detail', $grn['id']) }}" class="text-blue-600 hover:text-blue-900">
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

    <!-- Add Box/Divider Modal -->
    @if($showBoxDividerModal)
    <div class="fixed inset-0 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-2xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <!-- Modal Header -->
                <div class="flex justify-between items-center pb-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">
                        Add Box/Divider
                    </h3>
                    <button wire:click="closeBoxDividerModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Tab Navigation -->
                <div class="border-b border-gray-200 mt-4">
                    <nav class="-mb-px flex space-x-8">
                        <button wire:click="setActiveTab('boxes')"
                            class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'boxes' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Boards ({{ count($boxes) }})
                        </button>
                        <button wire:click="setActiveTab('dividers')"
                            class="py-2 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'dividers' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Dividers ({{ count($dividers) }})
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="mt-6">
                    @if($activeTab === 'boxes')
                    @include('livewire.job-orders.job-order-management.boxes-tab')
                    @elseif($activeTab === 'dividers')
                    @include('livewire.job-orders.job-order-management.dividers-tab')
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- View Box Modal -->
    @if($showViewBoxModal)
    <div class="fixed inset-0 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <!-- Modal Header -->
                <div class="flex justify-between items-center pb-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">View Box Details</h3>
                    <button wire:click="closeViewBoxModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Box Details -->
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Order Quantity</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $viewingBoxData['order_qty'] ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Selling Price</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $viewingBoxData['selling_price'] ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Activity</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $viewingBoxData['activity'] ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Printing Instruction</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $viewingBoxData['printing_instruction'] ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">No. of Colours</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $viewingBoxData['no_of_colours'] ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Stitched/Glued</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $viewingBoxData['stitched_glued'] ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Sample Available</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $viewingBoxData['sample_available'] ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Sample Attached</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $viewingBoxData['sample_attached'] ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Dimensions</label>
                            <p class="mt-1 text-sm text-gray-900">
                                L: {{ $viewingBoxData['length'] ?? '-' }} × 
                                W: {{ $viewingBoxData['width'] ?? '-' }} × 
                                H: {{ $viewingBoxData['height'] ?? '-' }} {{ $viewingBoxData['unit'] ?? '' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Dimension Type</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $viewingBoxData['dimension_type'] ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Top Liner</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $viewingBoxData['top_liner'] ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">PLY</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $viewingBoxData['ply'] ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Combinations</label>
                            <div class="mt-1 text-sm text-gray-900 space-y-1">
                                @if(!empty($viewingBoxData['combination_1']) && $viewingBoxData['combination_1'] !== '-') <p>Combination 1: {{ $viewingBoxData['combination_1'] }}</p> @endif
                                @if(!empty($viewingBoxData['combination_2']) && $viewingBoxData['combination_2'] !== '-') <p>Combination 2: {{ $viewingBoxData['combination_2'] }}</p> @endif
                                @if(!empty($viewingBoxData['combination_3']) && $viewingBoxData['combination_3'] !== '-') <p>Combination 3: {{ $viewingBoxData['combination_3'] }}</p> @endif
                                @if(!empty($viewingBoxData['combination_4']) && $viewingBoxData['combination_4'] !== '-') <p>Combination 4: {{ $viewingBoxData['combination_4'] }}</p> @endif
                                @if(!empty($viewingBoxData['combination_5']) && $viewingBoxData['combination_5'] !== '-') <p>Combination 5: {{ $viewingBoxData['combination_5'] }}</p> @endif
                                @if(!empty($viewingBoxData['combination_6']) && $viewingBoxData['combination_6'] !== '-') <p>Combination 6: {{ $viewingBoxData['combination_6'] }}</p> @endif
                                @if(!empty($viewingBoxData['combination_7']) && $viewingBoxData['combination_7'] !== '-') <p>Combination 7: {{ $viewingBoxData['combination_7'] }}</p> @endif
                                @php
                                    $hasCombinations = false;
                                    for($i = 1; $i <= 7; $i++) {
                                        if(!empty($viewingBoxData['combination_' . $i] ?? '') && ($viewingBoxData['combination_' . $i] ?? '') !== '-') {
                                            $hasCombinations = true;
                                            break;
                                        }
                                    }
                                @endphp
                                @if(!$hasCombinations) 
                                    <p class="text-gray-500">-</p>
                                @endif
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Flute</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $viewingBoxData['flute'] ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">FSC Claim</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $viewingBoxData['fsc_claim'] ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">No. of UPS</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $viewingBoxData['no_of_ups'] ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Supplier Price</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $viewingBoxData['supplier_price'] ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Reel Size</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $viewingBoxData['reel_size'] ? number_format((float)$viewingBoxData['reel_size'], 2) : '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Cut Size</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $viewingBoxData['cut_size'] ? number_format((float)$viewingBoxData['cut_size'], 2) : '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Board Qty</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $viewingBoxData['board_qty'] ? number_format((int)$viewingBoxData['board_qty'], 0) : '-' }}</p>
                        </div>
                        @if(!empty($viewingBoxData['notes']) && $viewingBoxData['notes'] !== '-')
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notes</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $viewingBoxData['notes'] }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="mt-6 flex justify-end">
                    <button wire:click="closeViewBoxModal" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- View Divider Modal -->
    @if($showViewDividerModal)
    <div class="fixed inset-0 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <!-- Modal Header -->
                <div class="flex justify-between items-center pb-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">View Divider Details</h3>
                    <button wire:click="closeViewDividerModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Divider Details -->
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Left Column -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Quantity</label>
                            <p class="mt-1 text-sm text-gray-900 font-medium">{{ number_format($viewingDividerData['quantity'] ?? 0) }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Unit</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $viewingDividerData['unit'] ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">PLY</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $viewingDividerData['ply'] ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">FSC Claim</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $viewingDividerData['fsc_claim'] ?? '-' }}</p>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Supplier Price</label>
                            <p class="mt-1 text-sm text-gray-900">
                                @if(isset($viewingDividerData['supplier_price']) && $viewingDividerData['supplier_price'] !== null && $viewingDividerData['supplier_price'] !== '')
                                    {{ number_format($viewingDividerData['supplier_price'], 2) }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Combinations</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ implode(' / ', array_filter([
                                    $viewingDividerData['combination_1'] ?? null,
                                    $viewingDividerData['combination_2'] ?? null,
                                    $viewingDividerData['combination_3'] ?? null,
                                    $viewingDividerData['combination_4'] ?? null,
                                    $viewingDividerData['combination_5'] ?? null,
                                    $viewingDividerData['combination_6'] ?? null,
                                    $viewingDividerData['combination_7'] ?? null,
                                ])) ?: '-' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="mt-6 flex justify-end">
                    <button wire:click="closeViewDividerModal" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Print Preview Modal -->
    @if($showPrintPreviewModal)
    <div class="fixed inset-0 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <!-- Modal Header -->
                <div class="flex items-center justify-between pb-4 border-b">
                    <h3 class="text-lg font-medium text-gray-900">Print Preview - Job Order</h3>
                    <button wire:click="closePrintPreviewModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Display Options -->
                <div class="mt-6">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Choose Display Format:</h4>
                    <div class="flex space-x-4 mb-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" wire:model.live="printDisplayFormat" value="reel_cuts" class="mr-2">
                            <span class="text-sm text-gray-700">Reel and Cuts</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" wire:model.live="printDisplayFormat" value="dimensions" class="mr-2">
                            <span class="text-sm text-gray-700">Dimensions</span>
                        </label>
                    </div>

                    <!-- Debug indicator -->
                    <div class="mb-4 p-2 bg-blue-50 border border-blue-200 rounded text-sm text-blue-700">
                        <strong>Current Format:</strong> {{ $printDisplayFormat === 'reel_cuts' ? 'Reel and Cuts' : 'Dimensions' }}
                    </div>
                </div>

                <!-- Print Preview Content -->
                <div class="border border-gray-300 rounded-lg p-6 bg-white print-preview" id="printContent">
                    <!-- Job Order Header -->
                    <div class="text-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">JOB ORDER</h2>
                        <p class="text-lg text-gray-700">Job Number: {{ $jobOrder->job_number }}</p>
                        <p class="text-sm text-gray-600">Date: {{ $jobOrder->date ? $jobOrder->date->format('M d, Y') : 'N/A' }}</p>
                    </div>

                    <!-- Job Order Details -->
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Supplier Information</h4>
                            <p class="text-sm text-gray-700"><strong>Supplier:</strong> {{ $jobOrder->supplier->name ?? 'N/A' }} ({{ $jobOrder->supplier->code ?? 'N/A' }})</p>
                            <p class="text-sm text-gray-700"><strong>Supplier PO:</strong> {{ $jobOrder->supplier_po_number ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-700"><strong>Purchase Order:</strong> {{ $jobOrder->po_number ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-700"><strong>PO Date:</strong> {{ $jobOrder->po_date ? $jobOrder->po_date->format('M d, Y') : 'N/A' }}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Customer Information</h4>
                            <p class="text-sm text-gray-700"><strong>Customer:</strong> {{ $jobOrder->customer->name ?? 'N/A' }} ({{ $jobOrder->customer->code ?? 'N/A' }})</p>
                            <p class="text-sm text-gray-700"><strong>Address:</strong> {{ $jobOrder->customer_address ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Material Specification -->
                    <div class="mb-4">
                        <p class="text-sm text-gray-700"><strong>Material:</strong> {{ $jobOrder->material_specification ?? '5PLY(135KL/112M/140TL/112M/140TL) B/C Flute' }}</p>
                    </div>

                    <!-- Items Table -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full border border-gray-300">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">No</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Description</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Delivery</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">QTY</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Unit Price Rs.</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Total</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Previous Po No</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($boxes as $index => $box)
                                <tr>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">{{ $index + 1 }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">
                                        @if($printDisplayFormat === 'reel_cuts')
                                            Reel Size - {{ number_format($box['reel_size'] ?? 0, 2) }}" Cut Size - {{ number_format($box['cut_size'] ?? 0, 2) }}"
                                        @else
                                            {{ $box['length'] }}x{{ $box['width'] }}x{{ $box['height'] }}{{ $box['unit'] }} {{ $box['dimension_type'] }}
                                        @endif
                                        <!-- Debug: Format is {{ $printDisplayFormat }} -->
                                    </td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900"></td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">{{ number_format($box['board_qty'] ?? $box['order_qty']) }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">{{ number_format($box['supplier_price'] ?? 0, 2) }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">{{ number_format(($box['supplier_price'] ?? 0) * ($box['board_qty'] ?? $box['order_qty']), 2) }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900"></td>
                                </tr>
                                @endforeach
                                @foreach($dividers as $index => $divider)
                                <tr>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">{{ count($boxes) + $index + 1 }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">
                                        @if($printDisplayFormat === 'reel_cuts')
                                            Divider - {{ $divider['ply'] }} PLY
                                        @else
                                            Divider - {{ $divider['ply'] }} PLY
                                        @endif
                                    </td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900"></td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">{{ number_format($divider['quantity']) }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">{{ number_format($divider['supplier_price'] ?? 0, 2) }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">{{ number_format(($divider['supplier_price'] ?? 0) * $divider['quantity'], 2) }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900"></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Notes -->
                    @if($jobOrder->notes)
                    <div class="mt-6">
                        <h4 class="font-semibold text-gray-900 mb-2">Notes:</h4>
                        <p class="text-sm text-gray-700">{{ $jobOrder->notes }}</p>
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
                    <button wire:click="printJobOrder"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors">
                        Print
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

    <!-- Edit Box Modal -->
    @if($showEditBoxModal)
        @include('livewire.job-orders.job-order-management.edit-box-modal')
    @endif

    <!-- Edit Divider Modal -->
    @if($showEditDividerModal)
        @include('livewire.job-orders.job-order-management.edit-divider-modal')
    @endif

    <script>
        // Print functionality
        window.printJobOrder = function() {
            console.log('Print function called'); // Debug log

            var printContent = document.getElementById('printContent');
            if (!printContent) {
                console.error('Print content not found');
                return;
            }

            var originalContents = document.body.innerHTML;

            // Create a new window for printing
            var printWindow = window.open('', '_blank', 'width=800,height=600');
            printWindow.document.write('<html><head><title>Job Order Print</title>');
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
</div>
