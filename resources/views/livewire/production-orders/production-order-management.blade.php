<div>
        <!-- Header -->
        <div class="px-6 py-4 mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Production Orders</h1>
            <p class="mt-2 text-gray-600">Manage production orders created from confirmed purchase orders.</p>
        </div>

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

        <!-- Production Orders Table -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">All Production Orders</h3>
                    {{-- <button wire:click="openCreateModal" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Create Production Order
                    </button> --}}
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Production Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purchase Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer / Dimensions</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GRN Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($productionOrders as $po)
                            <tr class="hover:bg-gray-50 cursor-pointer"
                                wire:navigate href="{{ route('production-order-detail', $po->id) }}"
                                wire:key="production-order-{{ $po->id }}">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $po->production_order_number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $po->date->format('Y-m-d') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $po->jobOrder->supplier_po_number ?? 'N/A' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $po->jobOrder->customer->name ?? 'N/A' }}</div>
                                    @php
                                        // Get dimensions from first production order item
                                        $firstItem = $po->items->first();
                                        $dimensions = 'N/A';
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
                                    <div class="text-xs text-gray-500">{{ $dimensions }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $po->items->count() }} items
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-gray-100 text-gray-800',
                                            'in_progress' => 'bg-blue-100 text-blue-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$po->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ ucfirst($po->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $grn = $po->grns->first();
                                        if ($grn) {
                                            if ($grn->status === 'processed') {
                                                $grnStatus = 'Processed';
                                                $grnColor = 'bg-green-100 text-green-800';
                                            } elseif ($grn->status === 'cancelled') {
                                                $grnStatus = 'Cancelled';
                                                $grnColor = 'bg-red-100 text-red-800';
                                            } else {
                                                $grnStatus = 'Pending';
                                                $grnColor = 'bg-yellow-100 text-yellow-800';
                                            }
                                        } else {
                                            $grnStatus = 'No GRN';
                                            $grnColor = 'bg-gray-100 text-gray-800';
                                        }
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $grnColor }}">
                                        {{ $grnStatus }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('production-order-detail', $po->id) }}" 
                                       class="text-blue-600 hover:text-blue-900" 
                                       title="View Production Order"
                                       wire:click.stop
                                       wire:navigate>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">No production orders</h3>
                                    <p class="mt-1 text-sm text-gray-500">Get started by creating a production order from a confirmed purchase order.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Production Order Modal -->
    @if($showCreateModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between pb-4 border-b">
                        <h3 class="text-lg font-medium text-gray-900">Create Production Order</h3>
                        <button wire:click="closeCreateModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="mt-6 space-y-4">
                        @if($selectedPurchaseOrder)
                            <!-- Selected Purchase Order Info -->
                            <div class="bg-green-50 p-4 rounded-lg">
                                <h4 class="font-medium text-green-900 mb-2">Selected Purchase Order</h4>
                                <div class="text-sm text-green-700">
                                    <p><strong>PO Number:</strong> {{ $selectedPurchaseOrder->po_number }}</p>
                                    <p><strong>Supplier:</strong> {{ $selectedPurchaseOrder->supplier->name ?? 'N/A' }}</p>
                                    <p><strong>Job Order:</strong> {{ $selectedPurchaseOrder->jobOrder->supplier_po_number ?? 'N/A' }}</p>
                                    <p><strong>Items:</strong> {{ $selectedPurchaseOrder->items->count() }} items</p>
                                </div>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Purchase Order</label>
                            <select wire:model="form.purchase_order_id" 
                                    wire:change="updatedFormPurchaseOrderId"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Confirmed Purchase Order</option>
                                @foreach($confirmedPurchaseOrders as $po)
                                    <option value="{{ $po->id }}">
                                        {{ $po->po_number }} - {{ $po->supplier->name ?? 'N/A' }} ({{ $po->items->count() }} items)
                                    </option>
                                @endforeach
                            </select>
                            @error('form.purchase_order_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Production Date</label>
                            <input type="date" wire:model="form.date" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @error('form.date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea wire:model="form.notes" rows="3"
                                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Additional notes for the production order..."></textarea>
                            @error('form.notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t mt-6">
                        <button wire:click="closeCreateModal" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition-colors">
                            Cancel
                        </button>
                        <button wire:click="generateProductionOrder" 
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors">
                            Create Production Order
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
