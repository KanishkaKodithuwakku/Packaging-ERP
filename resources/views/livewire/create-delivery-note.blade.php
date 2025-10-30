<div>
    <div class="mb-6">
        <a wire:navigate href="{{ route('delivery-notes-management') }}"
            class="inline-flex items-center text-gray-600 hover:text-gray-900">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Delivery Notes
        </a>
        <h1 class="text-3xl font-bold text-gray-900 mt-4">Create Delivery Note</h1>
    </div>

    <div class="bg-white rounded-lg shadow-sm border p-6">
        <form wire:submit.prevent="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Job Order *</label>
                    <select wire:model.live="form.job_order_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select Job Order</option>
                        @foreach($jobOrders as $jobOrder)
                            <option value="{{ $jobOrder->id }}">{{ $jobOrder->job_number }} - {{ $jobOrder->customer->name }}</option>
                        @endforeach
                    </select>
                    @error('form.job_order_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dispatch Date *</label>
                    <input type="date" wire:model="form.dispatch_date"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    @error('form.dispatch_date') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
                    <textarea wire:model="form.delivery_address" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea wire:model="form.notes" rows="3"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"></textarea>
                </div>
            </div>

            @if($selectedJobOrder)
                <div class="mt-6 border-t pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Available Finished Goods</h3>
                    @if(count($availableFg) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Material Code</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Available Qty</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty to Dispatch</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($availableFg as $index => $fg)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $fg['description'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $fg['material_code'] }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($fg['available_qty'], 2) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="number" wire:model="dispatchQuantities.{{ $index }}"
                                                    step="0.01" min="0.01" max="{{ $fg['available_qty'] }}"
                                                    value="{{ $fg['available_qty'] }}"
                                                    class="w-32 px-2 py-1 border border-gray-300 rounded-md text-sm">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
                            <p class="text-yellow-800">No Finished Goods available for this job order.</p>
                        </div>
                    @endif
                </div>
            @endif

            <div class="mt-6 flex justify-end">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                    Create Delivery Note
                </button>
            </div>
        </form>
    </div>
</div>
