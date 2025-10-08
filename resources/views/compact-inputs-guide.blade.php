<x-layouts.app>
    <div class="py-6">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-6">Compact Inputs Implementation Guide</h1>
                    
                    <!-- Overview -->
                    <div class="mb-8 bg-blue-50 p-6 rounded-lg">
                        <h2 class="text-lg font-semibold mb-4 text-blue-900">Overview</h2>
                        <p class="text-gray-700 mb-4">
                            The compact input system reduces the height and padding of form elements to create a more space-efficient interface. 
                            All input elements are reduced from the default ~40px height to 28px height.
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div class="bg-white p-3 rounded border">
                                <strong>Input Height:</strong> 28px (reduced from ~40px)
                            </div>
                            <div class="bg-white p-3 rounded border">
                                <strong>Padding:</strong> 4px 8px (reduced from default)
                            </div>
                            <div class="bg-white p-3 rounded border">
                                <strong>Font Size:</strong> 14px (optimized for compact size)
                            </div>
                        </div>
                    </div>

                    <!-- CSS Classes -->
                    <div class="mb-8">
                        <h2 class="text-lg font-semibold mb-4">Available CSS Classes</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-medium mb-3">Input Classes</h3>
                                <ul class="space-y-2 text-sm">
                                    <li><code class="bg-gray-200 px-2 py-1 rounded">compact-input</code> - For text, number, email, password inputs</li>
                                    <li><code class="bg-gray-200 px-2 py-1 rounded">compact-select</code> - For select dropdowns</li>
                                    <li><code class="bg-gray-200 px-2 py-1 rounded">compact-textarea</code> - For textarea elements</li>
                                </ul>
                            </div>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <h3 class="font-medium mb-3">Layout Classes</h3>
                                <ul class="space-y-2 text-sm">
                                    <li><code class="bg-gray-200 px-2 py-1 rounded">compact-form-grid</code> - Reduces grid gaps</li>
                                    <li><code class="bg-gray-200 px-2 py-1 rounded">compact-label</code> - Smaller label styling</li>
                                    <li><code class="bg-gray-200 px-2 py-1 rounded">compact-button</code> - Compact button styling</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Implementation Examples -->
                    <div class="mb-8">
                        <h2 class="text-lg font-semibold mb-4">Implementation Examples</h2>
                        
                        <!-- Basic Input -->
                        <div class="mb-6">
                            <h3 class="font-medium mb-3">Basic Input</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="compact-label block text-sm font-medium text-gray-700 mb-2">Order Number</label>
                                <input type="text" class="compact-input w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter order number">
                            </div>
                            <div class="mt-2 bg-gray-900 text-gray-100 p-3 rounded text-sm">
                                <code>&lt;input type="text" class="compact-input w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="Enter order number"&gt;</code>
                            </div>
                        </div>

                        <!-- Select Dropdown -->
                        <div class="mb-6">
                            <h3 class="font-medium mb-3">Select Dropdown</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="compact-label block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select class="compact-select w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                    <option>Pending</option>
                                    <option>Approved</option>
                                    <option>Completed</option>
                                </select>
                            </div>
                            <div class="mt-2 bg-gray-900 text-gray-100 p-3 rounded text-sm">
                                <code>&lt;select class="compact-select w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"&gt;</code>
                            </div>
                        </div>

                        <!-- Textarea -->
                        <div class="mb-6">
                            <h3 class="font-medium mb-3">Textarea</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <label class="compact-label block text-sm font-medium text-gray-700 mb-2">Notes</label>
                                <textarea class="compact-textarea w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" rows="3" placeholder="Enter notes"></textarea>
                            </div>
                            <div class="mt-2 bg-gray-900 text-gray-100 p-3 rounded text-sm">
                                <code>&lt;textarea class="compact-textarea w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" rows="3" placeholder="Enter notes"&gt;&lt;/textarea&gt;</code>
                            </div>
                        </div>

                        <!-- Form Grid -->
                        <div class="mb-6">
                            <h3 class="font-medium mb-3">Form Grid Layout</h3>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <div class="compact-form-grid grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="compact-label block text-sm font-medium text-gray-700 mb-2">Length (mm)</label>
                                        <input type="number" class="compact-input w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g., 600">
                                    </div>
                                    <div>
                                        <label class="compact-label block text-sm font-medium text-gray-700 mb-2">Width (mm)</label>
                                        <input type="number" class="compact-input w-full border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" placeholder="e.g., 400">
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2 bg-gray-900 text-gray-100 p-3 rounded text-sm">
                                <code>&lt;div class="compact-form-grid grid grid-cols-1 md:grid-cols-2 gap-4"&gt;</code>
                            </div>
                        </div>
                    </div>

                    <!-- Migration Guide -->
                    <div class="mb-8">
                        <h2 class="text-lg font-semibold mb-4">Migration Guide</h2>
                        <div class="bg-yellow-50 p-6 rounded-lg">
                            <h3 class="font-medium mb-3 text-yellow-800">How to Update Existing Forms</h3>
                            <div class="space-y-4 text-sm">
                                <div>
                                    <strong>1. Add CSS classes to inputs:</strong>
                                    <div class="mt-2 bg-white p-3 rounded border">
                                        <div class="text-red-600 mb-1">Before:</div>
                                        <code>&lt;input type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md"&gt;</code>
                                        <div class="text-green-600 mb-1 mt-2">After:</div>
                                        <code>&lt;input type="text" class="compact-input w-full border border-gray-300 rounded-md"&gt;</code>
                                    </div>
                                </div>
                                
                                <div>
                                    <strong>2. Update select elements:</strong>
                                    <div class="mt-2 bg-white p-3 rounded border">
                                        <div class="text-red-600 mb-1">Before:</div>
                                        <code>&lt;select class="w-full px-3 py-2 border border-gray-300 rounded-md"&gt;</code>
                                        <div class="text-green-600 mb-1 mt-2">After:</div>
                                        <code>&lt;select class="compact-select w-full border border-gray-300 rounded-md"&gt;</code>
                                    </div>
                                </div>
                                
                                <div>
                                    <strong>3. Update textarea elements:</strong>
                                    <div class="mt-2 bg-white p-3 rounded border">
                                        <div class="text-red-600 mb-1">Before:</div>
                                        <code>&lt;textarea class="w-full px-3 py-2 border border-gray-300 rounded-md"&gt;</code>
                                        <div class="text-green-600 mb-1 mt-2">After:</div>
                                        <code>&lt;textarea class="compact-textarea w-full border border-gray-300 rounded-md"&gt;</code>
                                    </div>
                                </div>
                                
                                <div>
                                    <strong>4. Update form grids:</strong>
                                    <div class="mt-2 bg-white p-3 rounded border">
                                        <div class="text-red-600 mb-1">Before:</div>
                                        <code>&lt;div class="grid grid-cols-1 md:grid-cols-2 gap-4"&gt;</code>
                                        <div class="text-green-600 mb-1 mt-2">After:</div>
                                        <code>&lt;div class="compact-form-grid grid grid-cols-1 md:grid-cols-2 gap-4"&gt;</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Live Example -->
                    <div class="mb-8">
                        <h2 class="text-lg font-semibold mb-4">Live Example</h2>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600 mb-4">See the compact form in action:</p>
                            <a href="{{ route('compact-form-example') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                                View Compact Form Example
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

