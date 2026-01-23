<!-- Create Job Order Modal -->
@if($showModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click.self="closeModal">
        <div class="relative top-20 mx-auto p-5 border w-11/12 max-w-4xl shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <!-- Modal Header -->
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">
                        Create New Job Order
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Flash Messages inside Modal -->
                @if (session()->has('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ session('success') }}
                    </div>
                </div>
                @endif

                @if (session()->has('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        {{ session('error') }}
                    </div>
                </div>
                @endif

                @if (session()->has('info'))
                <div class="mb-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ session('info') }}
                    </div>
                </div>
                @endif

                <!-- Currency Mismatch Error -->
                @if($currencyMismatchError)
                <div class="mb-4 bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $currencyMismatchError }}
                    </div>
                </div>
                @endif

                <!-- Form -->
                <div class="mt-6">
                    <form wire:submit.prevent="saveJobOrder">
                        <div class="grid grid-cols-1 md:grid-cols-2 " style="gap: 80px !important;">
                            <!-- Left Column -->
                            <div>
                                <h4 class="text-md font-semibold text-gray-800 mb-3">Job Order Information</h4>
                                <div class="space-y-2">
                                    <!-- Date -->
                                    <div class="flex items-center" style="gap: 0;">
                                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/3" style="margin-right: 2px;">Date <span class="text-red-500">*</span></label>
                                        <div class="flex-1">
                                            <input type="date"
                                                   wire:model="form.date"
                                                   class="block w-full mb-1 px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            @error('form.date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <!-- Customer -->
                                    <div class="flex items-center" style="gap: 0;">
                                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/3" style="margin-right: 2px;">Customer <span class="text-red-500">*</span></label>
                                        <div class="flex-1">
                                            <select wire:model.live="form.customer_id"
                                                    class="block w-full px-2 py-1.5 text-sm mb-1 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                                <option value="">Select Customer</option>
                                                @foreach($customers as $customer)
                                                    <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->code }})</option>
                                                @endforeach
                                            </select>
                                            @error('form.customer_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <!-- Customer Address -->
                                    <div class="flex items-center" style="gap: 0;">
                                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/3" style="margin-right: 2px;">Customer Address</label>
                                        <div class="flex-1">
                                            <div class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-900 min-h-[70px]">
                                                {{ $form['customer_address'] ?: 'Select customer first' }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Purchase Order No -->
                                    <div class="flex items-center" style="gap: 0;">
                                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/3" style="margin-right: 2px;">Purchase <br> OrderNo</label>
                                        <div class="flex-1">
                                            <input type="text"
                                                   wire:model="form.purchase_order_no"
                                                   class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                                   placeholder="Enter PO Number">
                                            @error('form.purchase_order_no') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div>
                                <h4 class="text-md font-semibold text-gray-800" style="margin-bottom: 80px;"></h4>
                                <div class="space-y-3">

                                    <!-- Supplier -->
                                    <div class="flex items-center" style="gap: 0;">
                                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/3" style="margin-right: 1px;">Supplier <span class="text-red-500">*</span></label>
                                        <div class="flex-1">
                                            <select wire:model.live="form.supplier_id"
                                                    class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                                <option value="">Select Supplier</option>
                                                @foreach($suppliers as $supplier)
                                                    <option value="{{ $supplier->id }}">{{ $supplier->name }} ({{ $supplier->code }})</option>
                                                @endforeach
                                            </select>
                                            @error('form.supplier_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <!-- Supplier Address -->
                                    <div class="flex items-center" style="gap: 0;">
                                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/3" style="margin-right: 1px;">Supplier Address</label>
                                        <div class="flex-1">
                                            <div class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm bg-gray-100 text-gray-900 min-h-[70px]">
                                                {{ $form['supplier_address'] ?: 'Select supplier first' }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- PO Date -->
                                    <div class="flex items-start" style="gap: 0;">
                                        <label class="block text-sm font-medium text-gray-700 mb-1 w-1/3" style="margin-right: 1px; padding-top: 8px;">PO Date <span class="text-red-500">*</span></label>
                                        <div class="flex-1">
                                            <input type="date"
                                                   wire:model="form.po_date"
                                                   class="block w-full px-2 py-1.5 text-sm border {{ $errors->has('form.po_date') ? 'border-red-500' : 'border-gray-300' }} rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            @error('form.po_date')
                                                <div class="mt-1 mb-0">
                                                    <span class="text-red-500 text-sm">{{ $message }}</span>
                                                </div>
                                            @enderror
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Notes - Full Width -->
                        <div class="mt-3">
                            <div class="flex items-center" style="gap: 0;">
                                <label class="block text-sm font-medium text-gray-700 mb-1 w-1/6" style="margin-left: -10px;">Notes</label>
                                <div class="flex-1">
                                    <textarea wire:model="form.notes"
                                              rows="3"
                                              class="block w-full px-2 py-1.5 text-sm border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                              placeholder="Enter any additional notes or remarks..."></textarea>
                                    @error('form.notes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end space-x-4 mt-6">
                            <button type="button"
                                    wire:click="closeModal"
                                    class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancel
                            </button>
                            <button type="submit"
                                    @if($currencyMismatchError) disabled @endif
                                    class="{{ $currencyMismatchError ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-500 hover:bg-blue-700' }} text-white font-bold py-2 px-4 rounded"
                                    @if($currencyMismatchError) title="{{ $currencyMismatchError }}" @endif>
                                Create Job Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
