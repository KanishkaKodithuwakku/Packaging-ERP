<div class="space-y-6">
    <!-- Add Divider Form -->
    <div class="bg-gray-50 p-4 rounded-md">
        <h4 class="text-lg font-medium text-gray-900 mb-4">Add New Divider</h4>

          <!-- Divider Specifications -->
          <div class="space-y-3">
            <!-- Row 1: FSC CLAIM, Quantity, Unit, PLY -->
            <div class="flex gap-4">
                <div class="flex items-center gap-3 flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">FSC Claim <span class="text-red-500">*</span></label>
                    <div class="flex-1">
                        <select wire:model="dividerForm.fsc_claim"
                                class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="100%">100%</option>
                            <option value="MIX">MIX</option>
                        </select>
                        @error('dividerForm.fsc_claim') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3 flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Quantity <span class="text-red-500">*</span></label>
                    <div class="flex-1">
                        <input type="number"
                               wire:model="dividerForm.quantity"
                               class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                               placeholder="1000">
                        @error('dividerForm.quantity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3 flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Unit <span class="text-red-500">*</span></label>
                    <div class="flex-1">
                        <select wire:model="dividerForm.unit"
                                class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="CM">CM</option>
                            <option value="MM">MM</option>
                            <option value="INCHES">INCHES</option>
                        </select>
                        @error('dividerForm.unit') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center gap-3 flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Ply <span class="text-red-500">*</span></label>
                    <div class="flex-1">
                        <select wire:model.live="dividerForm.ply"
                                class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="3">3</option>
                            <option value="5">5</option>
                            <option value="7">7</option>
                        </select>
                        @error('dividerForm.ply') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Combination Parameters -->
        <div class="mb-6">
            <h5 class="text-md font-medium text-gray-700 mb-2">Combination <span class="text-red-500">*</span></h5>
            <!-- Responsive Grid - up to 7 inputs -->
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-7 gap-0.5">
                @for($i = 1; $i <= 7; $i++)
                    <div @if(($dividerForm['ply'] == '3' && $i > 3) || ($dividerForm['ply'] == '5' && $i > 5) || ($dividerForm['ply'] == '7' && $i > 7) || (!$dividerForm['ply'] && $i > 3)) style="display: none;" @endif>
                        <input type="text"
                               wire:model="dividerForm.combination_{{ $i }}"
                               maxlength="6"
                               class="w-full px-1 py-2 text-xs border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                               placeholder="e.g., 140KL"
                               style="max-width: 150px;">
                        @error('dividerForm.combination_'.$i) <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                @endfor
            </div>
            @error('dividerForm.combination') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>



        <!-- Supplier Price -->
        <div class="mt-6">
            <div class="space-y-3">
                <div class="flex gap-4">
                    <div class="flex items-center gap-3" style="width: calc(25% - 0.75rem);">
                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/4">Supplier Price <span class="text-red-500">*</span></label>
                        <div class="flex-1">
                            <input type="number"
                                   step="0.01"
                                   wire:model="dividerForm.supplier_price"
                                   class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="0.00">
                            @error('dividerForm.supplier_price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Divider Button -->
        <div class="mt-6 flex justify-end">
            <button wire:click="addDivider"
                    class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700">
                Add Divider
            </button>
        </div>
    </div>

    <!-- Added Dividers List -->
    @if(count($dividers) > 0)
        <div class="bg-white border rounded-md">
            <div class="px-4 py-3 border-b border-gray-200">
                <h4 class="text-lg font-medium text-gray-900">Added Dividers ({{ count($dividers) }})</h4>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Combination</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">PLY</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">FSC Claim</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($dividers as $index => $divider)
                            <tr>
                                <td class="px-3 py-2 text-sm text-gray-900">
                                    @php
                                        $combination = [];
                                        for ($i = 1; $i <= 7; $i++) {
                                            if (!empty($divider["combination_{$i}"])) {
                                                $combination[] = $divider["combination_{$i}"];
                                            }
                                        }
                                    @endphp
                                    {{ implode(' / ', $combination) }}
                                </td>
                                <td class="px-3 py-2 text-sm text-gray-900">{{ $divider['ply'] }}</td>
                                <td class="px-3 py-2 text-sm text-gray-900">{{ $divider['quantity'] }}</td>
                                <td class="px-3 py-2 text-sm text-gray-900">{{ $divider['unit'] }}</td>
                                <td class="px-3 py-2 text-sm text-gray-900">{{ $divider['fsc_claim'] }}</td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        <button wire:click="editDivider({{ $index }})"
                                                class="text-blue-600 hover:text-blue-900"
                                                title="Edit Divider">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <button wire:click="removeDivider({{ $index }})"
                                                class="text-red-600 hover:text-red-900"
                                                title="Remove Divider">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif


</div>
