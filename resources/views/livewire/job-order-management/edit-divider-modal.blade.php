<!-- Edit Divider Modal -->
<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click.self="closeEditDividerModal">
    <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-[70vw] shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex justify-between items-center mb-4 pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Edit Divider</h3>
            <button wire:click="closeEditDividerModal" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Edit Divider Form -->
        <div class="space-y-6">
            <!-- Divider Specifications -->
            <div class="space-y-3">
                <!-- Row 1: FSC CLAIM, Quantity, Unit, PLY -->
                <div class="flex gap-4">
                    <div class="flex items-center gap-3 flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">FSC <br> Claim <span class="text-red-500">*</span></label>
                        <div class="flex-1">
                            <select wire:model="editingDividerData.fsc_claim"
                                    class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="100%">100%</option>
                                <option value="MIX">MIX</option>
                                <option value="No Claim">No Claim</option>
                            </select>
                            @error('editingDividerData.fsc_claim') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-3 flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Quantity <span class="text-red-500">*</span></label>
                        <div class="flex-1">
                            <input type="number"
                                   wire:model="editingDividerData.quantity"
                                   class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="1000">
                            @error('editingDividerData.quantity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-3 flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Unit <span class="text-red-500">*</span></label>
                        <div class="flex-1">
                            <select wire:model="editingDividerData.unit"
                                    class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="CM">CM</option>
                                <option value="MM">MM</option>
                                <option value="INCHES">INCHES</option>
                            </select>
                            @error('editingDividerData.unit') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex items-center gap-3 flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Ply <span class="text-red-500">*</span></label>
                        <div class="flex-1">
                            <select wire:model.live="editingDividerData.ply"
                                    class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                <option value="3">3</option>
                                <option value="5">5</option>
                                <option value="7">7</option>
                            </select>
                            @error('editingDividerData.ply') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Combination Parameters -->
            <div class="mb-6">
                <h5 class="text-md font-medium text-gray-700 mb-2">Combination <span class="text-red-500">*</span></h5>
                <!-- Responsive Grid - up to 7 inputs -->
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-7 gap-2">
                    @for($i = 1; $i <= 7; $i++)
                        <div @if(($editingDividerData['ply'] == '3' && $i > 3) || ($editingDividerData['ply'] == '5' && $i > 5) || ($editingDividerData['ply'] == '7' && $i > 7) || (!$editingDividerData['ply'] && $i > 3)) style="display: none;" @endif>
                            <input type="text"
                                   wire:model="editingDividerData.combination_{{ $i }}"
                                   maxlength="6"
                                   class="w-full px-1 py-2 text-xs border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="e.g., 140KL"
                                   style="max-width: 150px;">
                            @error('editingDividerData.combination_'.$i) <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    @endfor
                </div>
                @error('editingDividerData.combination') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Supplier Price -->
            <div class="mt-6">
                <div class="space-y-3">
                    <div class="flex gap-4">
                        <div class="flex items-center gap-3" style="width: calc(25% - 0.75rem);">
                            <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Supplier Price</label>
                            <div class="flex-1">
                                <input type="number"
                                       step="0.01"
                                       wire:model="editingDividerData.supplier_price"
                                       class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="0.00">
                                @error('editingDividerData.supplier_price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex justify-end gap-3">
                <button wire:click="closeEditDividerModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 border border-transparent rounded-md hover:bg-gray-300">
                    Cancel
                </button>
                <button wire:click="saveDivider({{ $editingDividerIndex }})"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

