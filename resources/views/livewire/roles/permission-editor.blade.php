<div>
    <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h1 class="text-2xl font-bold">Edit Permissions: {{ $role->name }}</h1>
            <a wire:navigate href="{{ route('role-management') }}" class="text-sm text-blue-600">← Back to Roles</a>
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
</div>


