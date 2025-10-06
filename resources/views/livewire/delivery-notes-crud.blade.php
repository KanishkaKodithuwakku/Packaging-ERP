<div>
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Delivery Notes</h2>
                <button wire:click="create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Add New Delivery Note
                </button>
            </div>
        </div>

        <div class="p-6">
            <!-- Delivery Notes Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DN No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">FG Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Delivered</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delivery Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($deliveryNotes as $deliveryNote)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $deliveryNote->dn_no }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $deliveryNote->customerOrder->customer->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $deliveryNote->customerOrder->order_no }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $deliveryNote->fg_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $deliveryNote->qty_delivered }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $deliveryNote->delivery_date->format('Y-m-d') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button wire:click="edit({{ $deliveryNote->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                    <a href="{{ route('delivery-notes.print', $deliveryNote->id) }}" target="_blank" class="text-green-600 hover:text-green-900 mr-3">🖨️ Print</a>
                                    <button wire:click="delete({{ $deliveryNote->id }})" class="text-red-600 hover:text-red-900" 
                                            onclick="return confirm('Are you sure you want to delete this delivery note?')">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $deliveryNotes->links() }}
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        {{ $editing ? 'Edit Delivery Note' : 'Create New Delivery Note' }}
                    </h3>
                    
                    <form wire:submit.prevent="save">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Customer Order</label>
                            <select wire:model="customer_order_id" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                <option value="">Select Customer Order</option>
                                @foreach($customerOrders as $customerOrder)
                                    <option value="{{ $customerOrder->id }}">
                                        {{ $customerOrder->order_no }} - {{ $customerOrder->customer->name }} ({{ $customerOrder->item_desc }})
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_order_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">DN Number</label>
                            <input type="text" wire:model="dn_no" class="w-full border border-gray-300 rounded-md px-3 py-2">
                            @error('dn_no') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Finished Goods Code</label>
                            <select wire:model="fg_code" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                <option value="">Select Finished Goods</option>
                                @foreach($availableFinishedGoods as $fg)
                                    <option value="{{ $fg }}">{{ $fg }}</option>
                                @endforeach
                            </select>
                            @error('fg_code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quantity Delivered</label>
                            <input type="number" step="0.01" wire:model="qty_delivered" class="w-full border border-gray-300 rounded-md px-3 py-2">
                            @error('qty_delivered') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Date</label>
                            <input type="date" wire:model="delivery_date" class="w-full border border-gray-300 rounded-md px-3 py-2">
                            @error('delivery_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
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
</div>
