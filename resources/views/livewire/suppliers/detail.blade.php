<div>
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h1 class="text-2xl font-bold">Supplier: {{ $supplier->name }}</h1>
            <a wire:navigate href="{{ route('suppliers') }}" class="text-sm text-blue-600">← Back to Suppliers</a>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <p class="text-sm text-gray-500">Code</p>
                    <p class="font-medium">{{ $supplier->code }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Phone</p>
                    <p class="font-medium">{{ $supplier->phone }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Email</p>
                    <p class="font-medium">{{ $supplier->email }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500">Address</p>
                    <p class="font-medium">{{ $supplier->address }}</p>
                </div>
            </div>

            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold">Reel Sizes</h2>
                <button wire:click="openReelModal" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-md">Add Reel Size</button>
            </div>

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">PLY</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Flute</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Size (mm)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Default</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($supplier->reelSizes as $size)
                    <tr>
                        <td class="px-6 py-3 text-sm">{{ $size->ply }}</td>
                        <td class="px-6 py-3 text-sm">{{ $size->flute }}</td>
                        <td class="px-6 py-3 text-sm">{{ number_format($size->size_mm, 2) }}</td>
                        <td class="px-6 py-3 text-sm">@if($size->is_default) <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Default</span> @endif</td>
                        <td class="px-6 py-3 text-sm">
                            <button wire:click="openReelModal({{ $size->id }})" class="px-2 py-1 text-xs bg-gray-100 rounded hover:bg-gray-200">Edit</button>
                            <button wire:click="deleteReel({{ $size->id }})" class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200">Delete</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-6 text-center text-gray-500">No reel sizes</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($showReelModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click.self="closeReelModal">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $editingReelId ? 'Edit Reel Size' : 'Add Reel Size' }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">PLY</label>
                        <select wire:model.defer="reelForm.ply" class="mt-1 block w-full border-gray-300 rounded-md">
                            <option value="3">3</option>
                            <option value="5">5</option>
                            <option value="7">7</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Flute</label>
                        <input type="text" wire:model.defer="reelForm.flute" class="mt-1 block w-full border-gray-300 rounded-md" placeholder="B/C/E/BC/BE" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Size (mm)</label>
                        <input type="number" step="0.01" wire:model.defer="reelForm.size_mm" class="mt-1 block w-full border-gray-300 rounded-md" />
                    </div>
                    <div class="flex items-center mt-6">
                        <input type="checkbox" wire:model.defer="reelForm.is_default" class="mr-2" /> Default
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea wire:model.defer="reelForm.notes" rows="3" class="mt-1 block w-full border-gray-300 rounded-md"></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button wire:click="closeReelModal" class="px-4 py-2 border border-gray-300 rounded-md">Cancel</button>
                    <button wire:click="saveReel" class="px-4 py-2 rounded-md text-white bg-blue-600 hover:bg-blue-700">Save</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>


