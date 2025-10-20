<div>
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Goods Receipt Notes (GRNs)</h2>
                <button wire:click="create" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Add New GRN
                </button>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Received Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($grns as $grn)
                            <tr class="hover:bg-gray-50 cursor-pointer" wire:key="grn-{{ $grn->id }}" wire:navigate href="{{ route('grn-detail', $grn->id) }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $grn->grn_no }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($grn->isFromProductionOrder())
                                        <div class="flex items-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 mr-2">
                                                Production
                                            </span>
                                            {{ $grn->productionOrder->supplier->name ?? 'N/A' }}
                                        </div>
                                    @else
                                        {{ $grn->supplierOrder->supplier->name ?? 'N/A' }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
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
                                    @else
                                        {{ $grn->supplierOrder->po_no ?? 'N/A' }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">{{ $grn->lot_code }}</td>
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
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $grn->received_date->format('Y-m-d') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button wire:click.stop="edit({{ $grn->id }})" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                    <button wire:click.stop="delete({{ $grn->id }})" class="text-red-600 hover:text-red-900" 
                                            onclick="return confirm('Are you sure you want to delete this GRN?')">Delete</button>
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Supplier Order</label>
                            <select wire:model="supplier_po_id" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                <option value="">Select Supplier Order</option>
                                @foreach($supplierOrders as $supplierOrder)
                                    <option value="{{ $supplierOrder->id }}">
                                        {{ $supplierOrder->po_no }} - {{ $supplierOrder->supplier->name }} ({{ $supplierOrder->material_code }})
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_po_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Material Code</label>
                            <input type="text" wire:model="material_code" class="w-full border border-gray-300 rounded-md px-3 py-2">
                            @error('material_code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity Received</label>
                                <input type="number" step="0.01" wire:model="qty_received" class="w-full border border-gray-300 rounded-md px-3 py-2">
                                @error('qty_received') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
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
</div>
