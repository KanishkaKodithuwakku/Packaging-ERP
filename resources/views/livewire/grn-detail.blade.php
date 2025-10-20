<div class="px-6 py-4">
    <a wire:navigate href="{{ route('grns') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-800">
        ← Back to GRNs
    </a>

    <div class="flex justify-between items-center">
        <h1 class="mt-2 text-2xl font-bold">GRN Details</h1>
        
        @if($grn && $grn->status === 'pending')
            <div class="flex space-x-2">
                <button wire:click="openModal" 
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Process to Stock
                </button>
            </div>
        @endif
    </div>

    @if($grn)

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-xs text-gray-500">GRN No</label>
                <div class="font-medium">{{ $grn->grn_no }}</div>
            </div>
            <div>
                <label class="text-xs text-gray-500">Lot Code</label>
                <div class="font-medium">{{ $grn->lot_code }}</div>
            </div>
            <div>
                <label class="text-xs text-gray-500">Received Date</label>
                <div class="font-medium">{{ optional($grn->received_date)->format('Y-m-d') }}</div>
            </div>
            <div>
                <label class="text-xs text-gray-500">Source</label>
                <div class="font-medium">
                    @if($grn->isFromProductionOrder())
                        Production - {{ $grn->productionOrder->production_order_number ?? 'N/A' }}
                    @else
                        Supplier PO - {{ $grn->supplierOrder->po_no ?? 'N/A' }}
                    @endif
                </div>
            </div>
            <div>
                <label class="text-xs text-gray-500">Status</label>
                <div class="font-medium">
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
                </div>
            </div>
            @if($grn->status === 'processed')
            <div>
                <label class="text-xs text-gray-500">Processed At</label>
                <div class="font-medium">{{ $grn->processed_at?->format('Y-m-d H:i') ?? 'N/A' }}</div>
            </div>
            <div>
                <label class="text-xs text-gray-500">Total Value</label>
                <div class="font-medium">${{ number_format($grn->total_value ?? 0, 2) }}</div>
            </div>
            @endif
        </div>

        <div class="mt-8">
            <h2 class="text-lg font-semibold mb-3">Items</h2>
            <div class="bg-white shadow rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Processed</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($grn->items as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ucfirst($item->item_type) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->description }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->material_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->qty_received }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->qty_processed ?? 0 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->qty_remaining ?? $item->qty_received }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if(($item->qty_processed ?? 0) >= $item->qty_received)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Completed
                                        </span>
                                    @elseif(($item->qty_processed ?? 0) > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Partial
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Pending
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-8 text-center text-gray-500">No items</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

            <!-- Processing Modal -->
            @if($showProcessingModal)
            <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Process GRN to Stock</h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Costing Method</label>
                        <select wire:model="costingMethod" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="FIFO">FIFO (First In, First Out)</option>
                            <option value="LIFO">LIFO (Last In, First Out)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" wire:model="enablePartialProcessing" class="mr-2">
                            <span class="text-sm font-medium text-gray-700">Enable Partial Processing</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1">Allow processing partial quantities of items</p>
                    </div>

                    @if($enablePartialProcessing)
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Partial Quantities</h4>
                        <div class="space-y-2 max-h-40 overflow-y-auto">
                            @foreach($grn->items as $item)
                            <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                                <div class="flex-1">
                                    <p class="text-sm font-medium">{{ $item->material_code }}</p>
                                    <p class="text-xs text-gray-500">{{ $item->description }}</p>
                                    <p class="text-xs text-gray-500">Total: {{ $item->qty_received }} {{ $item->uom }}</p>
                                </div>
                                <div class="ml-4">
                                    <input type="number" 
                                           wire:model="partialQuantities.{{ $item->id }}"
                                           wire:change="updatePartialQuantity({{ $item->id }}, $event.target.value)"
                                           min="0" 
                                           max="{{ $item->qty_received }}"
                                           step="0.01"
                                           class="w-20 px-2 py-1 text-sm border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500">
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @else
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Processing Summary</h4>
                        <div class="text-sm text-gray-600">
                            <p>Items to process: {{ $processingStatus['total_items'] ?? 0 }}</p>
                            <p>Already processed: {{ $processingStatus['processed_items'] ?? 0 }}</p>
                            <p>Pending: {{ $processingStatus['pending_items'] ?? 0 }}</p>
                        </div>
                    </div>
                    @endif

                    <div class="flex justify-end space-x-3">
                        <button wire:click="closeProcessingModal" 
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Cancel
                        </button>
                        <button wire:click="processToStock" 
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Process to Stock
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endif
</div>







