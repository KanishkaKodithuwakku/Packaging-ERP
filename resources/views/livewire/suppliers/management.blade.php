<div>
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h1 class="text-2xl font-bold">Suppliers</h1>
            <div class="space-x-2">
                <input type="text" wire:model.debounce.300ms="search" placeholder="Search suppliers..."
                       class="px-3 py-1.5 border rounded-md" />
                <button wire:click="openModal" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-md">Add Supplier</button>
            </div>
        </div>

        <div class="p-6">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($suppliers as $supplier)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <a wire:navigate href="{{ route('supplier-detail', $supplier->id) }}" class="text-blue-600 hover:underline">{{ $supplier->name }}</a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $supplier->code }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $supplier->phone }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $supplier->email }}</td>
                        <td class="px-6 py-4 text-sm">
                            <button wire:click="openModal({{ $supplier->id }})" class="px-2 py-1 text-xs bg-gray-100 rounded hover:bg-gray-200">Edit</button>
                            <button wire:click="delete({{ $supplier->id }})" class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200">Delete</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4">{{ $suppliers->links() }}</div>
        </div>
    </div>

    @if($showModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click.self="closeModal">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $editingId ? 'Edit Supplier' : 'Add Supplier' }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" wire:model.defer="form.name" class="mt-1 block w-full border-gray-300 rounded-md" />
                        @error('form.name')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Code</label>
                        <input type="text" wire:model.defer="form.code" class="mt-1 block w-full border-gray-300 rounded-md" />
                        @error('form.code')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="text" wire:model.defer="form.phone" class="mt-1 block w-full border-gray-300 rounded-md" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" wire:model.defer="form.email" class="mt-1 block w-full border-gray-300 rounded-md" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea wire:model.defer="form.address" rows="3" class="mt-1 block w-full border-gray-300 rounded-md"></textarea>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 pt-4">
                    <button wire:click="closeModal" class="px-4 py-2 border border-gray-300 rounded-md">Cancel</button>
                    <button wire:click="save" class="px-4 py-2 rounded-md text-white bg-blue-600 hover:bg-blue-700">Save</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>


