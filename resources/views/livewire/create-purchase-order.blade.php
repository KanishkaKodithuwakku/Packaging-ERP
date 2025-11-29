<div class="w-full py-6 px-6">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Create Purchase Order</h2>
                    <p class="text-gray-600">Select job order items to create a purchase order</p>
                </div>
                <a href="{{ route('purchase-order-management') }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                    Back to Purchase Orders
                </a>
            </div>

            <!-- Progress Steps -->
            <div class="mb-8">
                <div class="flex items-center justify-center space-x-4">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full {{ $showJobOrderSelection ? 'bg-blue-500 text-white' : (!empty($selectedJobOrderIds) ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600') }} flex items-center justify-center text-sm font-medium">
                            1
                        </div>
                        <span class="ml-2 text-sm font-medium {{ $showJobOrderSelection ? 'text-blue-600' : (!empty($selectedJobOrderIds) ? 'text-green-600' : 'text-gray-500') }}">Select Job Order</span>
                    </div>
                    <div class="w-8 h-0.5 {{ !empty($selectedJobOrderIds) ? 'bg-green-500' : 'bg-gray-300' }}"></div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full {{ $showItemSelection ? 'bg-blue-500 text-white' : ($selectedItems ? 'bg-green-500 text-white' : 'bg-gray-300 text-gray-600') }} flex items-center justify-center text-sm font-medium">
                            2
                        </div>
                        <span class="ml-2 text-sm font-medium {{ $showItemSelection ? 'text-blue-600' : ($selectedItems ? 'text-green-600' : 'text-gray-500') }}">Select Items</span>
                    </div>
                    <div class="w-8 h-0.5 {{ $selectedItems ? 'bg-green-500' : 'bg-gray-300' }}"></div>
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full {{ $showFinalReview ? 'bg-blue-500 text-white' : 'bg-gray-300 text-gray-600' }} flex items-center justify-center text-sm font-medium">
                            3
                        </div>
                        <span class="ml-2 text-sm font-medium {{ $showFinalReview ? 'text-blue-600' : 'text-gray-500' }}">Review & Create</span>
                    </div>
                </div>
            </div>

            <!-- Step 1: Job Order Selection -->
            @if($showJobOrderSelection)
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4">Select Job Order</h3>
                    
                    @if($jobOrders->count() > 0)
                        <div class="grid gap-4">
                            @foreach($jobOrders as $jobOrder)
                                @php
                                    $jobOrderSupplierId = $jobOrder->supplier_id;
                                    $isSelected = in_array($jobOrder->id, $selectedJobOrderIds);
                                    $isDifferentSupplier = !empty($selectedJobOrderIds) && $currentSelectedSupplierId !== null && $jobOrderSupplierId !== $currentSelectedSupplierId;
                                    $isDisabled = $isDifferentSupplier && !$isSelected;
                                    
                                    // Get supplier name
                                    $jobOrderSupplier = $jobOrder->supplier ?? null;
                                    if ($jobOrderSupplier) {
                                        $supplierName = $jobOrderSupplier->name;
                                    } else {
                                        $suppliers = collect();
                                        $jobOrder->boxes->each(function($box) use ($suppliers) {
                                            if($box->supplier) $suppliers->push($box->supplier->name);
                                        });
                                        $jobOrder->dividers->each(function($divider) use ($suppliers) {
                                            if($divider->supplier) $suppliers->push($divider->supplier->name);
                                        });
                                        $uniqueSuppliers = $suppliers->unique()->implode(', ');
                                        $supplierName = $uniqueSuppliers ?: 'No Supplier';
                                    }
                                @endphp
                                <div class="border border-gray-200 rounded-lg p-4 transition-colors {{ $isSelected ? 'border-blue-500 bg-blue-50' : ($isDisabled ? 'border-gray-200 bg-gray-50 opacity-60' : 'hover:bg-gray-50 cursor-pointer') }}"
                                     @if(!$isDisabled) wire:click="toggleJobOrder({{ $jobOrder->id }})" @endif>
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center space-x-3">
                                            <input type="checkbox" 
                                                   {{ $isSelected ? 'checked' : '' }}
                                                   {{ $isDisabled ? 'disabled' : '' }}
                                                   class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 {{ $isDisabled ? 'opacity-50' : '' }}">
                                            <div>
                                                <p class="text-sm text-gray-600">
                                                    <span class="font-medium">Customer:</span> {{ $jobOrder->customer->name ?? 'No Customer' }}
                                                </p>
                                                <p class="text-sm text-gray-600">
                                                    <span class="font-medium">Supplier:</span> 
                                                    <span class="{{ $isDifferentSupplier ? 'text-red-600 font-medium' : '' }}">{{ $supplierName }}</span>
                                                    @if($isDifferentSupplier)
                                                        <span class="text-red-500 text-xs ml-1">(Different Supplier)</span>
                                                    @endif
                                                </p>
                                                <p class="text-sm text-gray-500">Status: {{ ucfirst($jobOrder->status) }}</p>
                                            </div>
                                        </div>
                                        <div class="flex-1 flex justify-center items-center">
                                            <p class="text-sm text-gray-600">
                                                <span class="font-medium">Job Order Number:</span> 
                                                <span class="font-semibold text-gray-900">{{ $jobOrder->job_order_number ?? $jobOrder->job_number }}</span>
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm text-gray-600">{{ $jobOrder->boxes->count() }} Boxes, {{ $jobOrder->dividers->count() }} Dividers</p>
                                            <p class="text-sm text-gray-500">{{ $jobOrder->created_at->format('M d, Y') }}</p>
                                            @if($isDisabled)
                                                <p class="text-xs text-red-500 mt-1">Cannot select - different supplier</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="mt-6 flex justify-end">
                            <button wire:click="proceedToItemSelection" 
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md"
                                    @if(empty($selectedJobOrderIds)) disabled @endif>
                                Next: Select Items ({{ count($selectedJobOrderIds) }} selected)
                            </button>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500">No job orders available for purchase order creation.</p>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Step 2: Item Selection -->
            @if($showItemSelection)
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Select Items to Purchase</h3>
                        <button wire:click="goBack" class="text-gray-500 hover:text-gray-700">← Back to Job Orders</button>
                    </div>
                    
                    
                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                        <!-- Available Items -->
                        <div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Search Item*</label>
                                <p class="text-xs text-gray-500 mb-2">Search by: dimensions (120x130), job order number, or unit (MM/CM/INCHES)</p>
                                <div class="relative">
                                    <input type="text" 
                                           wire:model.live="searchItem"
                                           placeholder="Search by dimensions (e.g., 120x130), job order, or unit (MM/CM/INCHES)..."
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                                    
                                    <!-- Search Results Dropdown -->
                                    @if($searchItem && count($this->getFilteredAvailableItems()) > 0)
                                        <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                            <table class="w-full">
                                                <thead class="bg-gray-50 sticky top-0">
                                                    <tr>
                                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Job Order</th>
                                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Code</th>
                                                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Stock</th>
                                                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200">
                                                    @foreach($this->getFilteredAvailableItems() as $item)
                                                        <tr class="hover:bg-gray-50 {{ $item['remaining_qty'] == 0 ? 'bg-red-50' : '' }}">
                                                            <td class="px-3 py-2 text-sm font-medium text-gray-900">{{ $item['description'] }}</td>
                                                            <td class="px-3 py-2 text-sm text-gray-600 text-center">{{ $item['job_order_number'] }}</td>
                                                            <td class="px-3 py-2 text-sm text-gray-600 text-center">{{ $item['type'] }}</td>
                                                            <td class="px-3 py-2 text-sm text-gray-600 text-right">{{ $item['remaining_qty'] }}</td>
                                                            <td class="px-3 py-2 text-sm text-center">
                                                                @if($item['remaining_qty'] > 0)
                                                                    <button wire:click="addItem('{{ $item['id'] }}')"
                                                                            class="text-blue-600 hover:text-blue-800 font-medium text-xs">
                                                                        Add
                                                                    </button>
                                                                @else
                                                                    <span class="text-red-500 text-xs">Out of Stock</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @elseif($searchItem && count($this->getFilteredAvailableItems()) == 0)
                                        <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                                            <div class="px-3 py-2 text-sm text-gray-500 text-center">
                                                No items found for "{{ $searchItem }}"
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <!-- Show all available items when no search -->
                            @if(empty($searchItem))
                                <div class="border border-gray-200 rounded-lg overflow-hidden">
                                    <div class="bg-gray-50 px-4 py-2">
                                        <h4 class="text-sm font-medium text-gray-900">All Available Items ({{ count($availableItems) }} items)</h4>
                                    </div>
                                    <table class="w-full">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Job Order</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item Name</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Available Qty</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @forelse($availableItems as $index => $item)
                                                @php
                                                    $isSelected = false;
                                                    $selectedQty = 0;
                                                    foreach($selectedItems as $selectedItem) {
                                                        if($selectedItem['id'] === $item['id']) {
                                                            $isSelected = true;
                                                            $selectedQty = $selectedItem['selected_qty'];
                                                            break;
                                                        }
                                                    }
                                                @endphp
                                                <tr class="hover:bg-gray-50 {{ $isSelected ? 'bg-green-50' : '' }}">
                                                    <td class="px-4 py-2 text-sm font-medium text-gray-500">{{ $index + 1 }}</td>
                                                    <td class="px-4 py-2 text-sm text-gray-600">{{ $item['job_order_number'] }}</td>
                                                    <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $item['type'] }}</td>
                                                    <td class="px-4 py-2 text-sm text-gray-600">{{ $item['description'] }}</td>
                                                    <td class="px-4 py-2 text-sm text-gray-600">
                                                        {{ $item['remaining_qty'] }}
                                                        @if($isSelected)
                                                            <span class="text-green-600 text-xs">(Selected: {{ $selectedQty }})</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-2 text-sm">
                                                        @if($isSelected)
                                                            <span class="text-green-600 text-xs">Selected</span>
                                                        @else
                                                            <button wire:click="addItem('{{ $item['id'] }}')"
                                                                    class="text-blue-600 hover:text-blue-800 font-medium">
                                                                Add
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                                        @if(count($selectedJobOrderIds) == 0)
                                                            <div class="text-red-600 font-medium mb-2">No job orders selected!</div>
                                                            <p>Please go back and select at least one job order with boxes or dividers.</p>
                                                        @else
                                                            <div class="text-orange-600 font-medium mb-2">No items available!</div>
                                                            <p>The selected job orders don't have any boxes or dividers with remaining quantities.</p>
                                                            <div class="mt-4 text-xs text-gray-500 bg-yellow-50 p-3 rounded-md">
                                                                <strong>Possible reasons:</strong><br>
                                                                • All items have already been purchased<br>
                                                                • Items have been fully allocated to existing purchase orders<br>
                                                                • Job orders don't have any boxes or dividers<br><br>
                                                                <strong>Solutions:</strong><br>
                                                                • Select different job orders with available items<br>
                                                                • Create new job orders for the same items<br>
                                                                • Check existing purchase orders to see what's been purchased<br>
                                                                • Modify existing job orders to add more quantities
                                                            </div>
                                                            
                                                            @if(!empty($purchasedItemsDetails))
                                                                <div class="mt-4 bg-blue-50 p-4 rounded-md">
                                                                    <h4 class="text-sm font-medium text-blue-900 mb-2">📋 Purchased Items Details:</h4>
                                                                    @foreach($purchasedItemsDetails as $jobOrderDetail)
                                                                        <div class="mb-3">
                                                                            <h5 class="text-xs font-semibold text-blue-800">Job Order: {{ $jobOrderDetail['job_order_number'] }}</h5>
                                                                            @foreach($jobOrderDetail['items'] as $item)
                                                                                <div class="ml-4 mt-2 text-xs text-blue-700">
                                                                                    <div class="font-medium">{{ $item['description'] }}</div>
                                                                                    <div class="text-gray-600">
                                                                                        Ordered: {{ number_format($item['order_qty']) }} | 
                                                                                        Purchased: {{ number_format($item['purchased_qty']) }} | 
                                                                                        Remaining: {{ number_format($item['remaining_qty']) }}
                                                                                    </div>
                                                                                    @if(!empty($item['purchase_orders']))
                                                                                        <div class="mt-1">
                                                                                            <span class="text-gray-500">Purchased via:</span>
                                                                                            @foreach($item['purchase_orders'] as $po)
                                                                                                <span class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs mr-1">
                                                                                                    {{ $po['po_number'] }} ({{ number_format($po['quantity']) }})
                                                                                                </span>
                                                                                            @endforeach
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                            
                                                            <!-- Development/Testing Only - Remove in Production -->
                        {{-- <div class="mt-4 bg-red-50 p-3 rounded-md border border-red-200">
                            <h4 class="text-sm font-medium text-red-900 mb-2">⚠️ Development Tools:</h4>
                            <p class="text-xs text-red-700 mb-2">For testing purposes only - this will reset purchase history:</p>
                            <p class="text-xs text-red-600 mb-2">Current Currency: {{ $currentSupplierCurrency }} ({{ $this->getCurrencySymbol() }})</p>
                            <button wire:click="resetPurchaseHistory" 
                                    class="bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-1 rounded"
                                    onclick="return confirm('⚠️ WARNING: This will delete all purchase order items and reset quantities. This is for testing only. Are you sure?')">
                                Reset Purchase History
                            </button>
                        </div> --}}
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Selected Items -->
                        <div>
                            <h4 class="font-medium text-gray-900 mb-4">Selected Items</h4>
                            
                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                <table class="w-full">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Job Order</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Item Name</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                                            </tr>
                                        </thead>
                                    <tbody class="divide-y divide-gray-200">
                                        @forelse($selectedItems as $index => $item)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-2 text-sm font-medium text-gray-500">{{ $item['original_number'] ?? ($index + 1) }}</td>
                                                <td class="px-4 py-2 text-sm text-gray-600">{{ $item['job_order_number'] ?? '' }}</td>
                                                <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ $item['type'] }}</td>
                                                <td class="px-4 py-2 text-sm text-gray-600">{{ $item['description'] }}</td>
                                                <td class="px-4 py-2 text-sm">
                                                    <input type="number" 
                                                           wire:model.live="selectedItems.{{ $index }}.selected_qty"
                                                           wire:change="updateSelectedQuantity('{{ $item['id'] }}', $event.target.value)"
                                                           min="1" 
                                                           max="{{ $item['available_qty'] }}"
                                                           step="1"
                                                           class="w-20 px-2 py-1 border border-gray-300 rounded text-sm focus:ring-blue-500 focus:border-blue-500 {{ $item['selected_qty'] > $item['available_qty'] ? 'border-red-500 bg-red-50' : '' }}"
                                                           title="Available: {{ $item['available_qty'] }}"
                                                           oninput="if(this.value > {{ $item['available_qty'] }}) { this.value = {{ $item['available_qty'] }}; } if(this.value < 1) { this.value = 1; }">
                                                </td>
                                                <td class="px-4 py-2 text-sm">
                                                    <button wire:click="removeItem('{{ $item['id'] }}')"
                                                            class="text-red-600 hover:text-red-800 font-medium">
                                                        Remove
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                                    No items selected
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6 flex justify-end">
                        <button wire:click="proceedToFinalReview" 
                                class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-md"
                                @if(empty($selectedItems)) disabled @endif>
                            Next: Review & Create
                        </button>
                    </div>
                </div>
            @endif


            <!-- Step 3: Supplier Selection (Multiple Suppliers) -->
            @if($showFinalReview && $multipleSuppliersDetected)
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Multiple Suppliers Detected</h3>
                        <button wire:click="goBack" class="text-gray-500 hover:text-gray-700">← Back</button>
                    </div>
                    
                    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            <p class="text-yellow-800 font-medium">Items from multiple suppliers detected. Please select which supplier to use for this purchase order.</p>
                        </div>
                    </div>
                    
                    <div class="grid gap-4">
                        @foreach($availableSuppliers as $supplier)
                            @php
                                $supplierData = $itemsBySupplier[$supplier['id']];
                                $itemCount = count($supplierData['items']);
                                $totalValue = collect($supplierData['items'])->sum(function($item) {
                                    return $item['selected_qty'] * $item['unit_cost'];
                                });
                            @endphp
                            <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 cursor-pointer transition-colors {{ $selectedSupplierId == $supplier['id'] ? 'border-blue-500 bg-blue-50' : '' }}"
                                 wire:click="selectSupplier({{ $supplier['id'] }})">
                                <div class="flex justify-between items-center">
                                    <div class="flex items-center space-x-3">
                                        <input type="radio" 
                                               {{ $selectedSupplierId == $supplier['id'] ? 'checked' : '' }}
                                               class="text-blue-600 focus:ring-blue-500">
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $supplier['name'] }}</h4>
                                            <p class="text-sm text-gray-600">{{ $itemCount }} items</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900">{{ $this->getCurrencySymbol() }}{{ number_format($totalValue, 2) }}</p>
                                        <p class="text-xs text-gray-500">Total Value</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-6 flex justify-between">
                        <button wire:click="createSeparatePurchaseOrders" 
                                class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-2 rounded-md">
                            Create Separate POs for Each Supplier
                        </button>
                        <div class="flex space-x-4">
                            <button wire:click="goBack" 
                                    class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md">
                                Back
                            </button>
                            <button wire:click="createPurchaseOrder" 
                                    class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-md"
                                    @if(!$selectedSupplierId) disabled @endif>
                                Create Purchase Order
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Step 3: Final Review (Single Supplier) -->
            @if($showFinalReview && !$multipleSuppliersDetected)
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold">Review Purchase Order</h3>
                        <button wire:click="goBack" class="text-gray-500 hover:text-gray-700">← Back</button>
                    </div>
                    
                    <div class="grid md:grid-cols-2 gap-6">
                        <!-- Purchase Order Details -->
                        <div>
                            <h4 class="font-semibold mb-3">Purchase Order Details</h4>
                            <div class="space-y-2 text-sm">
                                <div><strong>Job Orders:</strong> 
                                    @if(!empty($selectedJobOrderIds))
                                        @foreach($selectedJobOrderIds as $jobOrderId)
                                            @php
                                                $jobOrder = $jobOrders->find($jobOrderId);
                                                $jobOrderNumber = $jobOrder ? ($jobOrder->job_order_number ?: $jobOrder->job_number) : 'Unknown';
                                            @endphp
                                            {{ $jobOrderNumber }}{{ !$loop->last ? ', ' : '' }}
                                        @endforeach
                                    @else
                                        No job orders selected
                                    @endif
                                </div>
                                <div><strong>Supplier:</strong> {{ $suppliers->find($currentSelectedSupplierId)->name ?? 'N/A' }}</div>
                                <div><strong>PO Date:</strong> {{ $poDate }}</div>
                                @if($notes)
                                    <div><strong>Notes:</strong> {{ $notes }}</div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Selected Items -->
                        <div>
                            <h4 class="font-semibold mb-3">Selected Items</h4>
                            <div class="space-y-2">
                                @php
                                    $itemsToShow = $multipleSuppliersDetected ? $selectedSupplierItems : $selectedItems;
                                @endphp
                                @foreach($itemsToShow as $item)
                                    <div class="flex justify-between items-center text-sm border-b border-gray-100 pb-2">
                                        <div>
                                            <div class="font-medium">{{ $item['description'] }}</div>
                                            <div class="text-gray-600">Qty: {{ $item['selected_qty'] }} | {{ $this->getCurrencySymbol() }}{{ number_format($item['unit_cost'], 2) }} each</div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-medium">{{ $this->getCurrencySymbol() }}{{ number_format($item['selected_qty'] * $item['unit_cost'], 2) }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    
                    @php
                        $validation = $this->validateQuantities();
                    @endphp
                    
                    @if(!empty($validation['errors']))
                        <div class="mt-4 bg-red-50 border border-red-200 rounded-md p-4">
                            <h4 class="text-sm font-medium text-red-800 mb-2">⚠️ Validation Errors:</h4>
                            <ul class="text-sm text-red-700 space-y-1">
                                @foreach($validation['errors'] as $error)
                                    <li>• {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    
                    <div class="mt-6 flex justify-end space-x-4">
                        <button wire:click="goBack" 
                                class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md">
                            Back
                        </button>
                        <button wire:click="createPurchaseOrder" 
                                class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-md {{ !$validation['is_valid'] ? 'opacity-50 cursor-not-allowed' : '' }}"
                                {{ !$validation['is_valid'] ? 'disabled' : '' }}>
                            Create Purchase Order
                        </button>
                    </div>
                </div>
            @endif

            <!-- Flash Messages -->
            @if (session()->has('success'))
                <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>
</div>
