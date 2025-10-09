<div>
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-semibold text-gray-800">Chart of Accounts</h2>
                <div class="space-x-2">
                    <a wire:navigate href="{{ route('create-group') }}"
                        class="bg-blue-500 hover:bg-blue-600 text-white font-medium px-4 py-2 rounded-md text-sm transition duration-300 ease-in-out">
                        Add Group
                    </a>
                    <a href="#"
                        class="bg-green-500 hover:bg-green-600 text-white font-medium px-4 py-2 rounded-md text-sm transition duration-300 ease-in-out">
                        Add Ledger
                    </a>
                </div>
            </div>
        </div>

        <div class="p-6">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Chart of Accounts Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Account Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">O/P Balance (LKR)</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">C/L Balance (LKR)</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($accountTree as $group)
                            @include('livewire.accounts.partials.group-row', ['group' => $group, 'level' => 0])
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
