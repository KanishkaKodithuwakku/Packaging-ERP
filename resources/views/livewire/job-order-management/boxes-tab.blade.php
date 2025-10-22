<div class="space-y-6">
    <!-- Add Box Form -->
    <div class="bg-gray-50 p-4 rounded-md">
        <h4 class="text-lg font-medium text-gray-900 mb-4">Add New Box</h4>
        
        <!-- Basic Information -->
        <div class="mb-6">
            <h5 class="text-md font-medium text-gray-700 mb-2">Basic Information</h5>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Order Qty <span class="text-red-500">*</span></label>
                    <input type="number" 
                           wire:model.live="boxForm.order_qty" 
                           class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white"
                           placeholder="1000">
                    @error('boxForm.order_qty') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price <span class="text-red-500">*</span></label>
                    <input type="number" 
                           step="0.01" 
                           wire:model="boxForm.selling_price" 
                           class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white"
                           placeholder="0.00">
                    @error('boxForm.selling_price') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Activity</label>
                    <input type="text" 
                           wire:model="boxForm.activity" 
                           class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white"
                           placeholder="Activity type">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Printing Instruction</label>
                    <select wire:model="boxForm.printing_instruction" 
                            class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <option value="">Select...</option>
                        <option value="No Printing">No Printing</option>
                        <option value="Single Color">Single Color</option>
                        <option value="Multi Color">Multi Color</option>
                        <option value="Full Color">Full Color</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No of Colours</label>
                    <input type="number" 
                           wire:model="boxForm.no_of_colours" 
                           class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white"
                           placeholder="0">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stitched/Glued</label>
                    <select wire:model="boxForm.stitched_glued" 
                            class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <option value="">Select...</option>
                        <option value="Stitched">Stitched</option>
                        <option value="Glued">Glued</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sample Available</label>
                    <select wire:model="boxForm.sample_available" 
                            class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <option value="No">No</option>
                        <option value="Yes">Yes</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sample Attached</label>
                    <select wire:model="boxForm.sample_attached" 
                            class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <option value="No">No</option>
                        <option value="Yes">Yes</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Box Dimensions -->
        <div class="mt-6">
            <h5 class="text-md font-medium text-gray-700 mb-2">Box Dimensions</h5>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Length (L) <span class="text-red-500">*</span></label>
                    <input type="number" 
                           step="0.01" 
                           wire:model.live="boxForm.length" 
                           class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white"
                           placeholder="0.00">
                    @error('boxForm.length') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Width (W) <span class="text-red-500">*</span></label>
                    <input type="number" 
                           step="0.01" 
                           wire:model.live="boxForm.width" 
                           class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white"
                           placeholder="0.00">
                    @error('boxForm.width') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Height (H) <span class="text-red-500">*</span></label>
                    <input type="number" 
                           step="0.01" 
                           wire:model.live="boxForm.height" 
                           class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white"
                           placeholder="0.00">
                    @error('boxForm.height') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Unit <span class="text-red-500">*</span></label>
                    <select wire:model.live="boxForm.unit" 
                            wire:change="calculateDimensions"
                            class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <option value="CM">CM</option>
                        <option value="MM">MM</option>
                        <option value="INCHES">INCHES</option>
                    </select>
                    @error('boxForm.unit') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                    <select wire:model.live="boxForm.dimension_type" 
                            wire:change="calculateDimensions"
                            class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <option value="INTERNAL">INTERNAL</option>
                        <option value="EXTERNAL">EXTERNAL</option>
                    </select>
                    @error('boxForm.dimension_type') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Material Specifications -->
        <div class="mt-6">
            <h5 class="text-md font-medium text-gray-700 mb-2">Material Specifications</h5>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Top Liner <span class="text-red-500">*</span></label>
                    <select wire:model.live="boxForm.top_liner" 
                            class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <option value="">Select...</option>
                        <option value="WHITE">WHITE</option>
                        <option value="KRAFT">KRAFT</option>
                        <option value="TEST">TEST</option>
                    </select>
                    @error('boxForm.top_liner') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">PLY <span class="text-red-500">*</span></label>
                    <select wire:model.live="boxForm.ply" 
                            class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <option value="3">3</option>
                        <option value="5">5</option>
                        <option value="7">7</option>
                    </select>
                    @error('boxForm.ply') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">FLUTE <span class="text-red-500">*</span></label>
                    <select wire:model="boxForm.flute" 
                            class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <option value="">Select...</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="E">E</option>
                        <option value="BC">BC</option>
                        <option value="EB">EB</option>
                    </select>
                    @error('boxForm.flute') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">FSC CLAIM <span class="text-red-500">*</span></label>
                    <select wire:model="boxForm.fsc_claim" 
                            class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white">
                        <option value="100%">100%</option>
                        <option value="MIX">MIX</option>
                    </select>
                    @error('boxForm.fsc_claim') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Combination Fields (Dynamic based on PLY) -->
        <div class="mt-6">
            <h5 class="text-md font-medium text-gray-700 mb-2">Combination Parameters</h5>
            <!-- Single Row - 5 inputs -->
            <div class="grid grid-cols-5 gap-2">
                @for($i = 1; $i <= 5; $i++)
                    <div @if(($boxForm['ply'] == '3' && $i > 3) || ($boxForm['ply'] == '5' && $i > 5) || ($boxForm['ply'] == '7' && $i > 7) || (!$boxForm['ply'] && $i > 3)) style="display: none;" @endif>
                        <input type="text" 
                               wire:model="boxForm.combination_{{ $i }}" 
                               class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                               placeholder="e.g., 140KL">
                    </div>
                @endfor
            </div>
        </div>

        <!-- Calculated Fields -->
        <div class="mt-6">
            <div class="flex justify-between items-center mb-2">
                <h5 class="text-md font-medium text-gray-700">Calculated Fields</h5>
                <button type="button" 
                        wire:click="forceCalculation"
                        class="px-3 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600">
                    Recalculate
                </button>
            </div>
            <!-- Debug Info -->
            <div class="mb-4 p-2 bg-yellow-100 text-xs">
                <strong>Debug:</strong> Unit={{ $boxForm['unit'] }}, Type={{ $boxForm['dimension_type'] }}, L={{ $boxForm['length'] }}, W={{ $boxForm['width'] }}, H={{ $boxForm['height'] }}
                <br><strong>Calculated:</strong> Reel={{ $calculatedReelSize }}, Cut={{ $calculatedCutSize }}
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reel Size</label>
                    <div class="w-full px-3 py-1 border border-gray-300 rounded-md bg-gray-100 text-gray-700">
                        {{ $calculatedReelSize ? number_format($calculatedReelSize, 2) . ' inches' : 'Enter dimensions' }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cut Size</label>
                    <div class="w-full px-3 py-1 border border-gray-300 rounded-md bg-gray-100 text-gray-700">
                        {{ $calculatedCutSize ? number_format($calculatedCutSize, 2) . ' inches' : 'Enter dimensions' }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No of Ups</label>
                    <input type="number" 
                           wire:model.live="boxForm.no_of_ups" 
                           class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white"
                           placeholder="0">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">BOARD QTY</label>
                    <div class="w-full px-3 py-1 border border-gray-300 rounded-md bg-gray-100 text-gray-700">
                        {{ $calculatedBoardQty ?: 'Enter order qty and no of ups' }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier Price</label>
                    <input type="number" 
                           step="0.01" 
                           wire:model="boxForm.supplier_price" 
                           class="w-full px-3 py-1 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white"
                           placeholder="0.00">
                </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea wire:model="boxForm.notes" 
                      rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Additional notes..."></textarea>
        </div>

        <!-- Add Box Button -->
        <div class="mt-6 flex justify-end">
            <button wire:click="addBox" 
                    class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700">
                Add Box 
            </button>
        </div>

    </div>
</div>