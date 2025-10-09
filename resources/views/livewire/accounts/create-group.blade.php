<div>
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">Add Account Group</h2>
        </div>

        <div class="p-6">
            <form wire:submit.prevent="save" class="max-w-2xl">
                        <!-- Group Name -->
                        <div class="mb-6">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Group Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model.defer="name" id="name" placeholder="Enter group name"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Group Code -->
                        <div class="mb-6">
                            <label for="code" class="block text-sm font-medium text-gray-700 mb-2">
                                Group Code (Optional)
                            </label>
                            <input type="text" wire:model.defer="code" id="code" placeholder="Enter group code"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Parent Group -->
                        <div class="mb-6">
                            <label for="group_id" class="block text-sm font-medium text-gray-700 mb-2">
                                Parent Group
                            </label>
                            <select wire:model="group_id" id="group_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select a parent group (optional)</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                            @error('group_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center gap-4">
                            <button type="submit"
                                class="bg-blue-500 hover:bg-blue-600 text-white font-medium px-6 py-2 rounded-md transition duration-300 ease-in-out">
                                Create Group
                            </button>
                            <a href="{{ route('chart-of-accounts') }}" wire:navigate
                                class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium px-6 py-2 rounded-md transition duration-300 ease-in-out">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
