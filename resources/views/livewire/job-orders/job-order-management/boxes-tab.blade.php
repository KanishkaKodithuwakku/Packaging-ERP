<div class="space-y-6" style="width: 100%; max-width: 100%;">
    <!-- Add Box Form -->
    <div class="bg-gray-50 p-3 rounded-md" style="width: 100%; max-width: 100%;">
        <h4 class="text-lg font-medium text-gray-900 mb-4">Add New Box</h4>

        <!-- Sub-tab Navigation for Box Form -->
        <div class="border-b border-gray-200 mb-4">
            <nav class="-mb-px flex space-x-4">
                <button wire:click="setBoxFormTab('basic')"
                        type="button"
                        class="py-2 px-1 border-b-2 font-medium text-sm {{ $boxFormTab === 'basic' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    1. Basic Information
                </button>
                <button wire:click="setBoxFormTab('dimensions')"
                        type="button"
                        class="py-2 px-1 border-b-2 font-medium text-sm {{ $boxFormTab === 'dimensions' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    2. Box Dimensions
                </button>
                <button wire:click="setBoxFormTab('material')"
                        type="button"
                        class="py-2 px-1 border-b-2 font-medium text-sm {{ $boxFormTab === 'material' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    3. Material & Combination
                </button>
                <button wire:click="setBoxFormTab('calculated')"
                        type="button"
                        class="py-2 px-1 border-b-2 font-medium text-sm {{ $boxFormTab === 'calculated' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    4. Calculated Fields
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="mt-4">
            @if($boxFormTab === 'basic')
                <!-- Tab 1: Basic Information -->
                <div class="mb-6">
                    <h5 class="text-md font-medium text-gray-700 mb-3">Basic Information</h5>
                    <table class="border-collapse" style="table-layout: fixed; width: 100%; max-width: 100%;">
                        <tbody>
                            <tr>
                                <td class="px-2 py-2 text-sm font-medium text-gray-700 border border-gray-300" style="width: 40%;">Order Qty <span class="text-red-500">*</span></td>
                                <td class="px-2 py-2 border border-gray-300">
                                    <input type="number"
                                           id="box-form-order-qty"
                                           wire:model="boxForm.order_qty"
                                           wire:change="calculateBoardQty"
                                           class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="1000">
                                    @error('boxForm.order_qty') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-2 text-sm font-medium text-gray-700 border border-gray-300" style="width: 40%;">Selling Price <span class="text-red-500">*</span></td>
                                <td class="px-2 py-2 border border-gray-300">
                                    <input type="number"
                                           step="0.01"
                                           wire:model="boxForm.selling_price"
                                           class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0.00">
                                    @error('boxForm.selling_price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-2 text-sm font-medium text-gray-700 border border-gray-300" style="width: 40%;">Activity <span class="text-red-500">*</span></td>
                                <td class="px-2 py-2 border border-gray-300">
                                    <input type="text"
                                           wire:model="boxForm.activity"
                                           class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="Activity type">
                                    @error('boxForm.activity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-2 text-sm font-medium text-gray-700 border border-gray-300" style="width: 40%;">Printing Instruction <span class="text-red-500">*</span></td>
                                <td class="px-2 py-2 border border-gray-300">
                                    <select wire:model="boxForm.printing_instruction"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select...</option>
                                        <option value="Printed">Printed</option>
                                        <option value="UnPrinted">UnPrinted</option>
                                    </select>
                                    @error('boxForm.printing_instruction') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-2 text-sm font-medium text-gray-700 border border-gray-300" style="width: 40%;">No of Colours <span class="text-red-500">*</span></td>
                                <td class="px-2 py-2 border border-gray-300">
                                    <input type="number"
                                           wire:model="boxForm.no_of_colours"
                                           min="0"
                                           class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0">
                                    @error('boxForm.no_of_colours') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-2 text-sm font-medium text-gray-700 border border-gray-300" style="width: 40%;">Stitched/Glued <span class="text-red-500">*</span></td>
                                <td class="px-2 py-2 border border-gray-300">
                                    <select wire:model="boxForm.stitched_glued"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select...</option>
                                        <option value="None">None</option>
                                        <option value="Stitched">Stitched</option>
                                        <option value="Glued">Glued</option>
                                    </select>
                                    @error('boxForm.stitched_glued') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-2 text-sm font-medium text-gray-700 border border-gray-300" style="width: 40%;">Sample Available <span class="text-red-500">*</span></td>
                                <td class="px-2 py-2 border border-gray-300">
                                    <select wire:model="boxForm.sample_available"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select...</option>
                                        <option value="No">No</option>
                                        <option value="Yes">Yes</option>
                                    </select>
                                    @error('boxForm.sample_available') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-2 text-sm font-medium text-gray-700 border border-gray-300" style="width: 40%;">Sample Attached <span class="text-red-500">*</span></td>
                                <td class="px-2 py-2 border border-gray-300">
                                    <select wire:model="boxForm.sample_attached"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select...</option>
                                        <option value="No">No</option>
                                        <option value="Yes">Yes</option>
                                    </select>
                                    @error('boxForm.sample_attached') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            @elseif($boxFormTab === 'dimensions')
                <!-- Tab 2: Box Dimensions -->
                <div class="mb-6">
                    <h5 class="text-md font-medium text-gray-700 mb-3">Box Dimensions</h5>
                    <table class="border-collapse" style="table-layout: fixed; width: 100%; max-width: 100%;">
                        <tbody>
                            <tr>
                                <td class="px-2 py-2 text-sm font-medium text-gray-700 border border-gray-300" style="width: 40%;">Length (L) <span class="text-red-500">*</span></td>
                                <td class="px-2 py-2 border border-gray-300">
                                    <input type="number"
                                           id="box-form-length"
                                           step="0.01"
                                           wire:model.live.debounce.500ms="boxForm.length"
                                           class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0.00">
                                    @error('boxForm.length') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-2 text-sm font-medium text-gray-700 border border-gray-300" style="width: 40%;">Width (W) <span class="text-red-500">*</span></td>
                                <td class="px-2 py-2 border border-gray-300">
                                    <input type="number"
                                           step="0.01"
                                           wire:model.live.debounce.500ms="boxForm.width"
                                           class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0.00">
                                    @error('boxForm.width') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-2 text-sm font-medium text-gray-700 border border-gray-300" style="width: 40%;">Height (H) <span class="text-red-500">*</span></td>
                                <td class="px-2 py-2 border border-gray-300">
                                    <input type="number"
                                           step="0.01"
                                           wire:model.live.debounce.500ms="boxForm.height"
                                           class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0.00">
                                    @error('boxForm.height') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-2 text-sm font-medium text-gray-700 border border-gray-300" style="width: 40%;">Unit <span class="text-red-500">*</span></td>
                                <td class="px-2 py-2 border border-gray-300">
                                    <select wire:model.live="boxForm.unit"
                                            wire:change="calculateDimensions"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="CM">CM</option>
                                        <option value="MM">MM</option>
                                        <option value="INCHES">INCHES</option>
                                    </select>
                                    @error('boxForm.unit') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-2 text-sm font-medium text-gray-700 border border-gray-300" style="width: 40%;">Type <span class="text-red-500">*</span></td>
                                <td class="px-2 py-2 border border-gray-300">
                                    <select wire:model.live="boxForm.dimension_type"
                                            wire:change="calculateDimensions"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="INTERNAL">INTERNAL</option>
                                        <option value="EXTERNAL">EXTERNAL</option>
                                    </select>
                                    @error('boxForm.dimension_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            @elseif($boxFormTab === 'material')
                <!-- Tab 3: Material Specifications & Combination Parameters -->
                <div class="mb-6">
                    <h5 class="text-md font-medium text-gray-700 mb-3">Material Specifications</h5>
                    <table class="border-collapse" style="table-layout: fixed; width: 100%; max-width: 100%;">
                        <tbody>
                            <tr>
                                <td class="px-3 py-2 text-sm font-medium text-gray-700 border border-gray-300 w-1/3">Top Liner <span class="text-red-500">*</span></td>
                                <td class="px-2 py-2 border border-gray-300">
                                    <select wire:model.live="boxForm.top_liner"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select...</option>
                                        <option value="WHITE">WHITE</option>
                                        <option value="BROWN">BROWN</option>
                                    </select>
                                    @error('boxForm.top_liner') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-2 text-sm font-medium text-gray-700 border border-gray-300" style="width: 40%;">FSC Claim <span class="text-red-500">*</span></td>
                                <td class="px-2 py-2 border border-gray-300">
                                    <select wire:model="boxForm.fsc_claim"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="No Claim">No Claim</option>
                                        <option value="100%">100%</option>
                                        <option value="MIX">MIX</option>
                                    </select>
                                    @error('boxForm.fsc_claim') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-2 text-sm font-medium text-gray-700 border border-gray-300" style="width: 40%;">Flute <span class="text-red-500">*</span></td>
                                <td class="px-2 py-2 border border-gray-300">
                                    <select wire:model="boxForm.flute"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select...</option>
                                        <option value="B">B FLUTE</option>
                                        <option value="C">C FLUTE</option>
                                        <option value="E">B/C FLUTE</option>
                                    </select>
                                    @error('boxForm.flute') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-2 text-sm font-medium text-gray-700 border border-gray-300" style="width: 40%;">Ply <span class="text-red-500">*</span></td>
                                <td class="px-2 py-2 border border-gray-300">
                                    <select wire:model.live="boxForm.ply"
                                            class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                        <option value="3">3</option>
                                        <option value="5">5</option>
                                        <option value="7">7</option>
                                    </select>
                                    @error('boxForm.ply') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Combination Fields (Dynamic based on PLY) -->
                <div class="mt-6">
                    <h5 class="text-md font-medium text-gray-700 mb-2">Combination Parameters <span class="text-red-500">*</span></h5>
                    <!-- Responsive Grid - up to 7 inputs -->
                    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-7 gap-0.5">
                        @for($i = 1; $i <= 7; $i++)
                            <div @if(($boxForm['ply'] == '3' && $i > 3) || ($boxForm['ply'] == '5' && $i > 5) || ($boxForm['ply'] == '7' && $i > 7) || (!$boxForm['ply'] && $i > 3)) style="display: none;" @endif>
                                <input type="text"
                                       wire:model="boxForm.combination_{{ $i }}"
                                       maxlength="6"
                                       class="w-full px-1 py-2 text-xs border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="e.g., 140KL"
                                       style="max-width: 150px;">
                                @error('boxForm.combination_'.$i) <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @endfor
                    </div>
                    @error('boxForm.combination') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

            @elseif($boxFormTab === 'calculated')
                <!-- Tab 4: Calculated Fields & Notes -->
                <div class="mb-6">
                    <div class="flex justify-between items-center mb-3">
                        <h5 class="text-md font-medium text-gray-700">Calculated Fields</h5>
                        <div class="flex flex-col items-end gap-2">
                            @if (session()->has('success'))
                                <div class="bg-green-100 border border-green-400 text-green-700 px-3 py-1.5 rounded text-xs">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ session('success') }}
                                    </div>
                                </div>
                            @endif
                            @if (session()->has('error'))
                                <div class="bg-red-100 border border-red-400 text-red-700 px-3 py-1.5 rounded text-xs">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ session('error') }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <table class="border-collapse" style="table-layout: fixed; width: 100%; max-width: 100%;">
                        <tbody>
                            <tr>
                                <td class="px-3 py-2 text-sm font-medium text-gray-700 border border-gray-300 w-1/3">Reel Size <span class="text-red-500">*</span></td>
                                <td class="px-2 py-2 border border-gray-300">
                                    <div class="flex items-center gap-2">
                                        <input type="number"
                                               step="0.01"
                                               wire:model.live="boxForm.reel_size"
                                               class="block flex-1 px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="{{ $calculatedReelSize ? number_format($calculatedReelSize, 2) . ' (calculated)' : '0.00' }}">
                                        <span class="text-xs text-gray-500 whitespace-nowrap">inches</span>
                                    </div>
                                    @if($calculatedReelSize && empty($boxForm['reel_size'] ?? ''))
                                        <p class="text-xs text-blue-600 mt-1">Caculated: {{ number_format($calculatedReelSize, 2) }}</p>
                                    @endif
                                    @error('boxForm.reel_size') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-2 text-sm font-medium text-gray-700 border border-gray-300" style="width: 40%;">Cut Size <span class="text-red-500">*</span></td>
                                <td class="px-2 py-2 border border-gray-300">
                                    <div class="flex items-center gap-2">
                                        <input type="number"
                                               step="0.01"
                                               wire:model.live="boxForm.cut_size"
                                               class="block flex-1 px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                               placeholder="{{ $calculatedCutSize ? number_format($calculatedCutSize, 2) . ' (calculated)' : '0.00' }}">
                                        <span class="text-xs text-gray-500 whitespace-nowrap">inches</span>
                                    </div>
                                    @if($calculatedCutSize && empty($boxForm['cut_size'] ?? ''))
                                        <p class="text-xs text-blue-600 mt-1">Calculated: {{ number_format($calculatedCutSize, 2) }}</p>
                                    @endif
                                    @error('boxForm.cut_size') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-2 text-sm font-medium text-gray-700 border border-gray-300" style="width: 40%;">No of Ups <span class="text-red-500">*</span></td>
                                <td class="px-2 py-2 border border-gray-300">
                                    <input type="number"
                                           wire:model="boxForm.no_of_ups"
                                           wire:change="calculateBoardQty"
                                           class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0">
                                    @error('boxForm.no_of_ups') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-2 text-sm font-medium text-gray-700 border border-gray-300" style="width: 40%;">Board Qty <span class="text-red-500">*</span></td>
                                <td class="px-2 py-2 border border-gray-300">
                                    <input type="number"
                                           step="0.01"
                                           wire:model.live="boxForm.board_qty"
                                           class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="{{ $calculatedBoardQty ? $calculatedBoardQty . ' (calculated)' : '0' }}">
                                    @if($calculatedBoardQty && empty($boxForm['board_qty'] ?? ''))
                                        <p class="text-xs text-blue-600 mt-1">Calculated: {{ $calculatedBoardQty }}</p>
                                    @endif
                                    @error('boxForm.board_qty') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </td>
                            </tr>
                            <tr>
                                <td class="px-2 py-2 text-sm font-medium text-gray-700 border border-gray-300" style="width: 40%;">Supplier Price <span class="text-red-500">*</span></td>
                                <td class="px-2 py-2 border border-gray-300">
                                    <input type="number"
                                           step="0.01"
                                           wire:model="boxForm.supplier_price"
                                           class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0.00">
                                    @error('boxForm.supplier_price') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Notes -->
                <div class="mt-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea wire:model="boxForm.notes"
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Additional notes..."></textarea>
                    @error('boxForm.notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            @endif
        </div>

        <!-- Navigation Buttons -->
        <div class="mt-6 flex justify-between items-center">
            <div class="flex gap-2">
                @if($boxFormTab !== 'basic')
                    <button wire:click="setBoxFormTab('{{ $boxFormTab === 'dimensions' ? 'basic' : ($boxFormTab === 'material' ? 'dimensions' : 'material') }}')"
                            type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 border border-transparent rounded-md hover:bg-gray-300">
                        ← Previous
                    </button>
                @endif
            </div>
            <div class="flex gap-2">
                @if($boxFormTab !== 'calculated')
                    <button wire:click="setBoxFormTab('{{ $boxFormTab === 'basic' ? 'dimensions' : ($boxFormTab === 'dimensions' ? 'material' : 'calculated') }}')"
                            type="button"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700">
                        Next →
                    </button>
                @endif
                @if($boxFormTab === 'calculated')
                    <button wire:click="addBox"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700">
                        Save
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
