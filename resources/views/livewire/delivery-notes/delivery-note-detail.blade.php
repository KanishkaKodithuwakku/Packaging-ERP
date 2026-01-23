<div>
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="font-medium">{!! session()->pull('success') !!}</div>
            </div>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded">
            <div class="flex items-start">
                <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                    <div class="font-medium">{{ session()->pull('error') }}</div>
                </div>
            </div>
        </div>
    @endif
    @if (session()->has('info'))
        <div class="mb-4 bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 rounded">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="font-medium">{!! session()->pull('info') !!}</div>
            </div>
        </div>
    @endif

    <div class="mb-6">
        <div class="flex justify-between items-start">
            <div>
                <a wire:navigate href="{{ route('delivery-notes-management') }}"
                    class="inline-flex items-center text-gray-600 hover:text-gray-900">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Delivery Notes
                </a>
                <h1 class="text-3xl font-bold text-gray-900 mt-4">Delivery Note Details</h1>
                <p class="text-gray-600 mt-1">DN Number: {{ $deliveryNote->dn_number }}</p>
            </div>
            @if(!$existingInvoice)
            <div class="flex space-x-3">
                <button type="button"
                        wire:click="openInvoiceModal"
                        class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 17v-6a2 2 0 012-2h8m-6 8h6a2 2 0 002-2V7a2 2 0 00-2-2h-8a2 2 0 00-2 2v10m-4 0h12"></path>
                    </svg>
                    Create Invoice
                </button>
                <button wire:click="printDeliveryNote"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print Delivery Note
                </button>
            </div>
            @endif
            @if($existingInvoice)
            <div class="flex space-x-3">
                <button type="button"
                        disabled
                        class="bg-gray-400 cursor-not-allowed text-white px-4 py-2 rounded-md text-sm font-medium flex items-center"
                        title="Invoice already created (Invoice #{{ $existingInvoice->invoice_number }})">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 17v-6a2 2 0 012-2h8m-6 8h6a2 2 0 002-2V7a2 2 0 00-2-2h-8a2 2 0 00-2 2v10m-4 0h12"></path>
                    </svg>
                    Invoice Created ({{ $existingInvoice->invoice_number }})
                </button>
                <a href="{{ route('invoice-detail', ['id' => $existingInvoice->id]) }}" 
                   wire:navigate
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    View Invoice
                </a>
                <button wire:click="printDeliveryNote"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print Delivery Note
                </button>
            </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Job Order</label>
                <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                    {{ optional($deliveryNote->jobOrder)->job_number ?? 'N/A' }}
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                    {{ optional($deliveryNote->jobOrder)->customer->name ?? 'N/A' }}
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                    {{ optional($deliveryNote->jobOrder)->customer->currency ?? 'N/A' }}
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <div class="w-full px-3 py-2">
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                        {{ $deliveryNote->status === 'dispatched' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $deliveryNote->status === 'partial' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $deliveryNote->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                        {{ $deliveryNote->status === 'invoiced' ? 'bg-purple-100 text-purple-800' : '' }}
                        {{ $deliveryNote->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ $deliveryNote->status === 'invoiced' ? 'INVOICED' : strtoupper($deliveryNote->status) }}
                        @if($deliveryNote->status === 'invoiced' && $deliveryNote->invoice)
                            <span class="ml-2 text-xs">({{ $deliveryNote->invoice->invoice_number }})</span>
                        @endif
                    </span>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dispatch Date</label>
                <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                    {{ $deliveryNote->dispatch_date->format('M d, Y') }}
                </div>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
                <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900 min-h-[60px]">
                    {{ $deliveryNote->delivery_address ?: 'N/A' }}
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Items</h3>
            </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Material Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($deliveryNote->items as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->description }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->material_code }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($item->quantity, 0) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $item->status === 'invoiced' ? 'bg-purple-100 text-purple-800' : '' }}
                                    {{ $item->status === 'dispatched' ? 'bg-green-100 text-green-800' : '' }}
                                    {{ $item->status === 'partial' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                    {{ $item->status === 'pending' ? 'bg-gray-100 text-gray-800' : '' }}">
                                    {{ $item->status === 'invoiced' ? 'INVOICED' : strtoupper($item->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Print content for full delivery note -->
    <div id="printContent" style="display: none;">
        <div style="max-width: 800px; margin: 0 auto; padding: 20px;">
            <div style="text-align: center; margin-bottom: 30px;">
                <h2 style="font-size: 28px; font-weight: bold; margin-bottom: 10px;">DELIVERY NOTE</h2>
                <p style="font-size: 18px;">DN Number: {{ $deliveryNote->dn_number }}</p>
                <p style="font-size: 14px;">Date: {{ $deliveryNote->dispatch_date->format('M d, Y') }}</p>
            </div>

            <div style="margin-bottom: 30px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <h3 style="font-weight: bold; margin-bottom: 10px;">Customer Information</h3>
                        <p><strong>Customer:</strong> {{ optional($deliveryNote->jobOrder)->customer->name ?? 'N/A' }}</p>
                        <p><strong>Currency:</strong> {{ optional($deliveryNote->jobOrder)->customer->currency ?? 'N/A' }}</p>
                        <p><strong>Job Order:</strong> {{ optional($deliveryNote->jobOrder)->job_number ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <h3 style="font-weight: bold; margin-bottom: 10px;">Delivery Information</h3>
                        <p><strong>Status:</strong> {{ ucfirst($deliveryNote->status) }}</p>
                        <p><strong>Address:</strong> {{ $deliveryNote->delivery_address ?: 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
                <thead>
                    <tr style="background-color: #f3f4f6; border-bottom: 2px solid #000;">
                        <th style="padding: 10px; text-align: left; border: 1px solid #000;">No</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #000;">Item Description</th>
                        <th style="padding: 10px; text-align: left; border: 1px solid #000;">Material Code</th>
                        <th style="padding: 10px; text-align: right; border: 1px solid #000;">Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deliveryNote->items as $index => $item)
                        <tr style="border-bottom: 1px solid #000;">
                            <td style="padding: 10px; border: 1px solid #000;">{{ $index + 1 }}</td>
                            <td style="padding: 10px; border: 1px solid #000;">{{ $item->description }}</td>
                            <td style="padding: 10px; border: 1px solid #000;">{{ $item->material_code }}</td>
                            <td style="padding: 10px; text-align: right; border: 1px solid #000;">{{ number_format($item->quantity, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($deliveryNote->notes)
            <div style="margin-bottom: 20px;">
                <h3 style="font-weight: bold; margin-bottom: 10px;">Notes</h3>
                <p>{{ $deliveryNote->notes }}</p>
            </div>
            @endif

            <div style="margin-top: 50px; border-top: 2px solid #000; padding-top: 20px;">
                <p>Prepared By: ________________</p>
                <p style="margin-top: 40px;">Received By: ________________</p>
            </div>
        </div>
    </div>

    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #printContent, #printContent * {
                visibility: visible;
            }
            #printContent {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                display: block !important;
            }
        }
    </style>


    <!-- Create Invoice Modal -->
    @if($showInvoiceModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click.self="closeInvoiceModal">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-5xl shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Create Invoice</h3>
                    <button wire:click="closeInvoiceModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="space-y-6">
                    <!-- Invoice Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Invoice Date</label>
                        <input type="date" wire:model="invoiceDate" 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500">
                        @error('invoiceDate') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Invoice Items Table -->
                    <div>
                        <h4 class="text-md font-medium text-gray-900 mb-3">Invoice Items</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Material Code</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Line Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($invoiceItems as $index => $item)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <input type="text" wire:model="invoiceItems.{{ $index }}.description" 
                                                       class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm">
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="text" wire:model="invoiceItems.{{ $index }}.material_code" 
                                                       class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm">
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="number" step="0.01" wire:model.live="invoiceItems.{{ $index }}.quantity" 
                                                       wire:change="updateInvoiceItemTotal({{ $index }})"
                                                       class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm text-right">
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="number" step="0.01" wire:model.live="invoiceItems.{{ $index }}.unit_price" 
                                                       wire:change="updateInvoiceItemTotal({{ $index }})"
                                                       class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm text-right">
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm font-medium">
                                                {{ number_format($this->getLineTotal($item), 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                    
                                    <!-- Extra Items -->
                                    @foreach($extraItems as $index => $item)
                                        <tr class="bg-yellow-50">
                                            <td class="px-4 py-3">
                                                <input type="text" wire:model="extraItems.{{ $index }}.description" 
                                                       placeholder="Item description"
                                                       class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm">
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="text" wire:model="extraItems.{{ $index }}.material_code" 
                                                       placeholder="Material code"
                                                       class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm">
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="number" step="0.01" wire:model.live="extraItems.{{ $index }}.quantity" 
                                                       wire:change="updateExtraItemTotal({{ $index }})"
                                                       placeholder="0"
                                                       class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm text-right">
                                            </td>
                                            <td class="px-4 py-3">
                                                <input type="number" step="0.01" wire:model.live="extraItems.{{ $index }}.unit_price" 
                                                       wire:change="updateExtraItemTotal({{ $index }})"
                                                       placeholder="0.00"
                                                       class="w-full border border-gray-300 rounded-md px-2 py-1 text-sm text-right">
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm font-medium">
                                                {{ number_format($this->getLineTotal($item), 2) }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <button type="button" wire:click="removeExtraItem({{ $index }})" 
                                                        class="text-red-600 hover:text-red-800">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="4" class="px-4 py-3 text-right text-sm font-medium">Total Amount:</td>
                                        <td class="px-4 py-3 text-right text-sm font-bold text-lg">
                                            {{ number_format($this->totalAmount, 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <!-- Add Extra Item Button -->
                        <div class="mt-3">
                            <button type="button" wire:click="addExtraItem" 
                                    class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Extra Item
                            </button>
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Remarks</label>
                        <textarea wire:model="remarks" rows="3"
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <button wire:click="closeInvoiceModal" 
                                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            Cancel
                        </button>
                        <button wire:click="createInvoice" 
                                wire:loading.attr="disabled"
                                wire:target="createInvoice"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="createInvoice">Create Invoice</span>
                            <span wire:loading wire:target="createInvoice">Creating...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @script
    <script>
        // Initialize print state
        window.deliveryNotePrintState = window.deliveryNotePrintState || {
            isPrinting: false
        };

        // Function to handle full delivery note print
        function handlePrintDeliveryNote() {
            if (window.deliveryNotePrintState.isPrinting) return;
            window.deliveryNotePrintState.isPrinting = true;
            window.print();
            setTimeout(() => { 
                window.deliveryNotePrintState.isPrinting = false; 
            }, 1000);
        }

        // Register event listeners (remove old ones first to prevent duplicates)
        const existingPrintListener = window._deliveryNotePrintListener;
        
        if (existingPrintListener) {
            window.removeEventListener('openPrintDialog', existingPrintListener);
            if (typeof Livewire !== 'undefined') {
                Livewire.off('openPrintDialog', existingPrintListener);
            }
        }

        // Create new listener
        const printListener = () => handlePrintDeliveryNote();

        // Store reference
        window._deliveryNotePrintListener = printListener;

        // Register both browser events and Livewire events
        window.addEventListener('openPrintDialog', printListener);

        // Also listen via Livewire if available
        if (typeof Livewire !== 'undefined') {
            Livewire.on('openPrintDialog', printListener);
        }
    </script>
    @endscript
</div>
