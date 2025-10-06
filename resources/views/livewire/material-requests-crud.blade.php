<div>
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Material Requests</h2>
                <button wire:click="create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Add New Material Request
                </button>
            </div>
        </div>

        <div class="p-6">
            <!-- Material Requests Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">MR No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Order</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Material Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Requested</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($materialRequests as $request)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $request->mr_no }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $request->jobOrder->jo_no }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $request->jobOrder->customerOrder->customer->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $request->material_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $request->qty_requested }} {{ $request->uom }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($request->status == 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($request->status == 'approved') bg-blue-100 text-blue-800
                                        @elseif($request->status == 'issued') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button wire:click="edit({{ $request->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                    @if($request->status == 'pending')
                                        <button wire:click="approve({{ $request->id }})" class="text-blue-600 hover:text-blue-900 mr-3">Approve</button>
                                    @elseif($request->status == 'approved')
                                        <button wire:click="issue({{ $request->id }})" class="text-green-600 hover:text-green-900 mr-3">Issue</button>
                                    @endif
                                    <button wire:click="delete({{ $request->id }})" class="text-red-600 hover:text-red-900" 
                                            onclick="return confirm('Are you sure you want to delete this material request?')">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $materialRequests->links() }}
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        {{ $editing ? 'Edit Material Request' : 'Create New Material Request' }}
                    </h3>
                    
                    <form wire:submit.prevent="save">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Job Order</label>
                            <select wire:model="job_order_id" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                <option value="">Select Job Order</option>
                                @foreach($jobOrders as $jobOrder)
                                    <option value="{{ $jobOrder->id }}">
                                        {{ $jobOrder->jo_no }} - {{ $jobOrder->customerOrder->customer->name }} ({{ $jobOrder->item_desc }})
                                    </option>
                                @endforeach
                            </select>
                            @error('job_order_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">MR Number</label>
                            <input type="text" wire:model="mr_no" class="w-full border border-gray-300 rounded-md px-3 py-2">
                            @error('mr_no') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Material Code</label>
                            <select wire:model="material_code" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                <option value="">Select Material</option>
                                @foreach($availableMaterials as $material)
                                    <option value="{{ $material }}">{{ $material }}</option>
                                @endforeach
                            </select>
                            @error('material_code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity Requested</label>
                                <input type="number" step="0.01" wire:model="qty_requested" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                @error('qty_requested') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">UOM</label>
                                <select wire:model="uom" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                    <option value="KG">KG</option>
                                    <option value="PCS">PCS</option>
                                    <option value="MTR">MTR</option>
                                    <option value="ROLL">ROLL</option>
                                </select>
                                @error('uom') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select wire:model="status" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="issued">Issued</option>
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
