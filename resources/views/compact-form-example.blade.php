<x-layouts.app>
    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-6">Compact Form Example</h1>
                    
                    <form class="space-y-6">
                        <!-- Customer Section -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h2 class="text-lg font-semibold mb-4">Customer Details</h2>
                            <div class="compact-form-grid grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="compact-label block text-sm font-medium text-gray-700">Customer</label>
                                    <select class="compact-select w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option>Select Customer</option>
                                        <option>Customer 1</option>
                                        <option>Customer 2</option>
                                    </select>
                                </div>
                                
                                <div>
                                    <label class="compact-label block text-sm font-medium text-gray-700">Order Number</label>
                                    <input type="text" class="compact-input w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter order number">
                                </div>
                                
                                <div>
                                    <label class="compact-label block text-sm font-medium text-gray-700">Status</label>
                                    <select class="compact-select w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                        <option>Pending</option>
                                        <option>Approved</option>
                                        <option>Completed</option>
                                    </select>
                                </div>
                                
                                <div class="md:col-span-2">
                                    <label class="compact-label block text-sm font-medium text-gray-700">Notes</label>
                                    <textarea class="compact-textarea w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" rows="3" placeholder="Enter notes"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items Section -->
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex justify-between items-center mb-4">
                                <h2 class="text-lg font-semibold">Order Items</h2>
                                <button type="button" class="compact-button bg-green-600 text-white px-3 py-1 rounded-md hover:bg-green-700">
                                    Add Item
                                </button>
                            </div>
                            
                            <!-- Item 1 -->
                            <div class="bg-white p-4 rounded border mb-4">
                                <h3 class="font-medium mb-3">Item 1</h3>
                                <div class="compact-form-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    <div>
                                        <label class="compact-label block text-sm font-medium text-gray-700">Item Description</label>
                                        <input type="text" class="compact-input w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter description">
                                    </div>
                                    
                                    <div>
                                        <label class="compact-label block text-sm font-medium text-gray-700">Length (mm)</label>
                                        <input type="number" class="compact-input w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g., 600">
                                    </div>
                                    
                                    <div>
                                        <label class="compact-label block text-sm font-medium text-gray-700">Width (mm)</label>
                                        <input type="number" class="compact-input w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g., 400">
                                    </div>
                                    
                                    <div>
                                        <label class="compact-label block text-sm font-medium text-gray-700">Height (mm)</label>
                                        <input type="number" class="compact-input w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g., 300">
                                    </div>
                                    
                                    <div>
                                        <label class="compact-label block text-sm font-medium text-gray-700">Ply</label>
                                        <input type="number" class="compact-input w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter ply">
                                    </div>
                                    
                                    <div>
                                        <label class="compact-label block text-sm font-medium text-gray-700">Flute Type</label>
                                        <input type="text" class="compact-input w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g., B, C, E">
                                    </div>
                                    
                                    <div>
                                        <label class="compact-label block text-sm font-medium text-gray-700">Quantity</label>
                                        <input type="number" class="compact-input w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter quantity">
                                    </div>
                                    
                                    <div>
                                        <label class="compact-label block text-sm font-medium text-gray-700">Unit Price</label>
                                        <input type="number" step="0.01" class="compact-input w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter unit price">
                                    </div>
                                    
                                    <div class="lg:col-span-3">
                                        <label class="compact-label block text-sm font-medium text-gray-700">GSM Layers</label>
                                        <div class="flex items-center space-x-2">
                                            <span class="text-sm text-gray-600">Add GSM Layer:</span>
                                            <button type="button" class="compact-button bg-blue-600 text-white px-2 py-1 rounded text-xs hover:bg-blue-700">
                                                + Add GSM Layer
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="lg:col-span-3">
                                        <label class="compact-label block text-sm font-medium text-gray-700">Notes</label>
                                        <textarea class="compact-textarea w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" rows="2" placeholder="Enter item notes"></textarea>
                                    </div>
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
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

