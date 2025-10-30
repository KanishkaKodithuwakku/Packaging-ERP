<div>
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Customer Orders</h2>
                <button wire:click="create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Add New Order
                </button>
            </div>
        </div>

        <div class="p-6">
            <!-- Orders Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quotation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Value</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($customerOrders as $order)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $order->order_no }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($order->quotation)
                                        <span class="text-blue-600 font-medium">{{ $order->quotation->qt_no }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->customer ? $order->customer->name : 'No Customer' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    <div class="max-w-xs">
                                        @if($order->orderItems->count() > 0)
                                            <div class="space-y-1">
                                                @foreach($order->orderItems as $item)
                                                        <div class="text-xs bg-gray-100 px-2 py-1 rounded">
                                                            <div class="font-medium">{{ $item->item_description }}</div>
                                                            <div class="text-gray-600">{{ $item->length_mm }}×{{ $item->width_mm }}×{{ $item->height_mm }}mm, {{ $item->ply }}ply, Qty: {{ $item->qty_ordered }}</div>
                                                        </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-400">No items</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->total_quantity }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($order->total_value > 0)
                                        ${{ number_format($order->total_value, 2) }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($order->status == 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($order->status == 'confirmed') bg-blue-100 text-blue-800
                                        @elseif($order->status == 'in_production') bg-orange-100 text-orange-800
                                        @elseif($order->status == 'completed') bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button wire:click="edit({{ $order->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                    @if($order->orderItems->count() == 0)
                                        <button wire:click="addItems({{ $order->id }})" class="text-green-600 hover:text-green-900 mr-3">Add Items</button>
                                    @else
                                        <button wire:click="addItems({{ $order->id }})" class="text-blue-600 hover:text-blue-900 mr-3">Manage Items</button>
                                    @endif
                                    @if($order->status != 'delivered')
                                        <a href="{{ route('job-orders') }}?create_from={{ $order->id }}" class="text-green-600 hover:text-green-900 mr-3">Create Job Order</a>
                                        <a href="{{ route('create-delivery-note') }}" class="text-purple-600 hover:text-purple-900 mr-3">Create Delivery Note</a>
                                    @endif
                                    <button wire:click="delete({{ $order->id }})" class="text-red-600 hover:text-red-900" 
                                            onclick="return confirm('Are you sure you want to delete this order?')">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $customerOrders->links() }}
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-10 mx-auto p-5 border w-11/12 max-w-6xl shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        {{ $editing ? 'Edit Customer Order' : 'Create New Customer Order' }}
                    </h3>
                    
                    <form wire:submit.prevent="save">
                        <!-- Order Details -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Customer</label>
                                <select wire:model="customer_id" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                                @error('customer_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Order Number</label>
                                <input type="text" wire:model="order_no" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                @error('order_no') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select wire:model="status" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="in_production">In Production</option>
                                    <option value="completed">Completed</option>
                                    <option value="delivered">Delivered</option>
                                </select>
                                @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea wire:model="notes" rows="2" class="w-full border border-gray-300 rounded-md px-3 py-2"></textarea>
                        </div>

                        @if(!$editing)
                            <div class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-blue-800">Two-Step Process</h3>
                                        <div class="mt-2 text-sm text-blue-700">
                                            <p>Step 1: Create the customer order with basic details</p>
                                            <p>Step 2: Add items to the order after creation</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

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

    <!-- Items Modal -->
    @if($showItemsModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-10 mx-auto p-5 border w-11/12 max-w-6xl shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        Manage Items for Order: {{ $order_no }}
                    </h3>
                    
                    <form wire:submit.prevent="saveItems">
                        <!-- Order Items Section -->
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="text-lg font-medium text-gray-900">Order Items</h4>
                                <button type="button" wire:click="addItem" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    Add Item
                                </button>
                            </div>

                            @error('orderItems') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            <div class="space-y-4">
                                @foreach($orderItems as $index => $item)
                                    <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                        <div class="flex justify-between items-center mb-3">
                                            <h5 class="font-medium text-gray-900">Item {{ $index + 1 }}</h5>
                                            @if(count($orderItems) > 1)
                                                <button type="button" wire:click="removeItem({{ $index }})" class="text-red-600 hover:text-red-900">
                                                    Remove
                                                </button>
                                            @endif
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                            <div class="md:col-span-2">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Item Description</label>
                                                <input type="text" wire:model="orderItems.{{ $index }}.item_description" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                                @error('orderItems.'.$index.'.item_description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Length (mm)</label>
                                                <input type="number" wire:model="orderItems.{{ $index }}.length_mm" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="e.g., 600">
                                                @error('orderItems.'.$index.'.length_mm') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Width (mm)</label>
                                                <input type="number" wire:model="orderItems.{{ $index }}.width_mm" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="e.g., 400">
                                                @error('orderItems.'.$index.'.width_mm') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Height (mm)</label>
                                                <input type="number" wire:model="orderItems.{{ $index }}.height_mm" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="e.g., 300">
                                                @error('orderItems.'.$index.'.height_mm') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Ply</label>
                                                <input type="number" wire:model="orderItems.{{ $index }}.ply" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                                @error('orderItems.'.$index.'.ply') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Flute Type</label>
                                                <input type="text" wire:model="orderItems.{{ $index }}.flute_type" class="w-full border border-gray-300 rounded-md px-3 py-2" placeholder="e.g., B, C, E">
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                                <input type="number" step="0.01" wire:model="orderItems.{{ $index }}.qty_ordered" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                                @error('orderItems.'.$index.'.qty_ordered') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price</label>
                                                <input type="number" step="0.01" wire:model="orderItems.{{ $index }}.unit_price" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                                @error('orderItems.'.$index.'.unit_price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                            </div>
                                        </div>

                                        <!-- GSM Layers -->
                                        <div class="mt-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">GSM Layers</label>
                                            <div class="space-y-2">
                                                @if(isset($item['gsm_layers']) && is_array($item['gsm_layers']))
                                                    @foreach($item['gsm_layers'] as $layerIndex => $gsm)
                                                        <div class="flex items-center space-x-2">
                                                            <input type="number" wire:model="orderItems.{{ $index }}.gsm_layers.{{ $layerIndex }}" class="w-24 border border-gray-300 rounded-md px-3 py-2">
                                                            <span class="text-sm text-gray-600">GSM</span>
                                                            @if(count($item['gsm_layers']) > 1)
                                                                <button type="button" wire:click="removeGsmLayer({{ $index }}, {{ $layerIndex }})" class="text-red-600 hover:text-red-900">Remove</button>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                @endif
                                                <button type="button" wire:click="addGsmLayer({{ $index }})" class="text-blue-600 hover:text-blue-900 text-sm">+ Add GSM Layer</button>
                                            </div>
                                        </div>

                                        <div class="mt-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                            <textarea wire:model="orderItems.{{ $index }}.notes" rows="2" class="w-full border border-gray-300 rounded-md px-3 py-2"></textarea>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button type="button" wire:click="closeItemsModal" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Cancel
                            </button>
                            <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                Save Items
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>