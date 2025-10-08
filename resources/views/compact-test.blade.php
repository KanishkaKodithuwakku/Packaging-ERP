<x-layouts.app>
    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-6">Compact Input Test</h1>
                    
                    <!-- Test Form -->
                    <form class="space-y-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h2 class="text-lg font-semibold mb-4">Test Compact Inputs</h2>
                            
                            <div class="compact-form-grid grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="compact-label block text-sm font-medium text-gray-700 mb-2">Customer</label>
                                    <select class="compact-select w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option>Select Customer</option>
                                        <option>Customer 1</option>
                                        <option>Customer 2</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="compact-label block text-sm font-medium text-gray-700 mb-2">Order Number</label>
                                    <input type="text" class="compact-input w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter order number">
                                </div>
                                
                                <div>
                                    <label class="compact-label block text-sm font-medium text-gray-700 mb-2">Status</label>
                                    <select class="compact-select w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option>Pending</option>
                                        <option>Approved</option>
                                        <option>Completed</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="compact-label block text-sm font-medium text-gray-700 mb-2">Length (mm)</label>
                                    <input type="number" class="compact-input w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g., 600">
                                </div>
                                
                                <div>
                                    <label class="compact-label block text-sm font-medium text-gray-700 mb-2">Width (mm)</label>
                                    <input type="number" class="compact-input w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g., 400">
                                </div>
                                
                                <div>
                                    <label class="compact-label block text-sm font-medium text-gray-700 mb-2">Height (mm)</label>
                                    <input type="number" class="compact-input w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g., 300">
                                </div>
                                
                                <div>
                                    <label class="compact-label block text-sm font-medium text-gray-700 mb-2">Ply</label>
                                    <input type="number" class="compact-input w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter ply">
                                </div>
                                
                                <div>
                                    <label class="compact-label block text-sm font-medium text-gray-700 mb-2">Flute Type</label>
                                    <input type="text" class="compact-input w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g., B, C, E">
                                </div>
                                
                                <div>
                                    <label class="compact-label block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                                    <input type="number" class="compact-input w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter quantity">
                                </div>
                                
                                <div>
                                    <label class="compact-label block text-sm font-medium text-gray-700 mb-2">Unit Price</label>
                                    <input type="number" step="0.01" class="compact-input w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter unit price">
                                </div>
                                
                                <div class="md:col-span-2">
                                    <label class="compact-label block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                    <textarea class="compact-textarea w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" rows="3" placeholder="Enter notes"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-3">
                            <button type="button" class="compact-button bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                                Cancel
                            </button>
                            <button type="submit" class="compact-button bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                                Create
                            </button>
                        </div>
                    </form>
                    
                    <!-- Comparison -->
                    <div class="mt-8 bg-yellow-50 p-4 rounded-lg">
                        <h3 class="font-medium mb-3 text-yellow-800">Size Comparison</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Regular Input (Default)</label>
                                <input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Regular size input">
                            </div>
                            <div>
                                <label class="compact-label block text-sm font-medium text-gray-700 mb-2">Compact Input</label>
                                <input type="text" class="compact-input w-full border border-gray-300 rounded-md" placeholder="Compact size input">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

