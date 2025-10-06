<div>
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Quotations</h2>
                <button wire:click="create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Add New Quotation
                </button>
            </div>
        </div>

        <div class="p-6">
            <!-- Quotations Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">QT No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size (mm)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ply</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Selling Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($quotations as $quotation)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $quotation->qt_no }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $quotation->customer ? $quotation->customer->name : 'No Customer' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $quotation->item_desc }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $quotation->size_mm }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $quotation->ply }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $quotation->qty_requested }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ number_format($quotation->selling_price, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        @if($quotation->status == 'draft') bg-gray-100 text-gray-800
                                        @elseif($quotation->status == 'sent') bg-blue-100 text-blue-800
                                        @elseif($quotation->status == 'accepted') bg-green-100 text-green-800
                                        @elseif($quotation->status == 'rejected') bg-red-100 text-red-800
                                        @elseif($quotation->status == 'expired') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($quotation->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button wire:click="edit({{ $quotation->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                    @if($quotation->status == 'draft')
                                        <button wire:click="send({{ $quotation->id }})" class="text-blue-600 hover:text-blue-900 mr-3">Send</button>
                                    @elseif($quotation->status == 'sent')
                                        <button wire:click="accept({{ $quotation->id }})" class="text-green-600 hover:text-green-900 mr-3">Accept</button>
                                        <button wire:click="reject({{ $quotation->id }})" class="text-red-600 hover:text-red-900 mr-3">Reject</button>
                                    @endif
                                    <button wire:click="delete({{ $quotation->id }})" class="text-red-600 hover:text-red-900" 
                                            onclick="return confirm('Are you sure you want to delete this quotation?')">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $quotations->links() }}
            </div>
        </div>
    </div>

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-4/5 max-w-4xl shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        {{ $editing ? 'Edit Quotation' : 'Create New Quotation' }}
                    </h3>
                    
                    <form wire:submit.prevent="save">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
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
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quotation Number</label>
                                <input type="text" wire:model="qt_no" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                @error('qt_no') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Item Description</label>
                            <input type="text" wire:model="item_desc" class="w-full border border-gray-300 rounded-md px-3 py-2">
                            @error('item_desc') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
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

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Flute Type</label>
                                <select wire:model="flute_type" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                    <option value="">Select Flute Type</option>
                                    <option value="A">A Flute</option>
                                    <option value="B">B Flute</option>
                                    <option value="C">C Flute</option>
                                    <option value="E">E Flute</option>
                                    <option value="F">F Flute</option>
                                </select>
                                @error('flute_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- GSM Layers -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">GSM Layers</label>
                            <div class="space-y-2">
                                @foreach($gsm_layers as $index => $gsm)
                                    <div class="flex items-center space-x-2">
                                        <input type="number" wire:model="gsm_layers.{{ $index }}" class="w-24 border border-gray-300 rounded-md px-3 py-2">
                                        <span class="text-sm text-gray-600">GSM</span>
                                        @if(count($gsm_layers) > 1)
                                            <button type="button" wire:click="removeGsmLayer({{ $index }})" class="text-red-600 hover:text-red-900">Remove</button>
                                        @endif
                                    </div>
                                @endforeach
                                <button type="button" wire:click="addGsmLayer" class="text-blue-600 hover:text-blue-900 text-sm">+ Add GSM Layer</button>
                            </div>
                            @error('gsm_layers') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity Requested</label>
                                <input type="number" step="0.01" wire:model="qty_requested" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                @error('qty_requested') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Profit Margin (%)</label>
                                <input type="number" step="0.01" wire:model="profit_margin" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                @error('profit_margin') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Valid Until</label>
                                <input type="date" wire:model="valid_until" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                @error('valid_until') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select wire:model="status" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                    <option value="draft">Draft</option>
                                    <option value="sent">Sent</option>
                                    <option value="accepted">Accepted</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="expired">Expired</option>
                                </select>
                                @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                            <textarea wire:model="notes" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-2"></textarea>
                            @error('notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
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
