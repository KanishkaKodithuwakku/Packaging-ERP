<div>
    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="font-medium">{{ session('success') }}</div>
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
                    <div class="font-medium">{{ session('error') }}</div>
                </div>
            </div>
        </div>
    @endif

    <div class="mb-6">
        <div class="flex justify-between items-start">
            <div>
                <a wire:navigate href="{{ route('invoices') }}"
                    class="inline-flex items-center text-gray-600 hover:text-gray-900">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Invoices
                </a>
                <h1 class="text-3xl font-bold text-gray-900 mt-4">Invoice Details</h1>
                <p class="text-gray-600 mt-1">Invoice Number: {{ $invoice->invoice_number }}</p>
            </div>
            <div class="flex space-x-3">
                @if(!$invoice->isConfirmed())
                    <button wire:click="saveInvoice"
                            wire:loading.attr="disabled"
                            wire:target="saveInvoice"
                            class="bg-green-600 hover:bg-green-700 disabled:bg-green-400 disabled:cursor-not-allowed text-white px-4 py-2 rounded-md text-sm font-medium flex items-center">
                        <svg wire:loading.remove wire:target="saveInvoice" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <svg wire:loading wire:target="saveInvoice" class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="saveInvoice">Save Invoice</span>
                        <span wire:loading wire:target="saveInvoice">Saving...</span>
                    </button>
                    <button wire:click="confirmInvoice"
                            wire:loading.attr="disabled"
                            wire:target="confirmInvoice"
                            onclick="return confirm('Are you sure you want to confirm this invoice? Once confirmed, it cannot be edited.')"
                            class="bg-purple-600 hover:bg-purple-700 disabled:bg-purple-400 disabled:cursor-not-allowed text-white px-4 py-2 rounded-md text-sm font-medium flex items-center">
                        <svg wire:loading.remove wire:target="confirmInvoice" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <svg wire:loading wire:target="confirmInvoice" class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="confirmInvoice">Confirm Invoice</span>
                        <span wire:loading wire:target="confirmInvoice">Confirming...</span>
                    </button>
                @else
                    <span class="px-4 py-2 bg-purple-100 text-purple-800 rounded-md text-sm font-medium flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Confirmed ({{ $invoice->confirmed_at->format('M d, Y') }})
                    </span>
                @endif
                <button wire:click="printInvoice"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Print Invoice
                </button>
            </div>
        </div>
    </div>

    <!-- Invoice Information -->
    <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                    {{ $invoice->customer->name ?? 'N/A' }}
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Invoice Date</label>
                <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                    {{ $invoice->invoice_date->format('M d, Y') }}
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <div class="w-full px-3 py-2">
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                        {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $invoice->status === 'sent' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $invoice->status === 'draft' ? 'bg-gray-100 text-gray-800' : '' }}
                        {{ $invoice->status === 'overdue' ? 'bg-red-100 text-red-800' : '' }}
                        {{ $invoice->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ ucfirst($invoice->status) }}
                    </span>
                    @if($invoice->isConfirmed())
                        <span class="ml-2 px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                            Confirmed
                        </span>
                    @endif
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Note</label>
                <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                    <a wire:navigate href="{{ route('delivery-note-detail', $invoice->delivery_note_id) }}" 
                       class="text-blue-600 hover:text-blue-800">
                        {{ $invoice->deliveryNote->dn_number ?? 'N/A' }}
                    </a>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Job Order</label>
                <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                    {{ $invoice->jobOrder->job_number ?? 'N/A' }}
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-900">
                    {{ $invoice->due_date ? $invoice->due_date->format('M d, Y') : 'N/A' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Items -->
    <div class="bg-white rounded-lg shadow-sm border">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Invoice Items</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Material Code</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Line Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($invoice->items as $index => $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->description }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->material_code }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">{{ number_format($item->quantity, 0) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                @if(!$invoice->isConfirmed())
                                    <input type="number" 
                                           wire:model.live.debounce.500ms="itemPrices.{{ $item->id }}"
                                           step="0.01" 
                                           min="0"
                                           class="w-24 px-2 py-1 border border-gray-300 rounded-md text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @else
                                    <span class="text-gray-900">{{ number_format($item->unit_price, 2) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                @if(!$invoice->isConfirmed())
                                    @php
                                        $currentPrice = $itemPrices[$item->id] ?? $item->unit_price;
                                        $currentLineTotal = $currentPrice * (float) $item->quantity;
                                    @endphp
                                    {{ number_format($currentLineTotal, 2) }}
                                @else
                                    {{ number_format($item->line_total, 2) }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-right text-sm font-medium text-gray-700">Subtotal:</td>
                        <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                            @if(!$invoice->isConfirmed())
                                {{ number_format($this->calculatedSubtotal, 2) }}
                            @else
                                {{ number_format($invoice->subtotal, 2) }}
                            @endif
                        </td>
                    </tr>
                    @if($invoice->discount_amount > 0)
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-right text-sm font-medium text-gray-700">Discount:</td>
                        <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">-{{ number_format($invoice->discount_amount, 2) }}</td>
                    </tr>
                    @endif
                    @if($invoice->tax_amount > 0)
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-right text-sm font-medium text-gray-700">Tax:</td>
                        <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">{{ number_format($invoice->tax_amount, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-right text-sm font-bold text-gray-900">Total Amount:</td>
                        <td class="px-6 py-4 text-right text-sm font-bold text-gray-900">
                            @if(!$invoice->isConfirmed())
                                {{ number_format($this->calculatedTotal, 2) }}
                            @else
                                {{ number_format($invoice->total_amount, 2) }}
                            @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    @if($invoice->notes)
    <div class="bg-white rounded-lg shadow-sm border p-6 mt-6">
        <h3 class="text-lg font-medium text-gray-900 mb-2">Notes</h3>
        <p class="text-gray-600">{{ $invoice->notes }}</p>
    </div>
    @endif

    @if($invoice->terms)
    <div class="bg-white rounded-lg shadow-sm border p-6 mt-6">
        <h3 class="text-lg font-medium text-gray-900 mb-2">Terms & Conditions</h3>
        <p class="text-gray-600">{{ $invoice->terms }}</p>
    </div>
    @endif

    <script>
        function printInvoice() {
            window.print();
        }
    </script>
</div>
