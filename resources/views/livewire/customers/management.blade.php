<div>
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h1 class="text-2xl font-bold">Customers</h1>
            <div class="space-x-2">
                <input type="text" wire:model.debounce.300ms="search" placeholder="Search customers..."
                       class="px-3 py-1.5 border rounded-md" />
                <button wire:click="openModal" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-md">Add Customer</button>
            </div>
        </div>

        @if (session()->has('success'))
            <div class="px-6 py-3 bg-green-100 border-l-4 border-green-500 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="p-6">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact Person</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Phone</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Currency</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($customers as $customer)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $customer->code ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ $customer->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $customer->contact_person ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $customer->phone ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $customer->email }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $customer->currency ?? 'LKR' }}</td>
                        <td class="px-6 py-4 text-sm">
                            @if($customer->is_active ?? true)
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded">Inactive</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <button wire:click="openModal({{ $customer->id }})" class="px-2 py-1 text-xs bg-gray-100 rounded hover:bg-gray-200">Edit</button>
                            <button wire:click="delete({{ $customer->id }})" 
                                    onclick="return confirm('Are you sure you want to delete this customer?')" 
                                    class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200">Delete</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">No customers found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-4">{{ $customers->links() }}</div>
        </div>
    </div>

    @if($showModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click.self="closeModal">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $editingId ? 'Edit Customer' : 'Add Customer' }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Code <span class="text-gray-400">(optional)</span></label>
                        <input type="text" wire:model.defer="form.code" class="mt-1 block w-full border-gray-300 rounded-md" />
                        @error('form.code')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                        <input type="text" wire:model.defer="form.name" class="mt-1 block w-full border-gray-300 rounded-md" />
                        @error('form.name')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Contact Person</label>
                        <input type="text" wire:model.defer="form.contact_person" class="mt-1 block w-full border-gray-300 rounded-md" />
                        @error('form.contact_person')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone</label>
                        <input type="text" wire:model.defer="form.phone" class="mt-1 block w-full border-gray-300 rounded-md" />
                        @error('form.phone')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email <span class="text-red-500">*</span></label>
                        <input type="email" wire:model.defer="form.email" class="mt-1 block w-full border-gray-300 rounded-md" />
                        @error('form.email')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Currency</label>
                        <select wire:model.defer="form.currency" class="mt-1 block w-full border-gray-300 rounded-md">
                            <option value="LKR">LKR</option>
                            <option value="USD">USD</option>
                            <option value="EUR">EUR</option>
                            <option value="GBP">GBP</option>
                            <option value="INR">INR</option>
                        </select>
                        @error('form.currency')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea wire:model.defer="form.address" rows="3" class="mt-1 block w-full border-gray-300 rounded-md"></textarea>
                        @error('form.address')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" wire:model.defer="form.is_active" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
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

