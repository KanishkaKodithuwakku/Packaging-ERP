<div>
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Goods Receipt Notes (GRNs)</h2>
                <div class="flex items-center space-x-4">
                    <input type="text" wire:model.live="search" placeholder="Search GRNs..."
                        class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 min-w-[200px]">
                    @if($filterSupplier || ($filterReceivingProgress && $filterReceivingProgress !== 'partial_and_not_received') || $filterDateFrom || $filterDateTo)
                    <button wire:click="resetFilters"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M15.312 11.424a5.5 5.5 0 0 1-9.201 2.466l-.312-.311h2.433a.75.75 0 0 0 0-1.5H3.989a.75.75 0 0 0-.75.75v4.242a.75.75 0 0 0 1.5 0v-2.43l.31.31a7 7 0 0 0 11.712-3.138.75.75 0 0 0-1.449-.39Zm1.23-3.723a.75.75 0 0 0 .219-.53V2.929a.75.75 0 0 0-1.5 0V5.36l-.31-.31A7 7 0 0 0 3.239 8.188a.75.75 0 1 0 1.448.389A5.5 5.5 0 0 1 13.89 6.11l.311.31h-2.432a.75.75 0 0 0 0 1.5h4.243a.75.75 0 0 0 .53-.219Z" clip-rule="evenodd" />
                        </svg>
                        Reset Filter
                    </button>
                    @else
                    <button wire:click="openFilterModal"
                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z">
                            </path>
                        </svg>
                        Filter
                    </button>
                    @endif
                    <button wire:click="create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Add New GRN
                    </button>
                </div>
            </div>
        </div>

        <div class="p-6">
            <!-- GRNs Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GRN No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lot Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items & Quantities</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Receiving Progress</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($grns as $grn)
                            <tr class="hover:bg-gray-50 cursor-pointer" wire:key="grn-{{ $grn->id }}" >
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" wire:navigate href="{{ route('grn-detail', $grn->id) }}">
                                    {{ $grn->grn_no }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" wire:navigate href="{{ route('grn-detail', $grn->id) }}">
                                    @if($grn->isFromProductionOrder())
                                        <div class="flex items-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 mr-2">
                                                Production
                                            </span>
                                            {{ $grn->productionOrder->supplier->name ?? 'N/A' }}
                                        </div>
                                    @elseif($grn->isFromPurchaseOrder())
                                        <div class="flex items-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2">
                                                Purchase
                                            </span>
                                            {{ $grn->purchaseOrder->supplier->name ?? 'N/A' }}
                                        </div>
                                    @else
                                        {{ $grn->supplierOrder->supplier->name ?? 'N/A' }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" wire:navigate href="{{ route('grn-detail', $grn->id) }}">
                                    @if($grn->isFromProductionOrder())
                                        <div class="text-sm text-gray-900">{{ $grn->productionOrder->production_order_number ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">
                                            @if($grn->item_type === 'multi')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                                    Multi-Item
                                                </span>
                                            @else
                                                {{ ucfirst($grn->item_type ?? 'Item') }}
                                            @endif
                                        </div>
                                    @elseif($grn->isFromPurchaseOrder())
                                        <div class="text-sm text-gray-900">{{ $grn->purchaseOrder->po_number ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">Purchase Order</div>
                                    @else
                                        {{ $grn->supplierOrder->po_no ?? 'N/A' }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600" wire:navigate href="{{ route('grn-detail', $grn->id) }}">{{ $grn->lot_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($grn->items->count() > 0)
                                        <div class="text-sm font-medium text-gray-900">{{ $grn->getTotalQuantity() }} PCS ({{ $grn->getItemsCount() }} items)</div>
                                        <div class="text-xs text-gray-500">
                                            @foreach($grn->items->take(2) as $item)
                                                {{ $item->description }} ({{ $item->qty_received }})<br>
                                            @endforeach
                                            @if($grn->items->count() > 2)
                                                <span class="text-gray-400">+{{ $grn->items->count() - 2 }} more items</span>
                                            @endif
                                        </div>
                                    @else
                                        No items
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" wire:navigate href="{{ route('grn-detail', $grn->id) }}">
                                    @php
                                        $totalExpected = $grn->getTotalExpectedQuantity();
                                        $totalReceived = $grn->getTotalPartiallyReceivedQuantity();
                                        $totalPending = $grn->getTotalPendingQuantity();
                                        $receivingPercentage = $totalExpected > 0 ? ($totalReceived / $totalExpected) * 100 : 0;

                                        if ($grn->isFullyReceived()) {
                                            $statusText = 'Fully Received';
                                            $statusColor = 'bg-green-100 text-green-800';
                                            $progressColor = 'bg-green-500';
                                            $progressWidth = 100;
                                        } elseif ($grn->hasPartialReceiving()) {
                                            $statusText = 'Partial (' . number_format($receivingPercentage, 1) . '%)';
                                            $statusColor = 'bg-yellow-100 text-yellow-800';
                                            $progressColor = 'bg-yellow-500';
                                            $progressWidth = $receivingPercentage;
                                        } else {
                                            $statusText = 'Not Received';
                                            $statusColor = 'bg-gray-100 text-gray-800';
                                            $progressColor = 'bg-gray-300';
                                            $progressWidth = 0;
                                        }
                                    @endphp
                                    <div class="flex flex-col space-y-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                            {{ $statusText }}
                                        </span>
                                        <div class="flex items-center space-x-2">
                                            <div class="flex-1 bg-gray-200 rounded-full h-2">
                                                <div class="{{ $progressColor }} h-2 rounded-full transition-all duration-300"
                                                     style="width: {{ $progressWidth }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-600 min-w-0">
                                                {{ number_format($totalReceived, 0) }}/{{ number_format($totalExpected, 0) }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" wire:navigate href="{{ route('grn-detail', $grn->id) }}">{{ $grn->received_date->format('Y-m-d') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" onclick="event.stopPropagation(); event.preventDefault(); return false;">
                                    <div class="flex items-center space-x-2" onclick="event.stopPropagation(); event.preventDefault(); return false;">
                                        <button disabled onclick="event.stopPropagation(); event.preventDefault(); return false;" class="text-gray-400 cursor-not-allowed flex items-center" title="Edit GRN is disabled">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        @if(!$grn->isFullyReceived() && !$grn->hasPartialReceiving())
                                        <button wire:click.stop.prevent="openDeleteConfirmModal({{ $grn->id }})" onclick="event.stopPropagation(); event.preventDefault(); return false;" class="text-red-600 hover:text-red-900 flex items-center"
                                                title="Delete">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                        @else
                                        <button disabled onclick="event.stopPropagation(); event.preventDefault(); return false;" class="text-gray-400 cursor-not-allowed flex items-center"
                                                title="Cannot delete GRN with receiving progress">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $grns->links() }}
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        {{ $editing ? 'Edit GRN' : 'Create New GRN' }}
                    </h3>

                    <form wire:submit.prevent="save">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Supplier *</label>
                            <select wire:model="supplier_id" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">PO Reference (Optional)</label>
                            <input type="text" wire:model="po_reference" placeholder="Enter PO reference if any" class="w-full border border-gray-300 rounded-md px-3 py-2">
                            @error('po_reference') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">GRN Number</label>
                            <input type="text" wire:model="grn_no" class="w-full border border-gray-300 rounded-md px-3 py-2">
                            @error('grn_no') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Lot Code</label>
                            <input type="text" wire:model="lot_code" placeholder="Auto-generated if empty" class="w-full border border-gray-300 rounded-md px-3 py-2">
                            @error('lot_code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Received Date</label>
                            <input type="date" wire:model="received_date" class="w-full border border-gray-300 rounded-md px-3 py-2">
                            @error('received_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button type="button" wire:click="closeModal" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancel
                            </button>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                {{ $editing ? 'Update' : 'Create' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteConfirmModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>

                    <h3 class="text-lg font-medium text-gray-900 text-center mb-2">
                        Delete GRN
                    </h3>

                    <div class="text-center text-sm text-gray-600 mb-6">
                        <p>Are you sure you want to delete this GRN?</p>
                        @if($grnToDelete)
                            @php
                                $grn = \App\Models\GRN::find($grnToDelete);
                            @endphp
                            @if($grn)
                                <p class="font-semibold text-gray-900 mt-2">
                                    {{ $grn->grn_no }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    This action cannot be undone.
                                </p>
                            @endif
                        @endif
                    </div>

                    <div class="flex space-x-3">
                        <button wire:click="closeDeleteConfirmModal"
                                class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md transition-colors">
                            Cancel
                        </button>
                        @if($grnToDelete)
                        <button wire:click="delete({{ $grnToDelete }})"
                                class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                            Delete
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Filter Modal -->
    @if($showFilterModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click.self="closeFilterModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <!-- Modal Header -->
                    <div class="flex justify-between items-center pb-4 border-b">
                        <h3 class="text-lg font-medium text-gray-900">
                            Filter GRNs
                        </h3>
                        <button wire:click="closeFilterModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Filter Form -->
                    <div class="mt-6">
                        <div class="space-y-3">
                            <!-- Row 1: Start Date, End Date -->
                            <div class="flex gap-3">
                                <div class="flex items-center gap-3 flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">Start Date</label>
                                    <div class="flex-1">
                                        <input type="date" wire:model.live="filterDateFrom"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">End Date</label>
                                    <div class="flex-1">
                                        <input type="date" wire:model.live="filterDateTo"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                            </div>

                            <!-- Row 2: Supplier, Status -->
                            <div class="flex gap-3">
                                <div class="flex items-center gap-3 flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">Supplier</label>
                                    <div class="flex-1">
                                        <select wire:model.live="filterSupplier"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">All Suppliers</option>
                                            @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="flex items-center gap-3 flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">Receiving Progress</label>
                                    <div class="flex-1">
                                        <select wire:model.live="filterReceivingProgress"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="all">All</option>
                                            <option value="partial_and_not_received">Partial & Not Received</option>
                                            <option value="not_received">Not Received</option>
                                            <option value="partial">Partial</option>
                                            <option value="fully_received">Fully Received</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-3 mt-8 pt-6 border-t">
                            <button type="button"
                                    wire:click="closeFilterModal"
                                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
