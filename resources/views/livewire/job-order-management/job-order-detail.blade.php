<!-- Job Order Details View -->
<div class="bg-white rounded-lg shadow-sm border p-6">
    <div class="flex justify-between items-start mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Job Order Details</h2>
            <p class="text-gray-600">Job Number: {{ $form['job_number'] }}</p>
        </div>
        <button wire:click="closeDetailModal" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Job Order Information -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Left Column -->
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Job Number</label>
                <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                    {{ $form['job_number'] }}
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                    {{ \Carbon\Carbon::parse($form['date'])->format('M d, Y') }}
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                    @if(isset($form['supplier']['name']))
                        {{ $form['supplier']['name'] }} ({{ $form['supplier']['code'] }})
                    @else
                        N/A
                    @endif
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Supplier PO Number</label>
                <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                    {{ $form['supplier_po_number'] }}
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Order Number</label>
                <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                    {{ $form['purchase_order_no'] ?: 'N/A' }}
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Order Date</label>
                <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                    {{ $form['po_date'] ? \Carbon\Carbon::parse($form['po_date'])->format('M d, Y') : 'N/A' }}
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900 min-h-[60px]">
                    {{ $form['notes'] ?: 'No notes' }}
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                    @if(isset($form['customer']['name']))
                        {{ $form['customer']['name'] }} ({{ $form['customer']['code'] }})
                    @else
                        N/A
                    @endif
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer Address</label>
                <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900 min-h-[60px]">
                    {{ $form['customer_address'] }}
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <div class="w-full px-3 py-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $form['status'] === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                        {{ $form['status'] === 'confirmed' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $form['status'] === 'in_production' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $form['status'] === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $form['status'] === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ ucfirst($form['status']) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mb-6 flex justify-between items-center">
        <button wire:click="openBoxDividerModal" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Box/Divider
        </button>
        
        @if(count($boxes) > 0 || count($dividers) > 0)
            <button wire:click="saveBoxDividerChanges" 
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Save Changes
            </button>
        @endif
    </div>

    <!-- Items Table -->
    <div class="border border-gray-200 rounded-lg overflow-hidden">
        <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Items</h3>
        </div>
        
        @if(count($boxes) > 0 || count($dividers) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DIMENSIONS</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">QTY</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PLY</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">REEL SIZE</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CUT SIZE</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($boxes as $index => $box)
                            <tr>
                                <td class="px-4 py-4 text-sm text-gray-900">
                                    {{ number_format($box['length'], 3) }}x{{ number_format($box['width'], 3) }}x{{ number_format($box['height'], 3) }} {{ $box['unit'] }}
                                    <div class="text-xs text-gray-500">{{ $box['dimension_type'] }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-900">{{ number_format($box['order_qty']) }}</td>
                                <td class="px-4 py-4 text-sm text-gray-900">{{ $box['ply'] }}</td>
                                <td class="px-4 py-4 text-sm text-gray-900">{{ number_format($box['reel_size'], 3) }}</td>
                                <td class="px-4 py-4 text-sm text-gray-900">{{ number_format($box['cut_size'], 3) }}</td>
                                <td class="px-4 py-4 text-sm text-gray-900">
                                    <button wire:click="removeBox({{ $index }})" 
                                            class="text-red-600 hover:text-red-900">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                        
                        @foreach($dividers as $index => $divider)
                            <tr>
                                <td class="px-4 py-4 text-sm text-gray-900">
                                    <div class="text-xs text-gray-500">Divider</div>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-900">{{ number_format($divider['quantity']) }}</td>
                                <td class="px-4 py-4 text-sm text-gray-900">{{ $divider['ply'] }}</td>
                                <td class="px-4 py-4 text-sm text-gray-900">-</td>
                                <td class="px-4 py-4 text-sm text-gray-900">-</td>
                                <td class="px-4 py-4 text-sm text-gray-900">
                                    <button wire:click="removeDivider({{ $index }})" 
                                            class="text-red-600 hover:text-red-900">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-4 py-8 text-center text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No items</h3>
                <p class="mt-1 text-sm text-gray-500">Get started by adding a box or divider.</p>
            </div>
        @endif
    </div>
</div>
