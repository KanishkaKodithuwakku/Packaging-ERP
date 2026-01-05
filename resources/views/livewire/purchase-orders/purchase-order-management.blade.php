<div>
    <!-- Header -->
    <div class="px-6 py-4 mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Purchase Orders</h1>
        <p class="mt-2 text-gray-600">Manage supplier purchase orders generated from job orders.</p>
    </div>

    <!-- Success/Error Messages -->
    @php
        $successMessage = session()->pull('success');
        $errorMessage = session()->pull('error');
    @endphp
    
    @if ($successMessage)
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {!! $successMessage !!}
    </div>
    @endif

    @if ($errorMessage)
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ $errorMessage }}
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
                <div class="flex items-center space-x-4">
                    <input type="text" wire:model.live="search" placeholder="Search purchase orders..."
                        class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 min-w-[200px]">
                    @if($filterSupplier || $filterStatus || $filterGRNStatus || $filterDateFrom || $filterDateTo)
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job
                            Order</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            GRN Status</th>
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
                            {{ \App\Helpers\DateFormatHelper::format($po->date) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $po->supplier->name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $po->supplier->code ?? '' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $jobOrders = $po->jobOrders();
                                $customers = $jobOrders->pluck('customer')->filter()->unique('id');
                            @endphp
                            @if($customers->count() > 0)
                                @if($customers->count() == 1)
                                    <div class="text-sm text-gray-900">{{ $customers->first()->name ?? 'N/A' }}</div>
                                    <div class="text-xs text-gray-500">{{ $customers->first()->code ?? '' }}</div>
                                @else
                                    <div class="text-sm text-gray-900">{{ $customers->count() }} Customers</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $customers->pluck('name')->take(2)->implode(', ') }}{{ $customers->count() > 2 ? '...' : '' }}
                                    </div>
                                @endif
                            @else
                                <div class="text-sm text-gray-500">N/A</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $jobOrders = $po->jobOrders();
                            @endphp
                            @if($jobOrders->count() > 0)
                                @if($jobOrders->count() == 1)
                                    <div class="text-sm text-gray-900">{{ $jobOrders->first()->supplier_po_number ?? $jobOrders->first()->job_order_number ?? $jobOrders->first()->job_number ?? 'N/A' }}</div>
                                @else
                                    <div class="text-sm text-gray-900">{{ $jobOrders->count() }} Job Orders</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $jobOrders->take(2)->map(function($jo) { return $jo->supplier_po_number ?? $jo->job_order_number ?? $jo->job_number ?? 'N/A'; })->implode(', ') }}{{ $jobOrders->count() > 2 ? '...' : '' }}
                                    </div>
                                @endif
                            @else
                                <div class="text-sm text-gray-500">N/A</div>
                            @endif
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
                            @if($po->status === 'cancelled' && $po->cancellation_reason)
                                <div class="mt-1 text-xs text-gray-500">
                                    Reason: {{ Str::limit($po->cancellation_reason, 50) }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @php
                            // Get GRN status for this purchase order
                            $grn = \App\Models\GRN::where('purchase_order_id', $po->id)->first();
                            if ($grn) {
                            $totalExpected = $grn->getTotalExpectedQuantity();
                            $totalReceived = $grn->getTotalPartiallyReceivedQuantity();
                            $totalPending = $grn->getTotalPendingQuantity();
                            $receivingPercentage = $totalExpected > 0 ? ($totalReceived / $totalExpected) * 100 : 0;

                            if ($grn->isFullyReceived()) {
                            $grnStatus = 'Fully Received';
                            $grnColor = 'bg-green-100 text-green-800';
                            $progressColor = 'bg-green-500';
                            $progressWidth = 100;
                            } elseif ($grn->hasPartialReceiving()) {
                            $grnStatus = 'Partial (' . number_format($receivingPercentage, 1) . '%)';
                            $grnColor = 'bg-yellow-100 text-yellow-800';
                            $progressColor = 'bg-yellow-500';
                            $progressWidth = $receivingPercentage;
                            } else {
                            $grnStatus = 'Not Received';
                            $grnColor = 'bg-gray-100 text-gray-800';
                            $progressColor = 'bg-gray-300';
                            $progressWidth = 0;
                            }
                            } else {
                            $grnStatus = 'No GRN';
                            $grnColor = 'bg-gray-100 text-gray-800';
                            $progressColor = 'bg-gray-300';
                            $progressWidth = 0;
                            }
                            @endphp
                            <div class="flex flex-col space-y-2">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $grnColor }}">
                                    {{ $grnStatus }}
                                </span>
                                @if($grn)
                                <div class="flex items-center space-x-2">
                                    <div class="flex-1 bg-gray-200 rounded-full h-2">
                                        <div class="{{ $progressColor }} h-2 rounded-full transition-all duration-300"
                                            style="width: {{ $progressWidth }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-600 min-w-0">
                                        {{ number_format($totalReceived, 0) }}/{{ number_format($totalExpected, 0) }}
                                    </span>
                                </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <button wire:click="viewPurchaseOrder({{ $po->id }})"
                                    class="text-gray-400 hover:text-gray-600" title="View Purchase Order">
                                    <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" style="color: #8d8d8d;" onMouseOver="this.style.color='#242629'" onMouseOut="this.style.color='#8d8d8d'">
                                        <path stroke="currentColor" stroke-width="2" d="M21 12c0 1.2-4.03 6-9 6s-9-4.8-9-6c0-1.2 4.03-6 9-6s9 4.8 9 6Z"/>
                                        <path stroke="currentColor" stroke-width="2" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                      </svg>

                                </button>

                                @if($po->status === 'draft')
                                <button wire:click="openPhoneConfirmModal({{ $po->id }})"
                                    class="text-green-600 hover:text-green-900" title="Confirm Purchase Order" >
                                    <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 24 24" style="color: #8d8d8d;" onMouseOver="this.style.color='green'" onMouseOut="this.style.color='#242629'">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 3v4a1 1 0 0 1-1 1H5m4 6 2 2 4-4m4-8v16a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V7.914a1 1 0 0 1 .293-.707l3.914-3.914A1 1 0 0 1 9.914 3H18a1 1 0 0 1 1 1Z"/>
                                      </svg>

                                </button>
                                {{-- <button wire:click="openPhoneConfirmModal({{ $po->id }})"
                                    class="text-yellow-600 hover:text-yellow-900" title="Confirm (Update Prices)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                        </path>
                                    </svg>
                                </button> --}}
                                @endif

                                {{-- @if($po->status === 'draft')
                                <button wire:click="openCancelConfirmModal({{ $po->id }})"
                                    class="text-red-600 hover:text-red-900"
                                    title="Cancel Purchase Order">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                @endif --}}

                                @if($po->status === 'confirmed')
                                <button wire:click="openGRNConfirmModal({{ $po->id }})"
                                    class="{{ $po->grn->isNotEmpty() ? 'text-gray-300 cursor-not-allowed' : 'text-gray-400 hover:text-gray-600' }}"
                                    title="{{ $po->grn->isNotEmpty() ? 'GRN Already Created' : 'Create GRN' }}"
                                    {{ $po->grn->isNotEmpty() ? 'disabled' : '' }}>
                                    <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24" style="color: #8d8d8d;" onMouseOver="this.style.color='#242629'" onMouseOut="this.style.color='#8d8d8d'">
                                        <path d="M17 20v-5h2v6.988H3V15h1.98v5H17Z"/>
                                        <path d="m6.84 14.522 8.73 1.825.369-1.755-8.73-1.825-.369 1.755Zm1.155-4.323 8.083 3.764.739-1.617-8.083-3.787-.739 1.64Zm3.372-5.481L10.235 6.08l6.859 5.704 1.132-1.362-6.859-5.704ZM15.57 17H6.655v2h8.915v-2ZM12.861 3.111l6.193 6.415 1.414-1.415-6.43-6.177-1.177 1.177Z"/>
                                      </svg>

                                </button>
                                @endif

                                @if($po->status === 'draft')
                                <button wire:click="openCancelConfirmModal({{ $po->id }})"
                                    class="" style="color: #c40d0d;"
                                    title="Cancel Purchase Order">
                                    <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" style="color: #8d8d8d;" onMouseOver="this.style.color='red'" onMouseOut="this.style.color='#8d8d8d'">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/>
                                      </svg>

                                </button>
                                @endif

                                @if($po->status === 'confirmed')
                                <button wire:click="openCancelConfirmModal({{ $po->id }})"
                                    class="text-gray-300 cursor-not-allowed" style="color: #9ca3af;"
                                    title="Cannot cancel confirmed purchase order"
                                    disabled>
                                    <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" style="color: #9ca3af;">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/>
                                      </svg>

                                </button>
                                @endif

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900 text-center">No purchase orders</h3>
                                <p class="mt-1 text-sm text-gray-500 text-center">Get started by generating a purchase order from a job
                                    order.</p>
                            </div>
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

                <!-- Display Format Selection -->
                <div class="mt-6">
                    <h4 class="text-md font-medium text-gray-900 mb-4">Choose Display Format:</h4>
                    <div class="flex space-x-4 mb-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" wire:model.live="displayFormat" value="reel_cuts" class="mr-2">
                            <span class="text-sm text-gray-700">Reel and Cuts</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" wire:model.live="displayFormat" value="dimensions" class="mr-2">
                            <span class="text-sm text-gray-700">Dimensions</span>
                        </label>
                    </div>

                    <!-- Format indicator -->
                    <div class="mb-4 p-2 bg-blue-50 border border-blue-200 rounded text-sm text-blue-700">
                        <strong>Current Format:</strong> {{ $displayFormat === 'reel_cuts' ? 'Reel and Cuts' : 'Dimensions' }}
                    </div>
                </div>

                <!-- Print Content (Hidden, shown only when printing) -->
                <div id="purchaseOrderPrintContent" class="hidden print:block print:p-6" style="display: none;">
                    <!-- Print Header -->
                    <div class="text-center mb-6 border-b-2 border-gray-800 pb-4">
                        <h2 class="text-3xl font-bold text-gray-900">PURCHASE ORDER</h2>
                        <p class="text-lg text-gray-700 mt-2">PO Number: {{ $selectedPurchaseOrder->po_number }}</p>
                        <p class="text-sm text-gray-600">Date: {{ \App\Helpers\DateFormatHelper::format($selectedPurchaseOrder->date) }}</p>
                    </div>

                    <!-- Print Details -->
                    <div class="grid grid-cols-2 gap-6 mb-6">
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Supplier Information</h4>
                            <p class="text-sm text-gray-700"><strong>Supplier:</strong> {{ $selectedPurchaseOrder->supplier->name ?? 'N/A' }} ({{ $selectedPurchaseOrder->supplier->code ?? 'N/A' }})</p>
                            <p class="text-sm text-gray-700"><strong>Status:</strong> {{ ucfirst($selectedPurchaseOrder->status) }}</p>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 mb-2">Order Information</h4>
                            @php
                                $jobOrders = $selectedPurchaseOrder->jobOrders();
                            @endphp
                            <p class="text-sm text-gray-700">
                                <strong>Job Order{{ $jobOrders->count() > 1 ? 's' : '' }}:</strong> 
                                @if($jobOrders->count() > 0)
                                    {{ $jobOrders->map(function($jo) { return $jo->supplier_po_number ?? $jo->job_order_number ?? $jo->job_number ?? 'N/A'; })->implode(', ') }}
                                @else
                                    N/A
                                @endif
                            </p>
                            <p class="text-sm text-gray-700"><strong>Total Amount:</strong> Rs. {{ number_format($selectedPurchaseOrder->getTotalAmount(), 2) }}</p>
                        </div>
                    </div>

                    <!-- Print Items Table -->
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-3">Purchase Order Items</h4>
                        <table class="min-w-full border border-gray-300" style="border-collapse: collapse;">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Item Type</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Job Order</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Description</th>
                                    @if($displayFormat === 'reel_cuts')
                                    <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Reel Size</th>
                                    <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Cut Size</th>
                                    @else
                                    <th class="border border-gray-300 px-4 py-2 text-left text-xs font-medium text-gray-700 uppercase">Dimensions</th>
                                    @endif
                                    <th class="border border-gray-300 px-4 py-2 text-right text-xs font-medium text-gray-700 uppercase">Quantity</th>
                                    <th class="border border-gray-300 px-4 py-2 text-right text-xs font-medium text-gray-700 uppercase">Unit Price</th>
                                    <th class="border border-gray-300 px-4 py-2 text-right text-xs font-medium text-gray-700 uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($selectedPurchaseOrder->items as $item)
                                <tr>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">{{ ucfirst($item->item_type) }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">
                                        @if($item->jobOrder)
                                            {{ $item->jobOrder->supplier_po_number ?? $item->jobOrder->job_order_number ?? $item->jobOrder->job_number ?? 'N/A' }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">{{ $item->description }}</td>
                                    @if($displayFormat === 'reel_cuts')
                                    @php
                                        $reelSize = $item->reel_size;
                                        $cutSize = $item->cut_size;
                                        if (($reelSize === null || $cutSize === null || $reelSize == 0 || $cutSize == 0) && $item->item_type === 'box') {
                                            $box = $item->getItem();
                                            if ($box) {
                                                // Use stored values if available, otherwise calculate
                                                if (($box->reel_size && $box->reel_size > 0)) {
                                                    $reelSize = $box->reel_size;
                                                } else {
                                                    try {
                                                        $supplierId = $selectedPurchaseOrder->supplier_id ?? null;
                                                        $reelSize = $box->calculateReelSize($supplierId);
                                                    } catch (\Exception $e) {
                                                        $reelSize = 0;
                                                    }
                                                }
                                                
                                                if (($box->cut_size && $box->cut_size > 0)) {
                                                    $cutSize = $box->cut_size;
                                                } else {
                                                    try {
                                                        $cutSize = $box->calculateCutSize();
                                                    } catch (\Exception $e) {
                                                        $cutSize = 0;
                                                    }
                                                }
                                            }
                                        }
                                    @endphp
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">{{ $reelSize && $reelSize > 0 ? number_format($reelSize, 2) . '"' : '-' }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">{{ $cutSize && $cutSize > 0 ? number_format($cutSize, 2) . '"' : '-' }}</td>
                                    @else
                                    @php
                                        $dimensions = '-';
                                        if ($item->item_type === 'box') {
                                            // Use getItem() method to get the box directly
                                            $box = $item->getItem();
                                            if ($box) {
                                                $dimensions = number_format($box->length, 2) . ' x ' . number_format($box->width, 2) . ' x ' . number_format($box->height, 2) . ' ' . ($box->unit ?? 'CM');
                                            }
                                        }
                                    @endphp
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900">{{ $dimensions }}</td>
                                    @endif
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900 text-right">{{ number_format($item->quantity) }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900 text-right">Rs. {{ number_format($item->unit_price, 2) }}</td>
                                    <td class="border border-gray-300 px-4 py-2 text-sm text-gray-900 text-right">Rs. {{ number_format($item->total_price, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="{{ $displayFormat === 'reel_cuts' ? 6 : 5 }}" class="border border-gray-300 px-4 py-2 text-right font-semibold text-gray-900">Total Amount:</td>
                                    <td class="border border-gray-300 px-4 py-2 text-right font-semibold text-gray-900">Rs. {{ number_format($selectedPurchaseOrder->getTotalAmount(), 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    @if($selectedPurchaseOrder->notes)
                    <div class="mt-6">
                        <h4 class="font-semibold text-gray-900 mb-2">Notes:</h4>
                        <p class="text-sm text-gray-700">{{ $selectedPurchaseOrder->notes }}</p>
                    </div>
                    @endif
                </div>

                <!-- Modal Body -->
                <div class="mt-6 space-y-6">
                    <!-- PO Header Information -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Supplier: <span class="text-xs font-normal text-gray-900">{{ $selectedPurchaseOrder->supplier->name ?? 'N/A' }}</span>
                                    @if($selectedPurchaseOrder->supplier->code ?? '')
                                        <span class="text-xs text-gray-500">({{ $selectedPurchaseOrder->supplier->code }})</span>
                                    @endif
                                </label>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Supplier Address: <span class="text-xs font-normal text-gray-900">{{ $selectedPurchaseOrder->supplier->address ?? 'N/A' }}</span></label>
                            </div>
                            <div>
                                @php
                                    $jobOrders = $selectedPurchaseOrder->jobOrders();
                                @endphp
                                <label class="block text-sm font-bold text-gray-700">
                                    @if($jobOrders->count() == 1)
                                        Job Order: <span class="text-xs font-normal text-gray-900">{{ $jobOrders->first()->supplier_po_number ?? $jobOrders->first()->job_order_number ?? $jobOrders->first()->job_number ?? 'N/A' }}</span>
                                        @if($jobOrders->first()->job_number ?? '')
                                            <span class="text-xs text-gray-500">({{ $jobOrders->first()->job_number }})</span>
                                        @endif
                                    @elseif($jobOrders->count() > 1)
                                        Job Orders ({{ $jobOrders->count() }}):
                                        <div class="mt-1 space-y-1">
                                            @foreach($jobOrders as $jo)
                                                <div class="text-xs font-normal text-gray-900">
                                                    • {{ $jo->supplier_po_number ?? $jo->job_order_number ?? $jo->job_number ?? 'N/A' }}
                                                    @if($jo->job_number ?? '')
                                                        <span class="text-gray-500">({{ $jo->job_number }})</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        Job Order: <span class="text-xs font-normal text-gray-900">N/A</span>
                                    @endif
                                </label>
                            </div>
                            <div class="flex gap-1">
                                <label class="block text-sm font-bold text-gray-700">Status:</label>
                                <div>
                                    @php
                                    $statusColors = [
                                    'draft' => 'bg-yellow-100 text-yellow-800',
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
                        <div class="space-y-4 text-right">
                            <div>
                                <label class="block text-sm font-bold text-gray-700">PO Number: <span class="text-xs font-normal text-gray-900">{{ $selectedPurchaseOrder->po_number }}</span></label>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Date: <span class="text-xs font-normal text-gray-900">{{ \App\Helpers\DateFormatHelper::format($selectedPurchaseOrder->date) }}</span></label>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Total Amount: <span class="text-lg font-bold text-gray-900">Rs. {{ number_format($selectedPurchaseOrder->getTotalAmount(), 2) }}</span></label>
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
                                            Job Order</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Description</th>
                                        @if($displayFormat === 'reel_cuts')
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Reel Size</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Cut Size</th>
                                        @else
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Length</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Width</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Height</th>
                                        @endif
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Quantity</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Unit Price</th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Total</th>
                                        @if($selectedPurchaseOrder && $selectedPurchaseOrder->status === 'draft')
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions</th>
                                        @endif
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($item->jobOrder)
                                                {{ $item->jobOrder->supplier_po_number ?? $item->jobOrder->job_order_number ?? $item->jobOrder->job_number ?? 'N/A' }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">{{ $item->description }}</td>
                                        @if($displayFormat === 'reel_cuts')
                                        @php
                                            // Get reel_size and cut_size from item
                                            $reelSize = $item->reel_size;
                                            $cutSize = $item->cut_size;

                                            // If values are not stored, try to get from the related box using getItem() method
                                            if (($reelSize === null || $cutSize === null || $reelSize == 0 || $cutSize == 0) && $item->item_type === 'box') {
                                                $box = $item->getItem();
                                                if ($box) {
                                                    // Use stored values if available, otherwise calculate
                                                    if (($box->reel_size && $box->reel_size > 0)) {
                                                        $reelSize = $box->reel_size;
                                                    } else {
                                                        try {
                                                            $supplierId = $selectedPurchaseOrder->supplier_id ?? null;
                                                            $reelSize = $box->calculateReelSize($supplierId);
                                                        } catch (\Exception $e) {
                                                            $reelSize = 0;
                                                        }
                                                    }
                                                    
                                                    if (($box->cut_size && $box->cut_size > 0)) {
                                                        $cutSize = $box->cut_size;
                                                    } else {
                                                        try {
                                                            $cutSize = $box->calculateCutSize();
                                                        } catch (\Exception $e) {
                                                            $cutSize = 0;
                                                        }
                                                    }
                                                }
                                            }
                                        @endphp
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $reelSize && $reelSize > 0 ? number_format($reelSize, 2) . '"' : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $cutSize && $cutSize > 0 ? number_format($cutSize, 2) . '"' : '-' }}
                                        </td>
                                        @else
                                        @php
                                            // Try to extract dimensions from description for boxes
                                            $length = '-';
                                            $width = '-';
                                            $height = '-';

                                            if ($item->item_type === 'box') {
                                                // Use getItem() method to get the box directly
                                                $box = $item->getItem();
                                                if ($box) {
                                                    $length = number_format($box->length, 2) . ' ' . ($box->unit ?? 'CM');
                                                    $width = number_format($box->width, 2) . ' ' . ($box->unit ?? 'CM');
                                                    $height = number_format($box->height, 2) . ' ' . ($box->unit ?? 'CM');
                                                }
                                            }
                                        @endphp
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $length }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $width }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $height }}</td>
                                        @endif
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @if($selectedPurchaseOrder->status === 'draft')
                                                <input type="number"
                                                       wire:model="itemQuantities.{{ $item->id }}"
                                                       min="1"
                                                       class="w-24 px-2 py-1 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                                            @else
                                                {{ $item->quantity }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs. {{
                                            number_format($item->unit_price, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rs. {{
                                            number_format(isset($itemQuantities[$item->id]) && $selectedPurchaseOrder->status === 'draft'
                                                ? ($item->unit_price * (int)$itemQuantities[$item->id])
                                                : $item->total_price, 2) }}</td>
                                        @if($selectedPurchaseOrder->status === 'draft')
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <button wire:click="deletePurchaseOrderItem({{ $item->id }})"
                                                    class="text-red-600 hover:text-red-900"
                                                    title="Delete Item"
                                                    onclick="return confirm('Are you sure you want to delete this item?')">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                        </td>
                                        @endif
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="{{ ($displayFormat === 'reel_cuts' ? 7 : 9) + ($selectedPurchaseOrder && $selectedPurchaseOrder->status === 'draft' ? 1 : 0) }}" class="px-6 py-12 text-center text-gray-500">
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
                    <button wire:click="printPurchaseOrder"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                        </svg>
                        Print
                    </button>
                    @if($selectedPurchaseOrder && $selectedPurchaseOrder->status === 'draft')
                    <button wire:click="savePurchaseOrder"
                        class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md transition-colors">
                        Save
                    </button>
                    @endif
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
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Supplier : <span class="text-xs font-normal text-gray-900">{{ $selectedPurchaseOrder->supplier->name ?? 'N/A' }}</span>
                                    @if($selectedPurchaseOrder->supplier->code ?? '')
                                        <span class="text-xs text-gray-500">({{ $selectedPurchaseOrder->supplier->code }})</span>
                                    @endif
                                </label>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Supplier Address : <span class="text-xs font-normal text-gray-900">{{ $selectedPurchaseOrder->supplier->address ?? 'N/A' }}</span></label>
                            </div>
                            <div>
                                @php
                                    $jobOrders = $selectedPurchaseOrder->jobOrders();
                                @endphp
                                <label class="block text-sm font-bold text-gray-700">
                                    @if($jobOrders->count() == 1)
                                        Job Order: <span class="text-xs font-normal text-gray-900">{{ $jobOrders->first()->supplier_po_number ?? $jobOrders->first()->job_order_number ?? $jobOrders->first()->job_number ?? 'N/A' }}</span>
                                        @if($jobOrders->first()->job_number ?? '')
                                            <span class="text-xs text-gray-500">({{ $jobOrders->first()->job_number }})</span>
                                        @endif
                                    @elseif($jobOrders->count() > 1)
                                        Job Orders ({{ $jobOrders->count() }}):
                                        <div class="mt-1 space-y-1">
                                            @foreach($jobOrders as $jo)
                                                <div class="text-xs font-normal text-gray-900">
                                                    • {{ $jo->supplier_po_number ?? $jo->job_order_number ?? $jo->job_number ?? 'N/A' }}
                                                    @if($jo->job_number ?? '')
                                                        <span class="text-gray-500">({{ $jo->job_number }})</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        Job Order: <span class="text-xs font-normal text-gray-900">N/A</span>
                                    @endif
                                </label>
                            </div>
                            <div class="flex gap-1">
                                <label class="block text-sm font-bold text-gray-700">Status :</label>
                                <div>
                                    @php
                                    $statusColors = [
                                    'draft' => 'bg-yellow-100 text-yellow-800',
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
                        <div class="space-y-4 text-right">
                            <div>
                                <label class="block text-sm font-bold text-gray-700">PO Number: <span class="text-xs font-normal text-gray-900">{{ $selectedPurchaseOrder->po_number }}</span></label>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Date: <span class="text-xs font-normal text-gray-900">{{ \App\Helpers\DateFormatHelper::format($selectedPurchaseOrder->date) }}</span></label>
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-gray-700">Total Amount: <span class="text-lg font-bold text-gray-900">Rs. {{ number_format($selectedPurchaseOrder->getTotalAmount(), 2) }}</span></label>
                            </div>
                        </div>
                    </div>

                    <!-- Info Box -->
                    <div class="bg-yellow-50 p-2 rounded-lg border border-yellow-400">
                        <div class="flex items-start gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5 text-yellow-600 flex-shrink-0 mt-0.5">
                                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                            </svg>
                            <div class="flex-1">
                                <h4 class="font-medium text-yellow-900 mb-2">Purchase Order : {{
                                    $selectedPurchaseOrder->po_number }}</h4>
                                <p class="text-sm text-yellow-700">Update supplier prices and confirm this Purchase Order after
                                    confirmation.</p>
                            </div>
                        </div>
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
                                                wire:model.live="phoneConfirmForm.item_{{ $item->id }}_unit_price"
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
                        <label class="block text-sm font-medium text-gray-700">Confirmation Notes</label>
                        <textarea wire:model="phoneConfirmForm.notes" rows="3"
                            class="border border-gray-400 mt-1 block w-full p-2  rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Any notes from the conversation..."></textarea>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t mt-6">
                    <button wire:click="closePhoneConfirmModal"
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



    <!-- GRN Confirmation Modal -->
    @if($showGRNConfirmModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-orange-100 rounded-full mb-4">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>

                    <h3 class="text-lg font-medium text-gray-900 text-center mb-2">
                        Create GRN from Purchase Order
                    </h3>

                    <div class="text-center text-sm text-gray-600 mb-6">
                        <p>Are you sure you want to create a GRN from:</p>
                        <p class="font-semibold text-gray-900 mt-1">
                            {{ $selectedPurchaseOrder->po_number ?? '' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-2">
                            This will create a Goods Received Note with {{ $selectedPurchaseOrder->items->count() ?? 0 }} items.
                        </p>
                    </div>

                    <div class="flex space-x-3">
                        <button wire:click="closeGRNConfirmModal"
                                class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md transition-colors">
                            Cancel
                        </button>
                        <button wire:click="createGRNFromPurchaseOrder({{ $selectedPurchaseOrder->id ?? '' }})"
                                class="flex-1 bg-orange-600 hover:bg-orange-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                            Create GRN
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Cancel Purchase Order Confirmation Modal -->
    @if($showCancelConfirmModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>

                    <h3 class="text-lg font-medium text-gray-900 text-center mb-2">
                        Cancel Purchase Order
                    </h3>

                    <div class="text-center text-sm text-gray-600 mb-6">
                        <p>Are you sure you want to cancel:</p>
                        <p class="font-semibold text-gray-900 mt-1">
                            {{ $selectedPurchaseOrder->po_number ?? '' }}
                        </p>
                        <div class="mt-3 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                            <p class="text-xs text-yellow-800 font-medium">⚠️ Important:</p>
                            <p class="text-xs text-yellow-700 mt-1">
                                @if($selectedPurchaseOrder && $selectedPurchaseOrder->grn->isNotEmpty())
                                    This will cancel the GRN and reverse all received quantities from inventory.
                                @else
                                    This will cancel the purchase order and cannot be undone.
                                @endif
                            </p>
                        </div>

                        <div class="mt-4">
                            <label for="cancellation_reason" class="block text-sm font-medium text-gray-700 mb-2">
                                Cancellation Reason (Optional)
                            </label>
                            <textarea wire:model="cancellationReason"
                                    id="cancellation_reason"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm"
                                    placeholder="Enter reason for cancellation..."></textarea>
                        </div>
                    </div>

                    <div class="flex space-x-3">
                        <button wire:click="closeCancelConfirmModal"
                                class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-md transition-colors">
                            Keep Order
                        </button>
                        <button wire:click="cancelPurchaseOrder({{ $selectedPurchaseOrder->id ?? '' }})"
                                class="flex-1 bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                            Cancel Order
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

        // Print Purchase Order functionality
        window.printPurchaseOrder = function() {
            var printContent = document.getElementById('purchaseOrderPrintContent');
            if (!printContent) {
                console.error('Print content not found');
                return;
            }

            // Show print content and hide modal
            printContent.style.display = 'block';

            // Create a new window for printing
            var printWindow = window.open('', '_blank', 'width=800,height=600');
            printWindow.document.write('<html><head><title>Purchase Order - {{ $selectedPurchaseOrder->po_number ?? "" }}</title>');
            printWindow.document.write('<style>');
            printWindow.document.write('body{font-family:Arial,sans-serif;margin:20px;padding:20px;}');
            printWindow.document.write('table{border-collapse:collapse;width:100%;margin-top:20px;}');
            printWindow.document.write('th,td{border:1px solid #000;padding:8px;text-align:left;}');
            printWindow.document.write('th{background-color:#f3f4f6;font-weight:bold;}');
            printWindow.document.write('.text-center{text-align:center;}');
            printWindow.document.write('.text-right{text-align:right;}');
            printWindow.document.write('@media print { @page { margin: 0.5cm; } body { margin: 0; } }');
            printWindow.document.write('</style>');
            printWindow.document.write('</head><body>');
            printWindow.document.write(printContent.innerHTML);
            printWindow.document.write('</body></html>');
            printWindow.document.close();

            // Wait for content to load then print
            setTimeout(function() {
                printWindow.print();
                printWindow.close();
                printContent.style.display = 'none';
            }, 500);
        };
    </script>
    @endscript

    <!-- Filter Modal -->
    @if($showFilterModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click.self="closeFilterModal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <!-- Modal Header -->
                    <div class="flex justify-between items-center pb-4 border-b">
                        <h3 class="text-lg font-medium text-gray-900">
                            Filter Purchase Orders
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
                                    <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">Status</label>
                                    <div class="flex-1">
                                        <select wire:model.live="filterStatus"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">All Status</option>
                                            <option value="draft">Draft</option>
                                            <option value="confirmed">Confirmed</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Row 3: GRN Status -->
                            <div class="flex gap-3">
                                <div class="flex items-center gap-3 flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1 w-1/5">GRN Status</label>
                                    <div class="flex-1">
                                        <select wire:model.live="filterGRNStatus"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">All GRN Status</option>
                                            <option value="no_grn">No GRN</option>
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

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</div>
