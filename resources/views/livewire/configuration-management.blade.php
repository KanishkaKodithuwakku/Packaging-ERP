<div>
    <div class="px-6 py-4">
        <h1 class="text-2xl font-bold text-gray-900">GRN Processing Configuration</h1>
        <p class="text-gray-600 mt-1">Configure default settings for GRN processing</p>

        @if (session()->has('success'))
        <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
        @endif

        <div class="mt-6 bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">GRN Processing Settings</h3>
            </div>

            <div class="px-6 py-4">
                <div class="space-y-6">
                    @foreach($configurations as $config)
                    <div class="flex items-center justify-between py-3 border-b border-gray-200 last:border-b-0">
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-gray-900">{{ ucwords(str_replace('_', ' ', $config->key)) }}</h4>
                            <p class="text-sm text-gray-500 mt-1">{{ $config->description }}</p>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            @if($editingKey === $config->key)
                                <div class="flex items-center space-x-2">
                                    @if($config->type === 'boolean')
                                        <select wire:model="editingValue" class="px-3 py-1 border border-gray-300 rounded-md text-sm">
                                            <option value="true">True</option>
                                            <option value="false">False</option>
                                        </select>
                                    @elseif($config->key === 'grn_default_costing_method')
                                        <select wire:model="editingValue" class="px-3 py-1 border border-gray-300 rounded-md text-sm">
                                            <option value="FIFO">FIFO (First In, First Out)</option>
                                            <option value="LIFO">LIFO (Last In, First Out)</option>
                                        </select>
                                    @else
                                        <input type="text" wire:model="editingValue" 
                                               class="px-3 py-1 border border-gray-300 rounded-md text-sm w-32">
                                    @endif
                                    <button wire:click="saveConfiguration" 
                                            class="px-3 py-1 bg-green-600 text-white rounded-md text-sm hover:bg-green-700">
                                        Save
                                    </button>
                                    <button wire:click="cancelEdit" 
                                            class="px-3 py-1 bg-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-400">
                                        Cancel
                                    </button>
                                </div>
                            @else
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-gray-900">
                                        @if($config->type === 'boolean')
                                            {{ $config->value === 'true' ? 'Enabled' : 'Disabled' }}
                                        @else
                                            {{ $config->value }}
                                        @endif
                                    </span>
                                    <button wire:click="editConfiguration('{{ $config->key }}')" 
                                            class="px-3 py-1 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">
                                        Edit
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>