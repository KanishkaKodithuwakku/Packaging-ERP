<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-[95vw] shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex justify-between items-center pb-4 border-b">
                <h3 class="text-lg font-medium text-gray-900">Edit Box</h3>
                <button wire:click="closeEditBoxModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Edit Box Form -->
            <div class="bg-gray-50 p-4 rounded-md mt-4">
                <!-- Basic Information -->
                <div class="mb-6">
                    <h5 class="text-md font-medium text-gray-700 mb-3">Basic Information</h5>
                    <div class="space-y-3">
                        <!-- Row 1: Order Qty, Selling Price, Activity, Printing Instruction -->
                        <div class="flex gap-4">
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Order <br>Qty <span class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <input type="number"
                                           wire:model.live="editingBoxData.order_qty"
                                           class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="1000">
                                    @error('editingBoxData.order_qty') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Selling Price <span class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <input type="number"
                                           step="0.01"
                                           wire:model="editingBoxData.selling_price"
                                           class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0.00">
                                    @error('editingBoxData.selling_price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Activity <span class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <input type="text"
                                           wire:model="editingBoxData.activity"
                                           class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Activity type">
                                    @error('editingBoxData.activity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Printing <br> Instruction <span class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <select wire:model="editingBoxData.printing_instruction"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select...</option>
                                        <option value="Printed">Printed</option>
                                        <option value="UnPrinted">UnPrinted</option>
                                    </select>
                                    @error('editingBoxData.printing_instruction') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Row 2: No of Colours, Stitched/Glued, Sample Available, Sample Attached -->
                        <div class="flex gap-4">
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">No of Colours <span class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <input type="number"
                                           wire:model="editingBoxData.no_of_colours"
                                           min="0"
                                           class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0">
                                    @error('editingBoxData.no_of_colours') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Stitched/<br>Glued <span class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <select wire:model="editingBoxData.stitched_glued"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select...</option>
                                        <option value="None">None</option>
                                        <option value="Stitched">Stitched</option>
                                        <option value="Glued">Glued</option>
                                    </select>
                                    @error('editingBoxData.stitched_glued') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Sample <br> Available <span class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <select wire:model="editingBoxData.sample_available"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select...</option>
                                        <option value="No">No</option>
                                        <option value="Yes">Yes</option>
                                    </select>
                                    @error('editingBoxData.sample_available') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Sample <br> Attached <span class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <select wire:model="editingBoxData.sample_attached"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select...</option>
                                        <option value="No">No</option>
                                        <option value="Yes">Yes</option>
                                    </select>
                                    @error('editingBoxData.sample_attached') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Box Dimensions -->
                <div class="mt-6">
                    <h5 class="text-md font-medium text-gray-700 mb-3">Box Dimensions</h5>
                    <div class="space-y-3">
                        <!-- Row 1: Length, Width, Height, Unit -->
                        <div class="flex gap-4">
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Length (L) <span class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <input type="number"
                                           step="0.01"
                                           wire:model.live="editingBoxData.length"
                                           class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0.00">
                                    @error('editingBoxData.length') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Width (W) <span class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <input type="number"
                                           step="0.01"
                                           wire:model.live="editingBoxData.width"
                                           class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0.00">
                                    @error('editingBoxData.width') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Height (H) <span class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <input type="number"
                                           step="0.01"
                                           wire:model.live="editingBoxData.height"
                                           class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0.00">
                                    @error('editingBoxData.height') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Unit <span class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <select wire:model.live="editingBoxData.unit"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="CM">CM</option>
                                        <option value="MM">MM</option>
                                        <option value="INCHES">INCHES</option>
                                    </select>
                                    @error('editingBoxData.unit') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Row 2: Type only (1 input) -->
                        <div class="flex gap-4">
                            <div class="flex items-center gap-3" style="width: calc(25% - 0.75rem);">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Type <span class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <select wire:model.live="editingBoxData.dimension_type"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="INTERNAL">INTERNAL</option>
                                        <option value="EXTERNAL">EXTERNAL</option>
                                    </select>
                                    @error('editingBoxData.dimension_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Material Specifications -->
                <div class="mt-6">
                    <h5 class="text-md font-medium text-gray-700 mb-3">Material Specifications</h5>
                    <div class="space-y-3">
                        <!-- Row 1: Top Liner, FSC CLAIM, FLUTE, PLY -->
                        <div class="flex gap-4">
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Top Liner <span class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <select wire:model.live="editingBoxData.top_liner"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select...</option>
                                        <option value="WHITE">WHITE</option>
                                        <option value="BROWN">BROWN</option>
                                    </select>
                                    @error('editingBoxData.top_liner') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">FSC Claim <span class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <select wire:model="editingBoxData.fsc_claim"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="No Claim">No Claim</option>
                                            <option value="100%">100%</option>
                                            <option value="MIX">MIX</option>
                                    </select>
                                    @error('editingBoxData.fsc_claim') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Flute <span class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <select wire:model="editingBoxData.flute"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select...</option>
                                        <option value="B">B FLUTE</option>
                                        <option value="C">C FLUTE</option>
                                        <option value="E">B/C FLUTE</option>
                                    </select>
                                    @error('editingBoxData.flute') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Ply <span class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <select wire:model.live="editingBoxData.ply"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="3">3</option>
                                        <option value="5">5</option>
                                        <option value="7">7</option>
                                    </select>
                                    @error('editingBoxData.ply') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Combination Fields (Dynamic based on PLY) -->
                <div class="mt-6">
                    <h5 class="text-md font-medium text-gray-700 mb-2">Combination Parameters <span class="text-red-500">*</span></h5>
                    <!-- Responsive Grid - up to 7 inputs -->
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-7 gap-0.5">
                        @for($i = 1; $i <= 7; $i++)
                            <div @if(($editingBoxData['ply'] == '3' && $i > 3) || ($editingBoxData['ply'] == '5' && $i > 5) || ($editingBoxData['ply'] == '7' && $i > 7) || (empty($editingBoxData['ply']) && $i > 3)) style="display: none;" @endif>
                                <input type="text"
                                       wire:model="editingBoxData.combination_{{ $i }}"
                                       maxlength="6"
                                       class="w-full px-1 py-2 text-xs border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="e.g., 140KL"
                                       style="max-width: 150px;">
                                @error('editingBoxData.combination_'.$i) <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @endfor
                    </div>
                    @error('editingBoxData.combination') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Calculated Fields -->
                <div class="mt-6">
                    <div class="flex justify-between items-center mb-3">
                        <h5 class="text-md font-medium text-gray-700">Calculated Fields</h5>
                    </div>

                    <div class="space-y-3">
                        <!-- Row 1: Reel Size, Cut Size, No of Ups, BOARD QTY -->
                        <div class="flex gap-4">
                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Reel Size <span class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <input type="number"
                                               step="0.01"
                                               wire:model.live="editingBoxData.reel_size"
                                               class="block flex-1 px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="0.00">
                                        <span class="text-xs text-gray-500 whitespace-nowrap">inches</span>
                                    </div>
                                    @error('editingBoxData.reel_size') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Cut Size <span class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <input type="number"
                                               step="0.01"
                                               wire:model.live="editingBoxData.cut_size"
                                               class="block flex-1 px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="0.00">
                                        <span class="text-xs text-gray-500 whitespace-nowrap">inches</span>
                                    </div>
                                    @error('editingBoxData.cut_size') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">No of Ups <span class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <input type="number"
                                           wire:model.live="editingBoxData.no_of_ups"
                                           class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0">
                                    @error('editingBoxData.no_of_ups') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div class="flex items-center gap-3 flex-1">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Board Qty <span class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <input type="number"
                                           step="0.01"
                                           wire:model.live="editingBoxData.board_qty"
                                           class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0">
                                    @error('editingBoxData.board_qty') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Row 2: Supplier Price only (1 input) -->
                        <div class="flex gap-4">
                            <div class="flex items-center gap-3" style="width: calc(25% - 0.75rem);">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Supplier Price <span class="text-red-500">*</span></label>
                                <div class="flex-1">
                                    <input type="number"
                                           step="0.01"
                                           wire:model="editingBoxData.supplier_price"
                                           class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0.00">
                                    @error('editingBoxData.supplier_price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea wire:model="editingBoxData.notes"
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Additional notes..."></textarea>
                    @error('editingBoxData.notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Modal Footer -->
                <div class="mt-6 flex justify-end space-x-3">
                    <button wire:click="closeEditBoxModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-md transition-colors">
                        Cancel
                    </button>
                    <button wire:click="saveBox({{ $editingBoxIndex }})"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md transition-colors">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
