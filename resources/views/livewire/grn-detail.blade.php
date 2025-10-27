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
                <div class="font-medium">{{ number_format($grn->total_value ?? 0, 2) }}</div>
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
                <div class="relative top-20 mx-auto p-5 border w-full max-w-4xl shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Process GRN to Stock</h3>
                    
                    @php
                        $enablePartialProcessing = \App\Models\SystemConfiguration::getValue('grn_enable_partial_processing', false);
                        $costingMethod = \App\Models\SystemConfiguration::getValue('grn_default_costing_method', 'FIFO');
                    @endphp
                    
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Processing Configuration</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg border">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Costing Method</p>
                                        <p class="text-lg font-semibold text-blue-600">{{ $costingMethod }}</p>
                                    </div>
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg border">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">Partial Processing</p>
                                        <p class="text-lg font-semibold {{ $enablePartialProcessing ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $enablePartialProcessing ? 'Enabled' : 'Disabled' }}
                                        </p>
                                    </div>
                                    <div class="w-8 h-8 {{ $enablePartialProcessing ? 'bg-green-100' : 'bg-red-100' }} rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 {{ $enablePartialProcessing ? 'text-green-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-3 text-center">These settings are configured in the master configuration. <a href="{{ route('configuration-management') }}" class="text-blue-600 hover:text-blue-800 underline">Change settings</a></p>
                    </div>

                    @if($enablePartialProcessing)
                    <div class="mb-4">
                        <div class="flex justify-between items-center mb-2">
                            <h4 class="text-sm font-medium text-gray-700">Partial Quantities</h4>
                            <div class="text-xs text-gray-500">
                                @php
                                    $receivedItems = $grn->items->where('qty_received_partial', '>', 0);
                                    $notReceivedItems = $grn->items->where('qty_received_partial', '=', 0);
                                @endphp
                                <span class="text-green-600">{{ $receivedItems->count() }} received</span>
                                @if($notReceivedItems->count() > 0)
                                    <span class="text-gray-400">• {{ $notReceivedItems->count() }} not received</span>
                                @endif
                            </div>
                        </div>
                        <div class="space-y-3 max-h-60 overflow-y-auto">
                            @foreach($grn->items as $item)
                                @if($item->qty_received_partial > 0)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border">
                                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $item->material_code }}</p>
                                            <p class="text-xs text-gray-600 mt-1">{{ $item->description }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Expected</p>
                                            <p class="text-sm font-medium">{{ number_format($item->qty_expected, 4) }} {{ $item->uom }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Received</p>
                                            <p class="text-sm font-medium text-green-600">{{ number_format($item->qty_received_partial, 4) }} {{ $item->uom }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-500">Pending</p>
                                            <p class="text-sm font-medium text-orange-600">{{ number_format($item->qty_pending, 4) }} {{ $item->uom }}</p>
                                        </div>
                                    </div>
                                    <div class="ml-6 flex items-center space-x-2">
                                        @php
                                            $availableForProcessing = $item->qty_received_partial - ($item->qty_processed ?? 0);
                                        @endphp
                                        <label class="text-sm font-medium text-gray-700">Process:</label>
                                        <input type="number" 
                                               wire:model="partialQuantities.{{ $item->id }}"
                                               wire:change="updatePartialQuantity({{ $item->id }}, $event.target.value)"
                                               min="0" 
                                               max="{{ $availableForProcessing }}"
                                               step="0.01"
                                               class="w-24 px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $partialQuantities[$item->id] > $availableForProcessing ? 'border-red-500 bg-red-50' : '' }}"
                                               title="Max: {{ $availableForProcessing }} {{ $item->uom }} (available for processing)"
                                               oninput="if(this.value > {{ $availableForProcessing }}) { this.value = {{ $availableForProcessing }}; }">
                                        <span class="text-xs text-gray-500">{{ $item->uom }}</span>
                                        <div class="text-xs text-gray-600">
                                            @if(($item->qty_processed ?? 0) > 0)
                                                <div class="text-blue-600 font-medium">({{ $item->qty_processed }} processed)</div>
                                            @endif
                                            <div class="text-green-600 font-medium">({{ $availableForProcessing }} available)</div>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class="flex items-center justify-between p-4 bg-gray-100 rounded-lg border border-gray-300 opacity-60">
                                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-500">{{ $item->material_code }}</p>
                                            <p class="text-xs text-gray-400 mt-1">{{ $item->description }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-400">Expected</p>
                                            <p class="text-sm font-medium text-gray-500">{{ number_format($item->qty_expected, 4) }} {{ $item->uom }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-400">Received</p>
                                            <p class="text-sm font-medium text-gray-500">{{ number_format($item->qty_received_partial, 4) }} {{ $item->uom }}</p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-gray-400">Pending</p>
                                            <p class="text-sm font-medium text-gray-500">{{ number_format($item->qty_pending, 4) }} {{ $item->uom }}</p>
                                        </div>
                                    </div>
                                    <div class="ml-6 flex items-center space-x-2">
                                        <label class="text-sm font-medium text-gray-500">Process:</label>
                                        <input type="number" 
                                               value="0"
                                               disabled
                                               class="w-24 px-3 py-2 text-sm border border-gray-300 rounded-md bg-gray-200 text-gray-500 cursor-not-allowed"
                                               title="No quantity received - cannot process">
                                        <span class="text-xs text-gray-400">{{ $item->uom }}</span>
                                        <span class="text-xs text-red-500 font-medium">Not Received</span>
                                    </div>
                                </div>
                                @endif
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

                    <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                        <div class="text-sm text-gray-500">
                            <span class="font-medium">{{ $grn->items->where('qty_received_partial', '>', 0)->count() }}</span> items to process
                            @if($grn->items->where('qty_received_partial', '=', 0)->count() > 0)
                                <span class="text-gray-400">({{ $grn->items->where('qty_received_partial', '=', 0)->count() }} not received)</span>
                            @endif
                        </div>
                        <div class="flex space-x-3">
                            <button wire:click="closeProcessingModal" 
                                    class="px-6 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
                                Cancel
                            </button>
                            <button wire:click="processToStock" 
                                    class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Process to Stock
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endif

    <!-- Partial Receiving Modal -->
    @if($showPartialReceivingModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
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







