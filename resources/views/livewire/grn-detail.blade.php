<div class="px-6 py-4">
    <a wire:navigate href="{{ route('grns') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-800">
        ← Back to GRNs
    </a>

    <div class="flex justify-between items-center">
        <h1 class="mt-2 text-2xl font-bold">GRN Details</h1>
        
        @if($grn && ($grn->status === 'pending' || $grn->hasPartialReceiving()))
            <div class="flex space-x-2">
                <button wire:click="openModal" 
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    @if($grn->hasPartialReceiving() && !$grn->isFullyReceived())
                        Process Partial to Stock
                    @else
                        Process to Stock
                    @endif
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
                <div class="font-medium">
                    {{ $grn->lot_code }}
                    @if($grn->isMultiItemGRN())
                        <span class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            Multi-Item
                        </span>
                    @endif
                </div>
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
                    @elseif($grn->isFromPurchaseOrder())
                        Purchase Order - {{ $grn->purchaseOrder->po_number ?? 'N/A' }}
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
            <div>
                <label class="text-xs text-gray-500">Receiving Status</label>
                <div class="font-medium">
                    @if($grn->isFullyReceived())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Fully Received
                        </span>
                    @elseif($grn->hasPartialReceiving())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            Partial ({{ number_format($grn->getReceivingPercentage(), 1) }}%)
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            Not Received
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expected</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pending</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($grn->items as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ ucfirst($item->item_type) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->description }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->material_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->qty_expected ?? $item->qty_received }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->qty_received_partial ?? 0 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->qty_pending ?? ($item->qty_expected ?? $item->qty_received) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <div class="flex items-center">
                                        <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $item->getReceivedPercentage() ?? 0 }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-600">{{ number_format($item->getReceivedPercentage() ?? 0, 1) }}%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($item->is_fully_received ?? false)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Fully Received
                                        </span>
                                    @elseif(($item->qty_received_partial ?? 0) > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            Partial
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Pending
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if(!($item->is_fully_received ?? false))
                                        <button wire:click="openPartialReceivingModal({{ $item->id }})" 
                                                class="text-blue-600 hover:text-blue-900 text-xs font-medium">
                                            Add Receipt
                                        </button>
                                    @else
                                        <span class="text-gray-400 text-xs">Complete</span>
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
                    
                    @php
                        $enablePartialProcessing = \App\Models\SystemConfiguration::getValue('grn_enable_partial_processing', false);
                        $costingMethod = \App\Models\SystemConfiguration::getValue('grn_default_costing_method', 'FIFO');
                    @endphp
                    
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Processing Configuration</h4>
                        <div class="text-sm text-gray-600 bg-gray-50 p-3 rounded">
                            <p><strong>Costing Method:</strong> {{ $costingMethod }}</p>
                            <p><strong>Partial Processing:</strong> {{ $enablePartialProcessing ? 'Enabled' : 'Disabled' }}</p>
                            <p class="text-xs text-gray-500 mt-1">These settings are configured in the master configuration. <a href="{{ route('configuration-management') }}" class="text-blue-600 hover:text-blue-800">Change settings</a></p>
                        </div>
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
                                    <p class="text-xs text-gray-500">Expected: {{ $item->qty_expected }} {{ $item->uom }}</p>
                                    <p class="text-xs text-gray-500">Received: {{ $item->qty_received_partial }} {{ $item->uom }}</p>
                                    <p class="text-xs text-gray-500">Pending: {{ $item->qty_pending }} {{ $item->uom }}</p>
                                </div>
                                <div class="ml-4">
                                    <input type="number" 
                                           wire:model="partialQuantities.{{ $item->id }}"
                                           wire:change="updatePartialQuantity({{ $item->id }}, $event.target.value)"
                                           min="0" 
                                           max="{{ $item->qty_received_partial }}"
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

    <!-- Partial Receiving Modal -->
    @if($showPartialReceivingModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Add Partial Receipt</h3>
                
                @if($selectedGRNItem)
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Item Details</label>
                        <div class="text-sm text-gray-600">
                            <div><strong>Description:</strong> {{ $selectedGRNItem->description }}</div>
                            <div><strong>Expected:</strong> {{ $selectedGRNItem->qty_expected }} {{ $selectedGRNItem->uom }}</div>
                            <div><strong>Already Received:</strong> {{ $selectedGRNItem->qty_received_partial }} {{ $selectedGRNItem->uom }}</div>
                            <div><strong>Pending:</strong> {{ $selectedGRNItem->qty_pending }} {{ $selectedGRNItem->uom }}</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="partial_quantity" class="block text-sm font-medium text-gray-700 mb-2">Quantity to Receive</label>
                        <input type="number" 
                               wire:model="partialReceivingForm.quantity" 
                               step="0.01" 
                               min="0.01" 
                               max="{{ $selectedGRNItem->qty_pending }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               placeholder="Enter quantity to receive">
                        @error('partialReceivingForm.quantity') 
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p> 
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="partial_notes" class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                        <textarea wire:model="partialReceivingForm.notes" 
                                  rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                  placeholder="Add any notes about this partial receipt"></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button wire:click="closePartialReceivingModal" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                            Cancel
                        </button>
                        <button wire:click="addPartialReceiving" 
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                            Add Receipt
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>







