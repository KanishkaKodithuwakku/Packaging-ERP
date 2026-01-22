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
                    <button onclick="showConfirmModal()"
                            type="button"
                            class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Confirm Invoice
                    </button>
                @else
                    <span class="px-4 py-2 bg-purple-100 text-purple-800 rounded-md text-sm font-medium flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Confirmed ({{ $invoice->confirmed_at->format('M d, Y') }})
                    </span>
                @endif
                <button type="button"
                        onclick="printInvoice({{ $invoice->id }})"
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

        @php
            // Get customer currency symbol for display
            $customerCurrency = ($invoice->customer && $invoice->customer->currency) ? $invoice->customer->currency : 'LKR';
            $currencySymbol = match($customerCurrency) {
                'LKR' => 'Rs.',
                'USD' => '$',
                'EUR' => '€',
                'GBP' => '£',
                'INR' => '₹',
                default => $customerCurrency . ' '
            };
        @endphp

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Material Code</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Quantity</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Unit Price ({{ $currencySymbol }})</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Line Total ({{ $currencySymbol }})</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($invoice->items as $index => $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->description }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->material_code }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">{{ number_format($item->quantity, 0) }}</td>
                            @php
                                // Determine customer type and tax mode
                                $customerType = $invoice->customer?->customer_type ?? 'non_tax_customer';
                                $isNonTaxCustomerWithTax = ($customerType === 'non_tax_customer') && ($invoice->customer?->taxes->isNotEmpty() ?? false);
                                $isTaxCustomer = ($customerType === 'tax_customer');
                                
                                // Get VAT rate if applicable
                                $hasVatTax = $invoice->customer?->taxes->contains(function($tax) {
                                    return strtoupper($tax->abbreviation) === 'VAT' && (float) $tax->percentage === 15.00;
                                });
                                $vatRate = $hasVatTax ? 15.00 : 0.00;
                                $vatFactor = $vatRate > 0 ? (1 + ($vatRate / 100)) : 1;
                                
                                // For Non Tax + With Tax: display VAT-inclusive price
                                // For Tax Customer: display base price
                                if (!$invoice->isConfirmed()) {
                                    $displayUnitPrice = (float) ($itemPrices[$item->id] ?? $item->unit_price);
                                } else {
                                    $basePrice = (float) $item->unit_price;
                                    $displayUnitPrice = $isNonTaxCustomerWithTax 
                                        ? ($basePrice * $vatFactor)
                                        : $basePrice;
                                }
                                
                                $lineTotal = $displayUnitPrice * (float) $item->quantity;
                            @endphp
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                @if(!$invoice->isConfirmed())
                                    <div class="flex items-center justify-end">
                                        <span class="text-gray-500 mr-1">{{ $currencySymbol }}</span>
                                        <input type="number" 
                                               wire:model.live.debounce.500ms="itemPrices.{{ $item->id }}"
                                               step="0.01" 
                                               min="0"
                                               class="w-24 px-2 py-1 border border-gray-300 rounded-md text-sm text-right focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    </div>
                                @else
                                    <span class="text-gray-900">{{ $currencySymbol }} {{ number_format($displayUnitPrice, 2) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                                {{ $currencySymbol }} {{ number_format($lineTotal, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    @php
                        // Calculate subtotal from displayed line totals (for both confirmed and unconfirmed)
                        $calculatedSubtotalFromItems = 0;
                        $customerType = $invoice->customer?->customer_type ?? 'non_tax_customer';
                        $isNonTaxCustomerWithTax = ($customerType === 'non_tax_customer') && ($invoice->customer?->taxes->isNotEmpty() ?? false);
                        $hasVatTax = $invoice->customer?->taxes->contains(function($tax) {
                            return strtoupper($tax->abbreviation) === 'VAT' && (float) $tax->percentage === 15.00;
                        });
                        $vatRate = $hasVatTax ? 15.00 : 0.00;
                        $vatFactor = $vatRate > 0 ? (1 + ($vatRate / 100)) : 1;
                        
                        foreach ($invoice->items as $item) {
                            $basePrice = (float) $item->unit_price;
                            $displayUnitPrice = $isNonTaxCustomerWithTax 
                                ? ($basePrice * $vatFactor)
                                : $basePrice;
                            $lineTotal = $displayUnitPrice * (float) $item->quantity;
                            $calculatedSubtotalFromItems += $lineTotal;
                        }
                        
                        $taxLines = $invoice->getTaxLines();
                        $taxTotal = collect($taxLines)->sum(fn ($l) => (float) ($l['amount'] ?? 0));
                        
                        // For Non Tax + With Tax: Total = Subtotal - Discount (VAT already included)
                        // For Tax Customer: Total = Subtotal - Discount + Tax
                        if ($isNonTaxCustomerWithTax) {
                            $calculatedTotal = max(0, $calculatedSubtotalFromItems - (float) $invoice->discount_amount);
                        } else {
                            $confirmedNet = max(0, $calculatedSubtotalFromItems - (float) $invoice->discount_amount);
                            $calculatedTotal = $confirmedNet + (float) $taxTotal;
                        }
                    @endphp
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-right text-sm font-medium text-gray-700">Subtotal:</td>
                        <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">
                            @if(!$invoice->isConfirmed())
                                {{ $currencySymbol }} {{ number_format($this->calculatedSubtotal, 2) }}
                            @else
                                {{ $currencySymbol }} {{ number_format($calculatedSubtotalFromItems, 2) }}
                            @endif
                        </td>
                    </tr>
                    @if($invoice->discount_amount > 0)
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-right text-sm font-medium text-gray-700">Discount:</td>
                        <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">-{{ $currencySymbol }} {{ number_format($invoice->discount_amount, 2) }}</td>
                    </tr>
                    @endif

                    @foreach($taxLines as $line)
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-right text-sm font-medium text-gray-700">
                                {{ $line['label'] }} ({{ number_format((float) $line['percentage'], 2) }}%):
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium text-gray-900">+{{ $currencySymbol }} {{ number_format((float) $line['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-right text-sm font-bold text-gray-900">Total Amount:</td>
                        <td class="px-6 py-4 text-right text-sm font-bold text-gray-900">
                            @if(!$invoice->isConfirmed())
                                {{ $currencySymbol }} {{ number_format($this->calculatedTotal, 2) }}
                            @else
                                {{ $currencySymbol }} {{ number_format($calculatedTotal, 2) }}
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

    <!-- Confirm Invoice Modal -->
    <div id="confirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="display: none;">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Confirm Invoice</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Are you sure you want to confirm this invoice? Once confirmed, it cannot be edited.
                </p>
                <div class="flex justify-end space-x-3">
                    <button type="button"
                            onclick="closeConfirmModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
                        Cancel
                    </button>
                    <button type="button"
                            wire:click="confirmInvoice"
                            wire:loading.attr="disabled"
                            wire:target="confirmInvoice"
                            class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors flex items-center disabled:bg-purple-400 disabled:cursor-not-allowed">
                        <svg wire:loading.remove wire:target="confirmInvoice" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <svg wire:loading wire:target="confirmInvoice" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove wire:target="confirmInvoice">Confirm</span>
                        <span wire:loading wire:target="confirmInvoice">Confirming...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showConfirmModal() {
            const modal = document.getElementById('confirmModal');
            if (modal) {
                modal.style.display = 'flex';
                modal.style.alignItems = 'center';
                modal.style.justifyContent = 'center';
            }
        }

        function closeConfirmModal() {
            const modal = document.getElementById('confirmModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        window.addEventListener('showConfirmModal', showConfirmModal);
        window.addEventListener('closeConfirmModal', closeConfirmModal);
        
        // Also listen to Livewire events
        document.addEventListener('livewire:init', () => {
            Livewire.on('closeConfirmModal', () => {
                closeConfirmModal();
            });
        });
        
        function printInvoice(invoiceId) {
            // Construct the print URL
            const baseUrl = '{{ url("/") }}';
            const printUrl = baseUrl + '/invoices/' + invoiceId + '/print';
            
            // Create a hidden iframe to load the print page
            const iframe = document.createElement('iframe');
            iframe.style.position = 'fixed';
            iframe.style.right = '0';
            iframe.style.bottom = '0';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = '0';
            iframe.src = printUrl;
            
            // Append to body
            document.body.appendChild(iframe);
            
            // Wait for iframe to load, then trigger print
            iframe.onload = function() {
                setTimeout(function() {
                    try {
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                    } catch (e) {
                        // If cross-origin or other issue, fallback to new window
                        window.open(printUrl, '_blank', 'width=800,height=600');
                    }
                }, 500);
            };
            
            // Clean up iframe after print (with delay to allow print dialog to open)
            setTimeout(function() {
                if (iframe && iframe.parentNode) {
                    iframe.parentNode.removeChild(iframe);
                }
            }, 2000);
        }
    </script>


</div>
