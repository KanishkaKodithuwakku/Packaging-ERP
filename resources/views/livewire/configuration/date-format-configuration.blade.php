<div>
    <div class="px-6 py-4">
        <h1 class="text-2xl font-bold text-gray-900">Date Format Configuration</h1>
        <p class="text-gray-600 mt-1">Configure the date format for displaying dates throughout the system</p>

        @if (session()->has('success'))
        <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
        @endif

        <div class="mt-6 bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Date Format Settings</h3>
            </div>

            <div class="px-6 py-4">
                <form wire:submit.prevent="saveDateFormat">
                    <div class="space-y-6">
                        <!-- Date Format Selection -->
                        <div>
                            <label for="dateFormat" class="block text-sm font-medium text-gray-700 mb-2">
                                Select Date Format <span class="text-red-500">*</span>
                            </label>
                            <select 
                                id="dateFormat"
                                wire:model.live="dateFormat"
                                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 text-sm">
                                @foreach($dateFormats as $format)
                                    <option value="{{ $format['value'] }}">
                                        {{ $format['label'] }} [{{ $format['example'] }}]
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-2 text-sm text-gray-500">
                                Current format: <strong>{{ $dateFormat }}</strong>
                            </p>
                        </div>

                        <!-- Preview Section -->
                        <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Preview</h4>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Today's Date:</span>
                                    <span class="text-sm font-medium text-gray-900">
                                        @if($dateFormat)
                                            {{ \Carbon\Carbon::now()->format($dateFormat) }}
                                        @endif
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Sample Date (Jan 15, 2024):</span>
                                    <span class="text-sm font-medium text-gray-900">
                                        @if($dateFormat)
                                            {{ \Carbon\Carbon::parse('2024-01-15')->format($dateFormat) }}
                                        @endif
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Current Selection:</span>
                                    <span class="text-sm font-medium text-blue-600">
                                        @foreach($dateFormats as $format)
                                            @if($format['value'] === $dateFormat)
                                                {{ $format['label'] }} - {{ $format['example'] }}
                                            @endif
                                        @endforeach
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Save Button -->
                        <div class="flex justify-end space-x-3 pt-4 border-t">
                            <button 
                                type="submit"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md text-sm transition-colors">
                                Save Date Format
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

