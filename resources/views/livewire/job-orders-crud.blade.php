<div>
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Job Orders</h2>
                <button wire:click="create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Add New Job Order
                </button>
            </div>
        </div>

        <div class="p-6">
            <!-- Job Orders Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Order No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size (mm)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ply</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty to Make</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($jobOrders as $jobOrder)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $jobOrder->jo_no }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $jobOrder->customerOrder->customer->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $jobOrder->item_desc }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $jobOrder->size_mm }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $jobOrder->ply }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $jobOrder->qty_to_make }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($jobOrder->status == 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($jobOrder->status == 'in_progress') bg-blue-100 text-blue-800
                                        @else bg-green-100 text-green-800
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $jobOrder->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button wire:click="edit({{ $jobOrder->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                    @if($jobOrder->status != 'completed')
                                        <a href="{{ route('material-requests') }}?create_from={{ $jobOrder->id }}" class="text-purple-600 hover:text-purple-900 mr-3">Create Material Request</a>
                                    @endif
                                    @if($jobOrder->status == 'in_progress')
                                        <button wire:click="complete({{ $jobOrder->id }})" class="text-green-600 hover:text-green-900 mr-3">Complete</button>
                                    @endif
                                    <button wire:click="delete({{ $jobOrder->id }})" class="text-red-600 hover:text-red-900" 
                                            onclick="return confirm('Are you sure you want to delete this job order?')">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $jobOrders->links() }}
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        {{ $editing ? 'Edit Job Order' : 'Create New Job Order' }}
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Job Order Number</label>
                            <input type="text" wire:model="jo_no" class="w-full border border-gray-300 rounded-md px-3 py-2">
                            @error('jo_no') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Item Description</label>
                            <input type="text" wire:model="item_desc" class="w-full border border-gray-300 rounded-md px-3 py-2">
                            @error('item_desc') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Size (mm)</label>
                                <input type="number" wire:model="size_mm" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                @error('size_mm') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Ply</label>
                                <input type="number" wire:model="ply" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                @error('ply') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Quantity to Make</label>
                            <input type="number" step="0.01" wire:model="qty_to_make" class="w-full border border-gray-300 rounded-md px-3 py-2">
                            @error('qty_to_make') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select wire:model="status" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                            @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
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
