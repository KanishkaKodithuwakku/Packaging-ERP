<x-layouts.app>
    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-6">UOM Conversion Examples</h1>

                    <!-- Example 1: Glue Conversion -->
                    <div class="mb-8 bg-blue-50 p-6 rounded-lg">
                        <h2 class="text-lg font-semibold mb-4 text-blue-900">Example 1: Glue Product Conversion</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="font-medium mb-2">Product Details:</h3>
                                <ul class="text-sm space-y-1">
                                    <li><strong>Item:</strong> Industrial Glue</li>
                                    <li><strong>Base UOM:</strong> KG (Kilogram)</li>
                                    <li><strong>Conversion Profile:</strong> Glue Profile</li>
                                </ul>
                            </div>
                            <div>
                                <h3 class="font-medium mb-2">Conversion Rules:</h3>
                                <ul class="text-sm space-y-1">
                                    <li>1 Liter = 0.8 KG</li>
                                    <li>1 Barrel = 200 Liters = 160 KG</li>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-4 p-4 bg-white rounded border">
                            <h4 class="font-medium mb-2">Conversion Example:</h4>
                            <p class="text-sm">
                                <strong>Scenario:</strong> GRN receives 2 Barrels of Glue<br>
                                <strong>Transaction UOM:</strong> Barrels<br>
                                <strong>Transaction Qty:</strong> 2 Barrels<br>
                                <strong>Converted Qty:</strong> 320 KG (2 × 160 KG)<br>
                                <strong>Stock Value:</strong> 320 KG in base UOM
                            </p>
                        </div>
                    </div>

                    <!-- Example 2: Paint Conversion -->
                    <div class="mb-8 bg-green-50 p-6 rounded-lg">
                        <h2 class="text-lg font-semibold mb-4 text-green-900">Example 2: Paint Product Conversion</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="font-medium mb-2">Product Details:</h3>
                                <ul class="text-sm space-y-1">
                                    <li><strong>Item:</strong> Premium Paint</li>
                                    <li><strong>Base UOM:</strong> KG (Kilogram)</li>
                                    <li><strong>Conversion Profile:</strong> Paint Profile</li>
                                </ul>
                            </div>
                            <div>
                                <h3 class="font-medium mb-2">Conversion Rules:</h3>
                                <ul class="text-sm space-y-1">
                                    <li>1 Liter = 1.2 KG</li>
                                    <li>1 Gallon = 3.785 Liters = 4.54 KG</li>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-4 p-4 bg-white rounded border">
                            <h4 class="font-medium mb-2">Conversion Example:</h4>
                            <p class="text-sm">
                                <strong>Scenario:</strong> Material Request for 5 Gallons of Paint<br>
                                <strong>Transaction UOM:</strong> Gallons<br>
                                <strong>Transaction Qty:</strong> 5 Gallons<br>
                                <strong>Converted Qty:</strong> 22.7 KG (5 × 4.54 KG)<br>
                                <strong>Stock Value:</strong> 22.7 KG in base UOM
                            </p>
                        </div>
                    </div>

                    <!-- Example 3: Global Conversion -->
                    <div class="mb-8 bg-purple-50 p-6 rounded-lg">
                        <h2 class="text-lg font-semibold mb-4 text-purple-900">Example 3: Global Conversion (No Profile)
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="font-medium mb-2">Product Details:</h3>
                                <ul class="text-sm space-y-1">
                                    <li><strong>Item:</strong> Steel Rods</li>
                                    <li><strong>Base UOM:</strong> KG (Kilogram)</li>
                                    <li><strong>Conversion Profile:</strong> None (Uses Global)</li>
                                </ul>
                            </div>
                            <div>
                                <h3 class="font-medium mb-2">Global Conversion Rules:</h3>
                                <ul class="text-sm space-y-1">
                                    <li>1 Gram = 0.001 KG</li>
                                    <li>1 Pound = 0.453592 KG</li>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-4 p-4 bg-white rounded border">
                            <h4 class="font-medium mb-2">Conversion Example:</h4>
                            <p class="text-sm">
                                <strong>Scenario:</strong> Supplier PO for 1000 Grams of Steel<br>
                                <strong>Transaction UOM:</strong> Grams<br>
                                <strong>Transaction Qty:</strong> 1000 Grams<br>
                                <strong>Converted Qty:</strong> 1 KG (1000 × 0.001)<br>
                                <strong>Stock Value:</strong> 1 KG in base UOM
                            </p>
                        </div>
                    </div>

                    <!-- Conversion Logic Flow -->
                    <div class="bg-gray-50 p-6 rounded-lg">
                        <h2 class="text-lg font-semibold mb-4">Conversion Logic Flow</h2>
                        <div class="space-y-4">
                            <div class="flex items-start space-x-3">
                                <div
                                    class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 font-semibold text-sm">1</span>
                                </div>
                                <div>
                                    <h4 class="font-medium">Check Item-Specific Profile</h4>
                                    <p class="text-sm text-gray-600">If item has a conversion profile, look for
                                        conversion rules within that profile first.</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-3">
                                <div
                                    class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <span class="text-green-600 font-semibold text-sm">2</span>
                                </div>
                                <div>
                                    <h4 class="font-medium">Check Global Conversions</h4>
                                    <p class="text-sm text-gray-600">If no profile-specific rule found, check global
                                        conversion rules.</p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-3">
                                <div
                                    class="flex-shrink-0 w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                    <span class="text-red-600 font-semibold text-sm">3</span>
                                </div>
                                <div>
                                    <h4 class="font-medium">Throw Exception</h4>
                                    <p class="text-sm text-gray-600">If no conversion rule found, throw an exception to
                                        prevent data inconsistency.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Code Example -->
                    {{-- <div class="mt-8 bg-gray-900 text-gray-100 p-6 rounded-lg">
                        <h3 class="text-lg font-semibold mb-4">Code Example</h3>
                        <pre class="text-sm overflow-x-auto"><code>// Using the UOM Conversion Service
use App\Services\UomConversionService;

$conversionService = new UomConversionService();

// Example: Convert 2 Barrels to KG for Glue item
$item = Inventory::find(1); // Glue item with base UOM = KG, profile = Glue Profile
$barrelUomId = Uom::where('code', 'BRL')->first()->id;

try {
    $convertedQty = $conversionService->convertToBaseUom($item, 2, $barrelUomId);
    // Result: 320 KG (2 × 160 KG from profile conversion)
} catch (Exception $e) {
    // Handle conversion error
    Log::error('UOM conversion failed: ' . $e->getMessage());
}</code></pre>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>