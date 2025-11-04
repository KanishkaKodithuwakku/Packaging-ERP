<div>
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h1 class="text-2xl font-bold">Edit Permissions: {{ $role->name }}</h1>
            <div class="flex items-center space-x-3">
                <button wire:click="openAddPermissionModal" class="px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-md">
                    + Add New Permission
                </button>
                <a wire:navigate href="{{ route('role-management') }}" class="text-sm text-blue-600">← Back to Roles</a>
            </div>
        </div>

        <div class="p-6">
            @if (session()->has('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded">{{ session('success') }}</div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($permissions as $group => $items)
                    <div class="bg-gray-50 rounded p-4 border">
                        <h3 class="text-sm font-semibold text-gray-700 mb-2">{{ $group }}</h3>
                        <div class="space-y-2">
                            @foreach($items as $perm)
                                <label class="flex items-center space-x-2 text-sm">
                                    <input type="checkbox" wire:click="togglePermission('{{ $perm->name }}')" @checked(in_array($perm->name,$selected)) />
                                    <span>{{ $perm->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex justify-end">
                <button wire:click="save" class="px-4 py-2 rounded-md text-white bg-blue-600 hover:bg-blue-700">Save Changes</button>
            </div>
        </div>
    </div>

    <!-- Add New Permission Modal -->
    @if($showAddPermissionModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click.self="closeAddPermissionModal">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Permission</h3>
                
                @if (session()->has('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded">{{ session('success') }}</div>
                @endif

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Permission Name</label>
                    <input type="text" wire:model.defer="newPermissionName" 
                           placeholder="e.g., view customers"
                           class="mt-1 block w-full border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500" />
                    @error('newPermissionName')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Use lowercase letters and spaces. Example: "view customers", "create orders"</p>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <button wire:click="closeAddPermissionModal" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Cancel</button>
                    <button wire:click="createPermission" class="px-4 py-2 rounded-md text-white bg-green-600 hover:bg-green-700">Create Permission</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>


