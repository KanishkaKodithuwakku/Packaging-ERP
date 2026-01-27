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
    @php
        $accountSettings = \App\Models\AccountSetting::getInstance();
        $companyName = $accountSettings->company_name ?? 'Kings Packaging (Pvt) Ltd';
        $companyAddress = $accountSettings->address ?? '182/16, Panaluwa Industrial Zone, Panaluwa, Watareka, Padukka.';
        $companyEmail = $accountSettings->email ?? 'ranasingha@kingspack.lk';
        $companyPhone = '011-4948706';
        $companyWebsite = 'www.kingspack.lk';
        $vatRegNo = '114623598-7000'; // Default VAT Reg No
    @endphp
    <div id="printContent" style="display: none;">
        <style>
            @media print {
                @page {
                    margin: 0.5cm;
                    size: A5 landscape;
                }
                body {
                    margin: 0;
                    padding: 0;
                }
                * {
                    page-break-inside: avoid;
                    page-break-after: avoid;
                    page-break-before: avoid;
                }
                html, body {
                    height: 100%;
                    overflow: hidden;
                }
            }
            .aod-print-container {
                font-family: Arial, sans-serif;
                padding: 15px;
                background: white;
                color: #000;
                display: flex;
                flex-direction: column;
                height: calc(100vh - 1cm);
                max-height: calc(100vh - 1cm);
                overflow: hidden;
                page-break-inside: avoid;
            }
            @media print {
                .aod-print-container {
                    height: calc(100vh - 1cm);
                    max-height: calc(100vh - 1cm);
                    overflow: hidden;
                    page-break-inside: avoid;
                    page-break-after: avoid;
                    page-break-before: avoid;
                }
            }
            .aod-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 15px;
                gap: 30px;
                width: 100%;
                page-break-inside: avoid;
                flex-shrink: 0;
            }
            .aod-company-section {
                flex: 1;
                text-align: left;
                display: flex;
                flex-direction: column;
                align-items: flex-start;
            }
            .aod-company-top {
                display: flex;
                flex-direction: row;
                align-items: flex-start;
                gap: 15px;
                width: 100%;
            }
            .aod-logo-area {
                width: auto;
                max-width: 120px;
                background-color: transparent;
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                justify-content: flex-start;
                padding: 0;
                flex-shrink: 0;
            }
            .aod-logo-area img {
                max-width: 100%;
                height: auto;
                object-fit: contain;
                display: block;
            }
            .aod-company-info {
                flex: 1;
                display: flex;
                flex-direction: column;
            }
            .aod-company-name {
                font-size: 24px;
                font-weight: bold;
                color: #1e3a8a;
                margin-bottom: 6px;
                line-height: 1.2;
            }
            .aod-company-slogan-image {
                margin-bottom: 8px;
                max-width: 100%;
            }
            .aod-company-slogan-image img {
                max-width: 100%;
                height: auto;
                display: block;
            }
            .aod-company-slogan {
                font-size: 12px;
                color: #000;
                margin-bottom: 4px;
                line-height: 1.4;
                font-weight: normal;
            }
            .aod-company-slogan-italic {
                font-size: 11px;
                color: #000;
                margin-bottom: 8px;
                font-style: italic;
                font-family: 'Times New Roman', serif;
                line-height: 1.4;
            }
            .aod-company-details {
                font-size: 11px;
                color: #000;
                line-height: 1.6;
                margin-top: 10px;
            }
            .aod-aod-box {
                margin-top: 15px;
                width: 100%;
            }
            .aod-title-section {
                flex: 1;
                text-align: right;
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                min-width: 300px;
            }
            .aod-title {
                font-size: 24px;
                font-weight: bold;
                color: #000;
                text-align: center;
                margin-bottom: 15px;
                width: 100%;
            }
            .aod-right-section {
                width: 100%;
                max-width: 350px;
            }
            .aod-details-section {
                display: none;
            }
            .aod-detail-box {
                background-color: white;
                border: 1px solid #000;
                border-radius: 6px;
                padding: 12px 15px;
                margin-bottom: 15px;
                text-align: left;
                font-size: 12px;
            }
            .aod-detail-label {
                font-weight: bold;
                color: #000;
                margin-bottom: 5px;
                display: block;
            }
            .aod-detail-content {
                color: #000;
                line-height: 1.6;
            }
            .aod-items-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: -15px; /*table top margin  */
                margin-bottom: 15px;
                table-layout: fixed;
                border: 1px solid #000;
                page-break-inside: avoid;
                flex: 1;
            }
            .aod-items-table th:first-child {
                width: 25%;
            }
            .aod-items-table th:nth-child(2) {
                width: 55%;
            }
            .aod-items-table th:last-child {
                width: 20%;
            }
            .aod-items-table th {
                background-color: #9ea7b6;
                color: white;
                padding: 3px 4px;
                text-align: center;
                font-size: 12px;
                font-weight: bold;
                border-left: 1px solid #000;
                border-right: 1px solid #000;
                border-top: 1px solid #000;
                border-bottom: 1px solid #000;
                text-transform: uppercase;
                line-height: 1.2;
            }
            .aod-items-table th:first-child {
                border-left: none;
            }
            .aod-items-table th:last-child {
                border-right: none;
            }
            .aod-items-table td {
                padding: 0px 4px 0px 8px;
                border-left: 1px solid #000;
                border-right: 1px solid #000;
                border-top: none;
                border-bottom: none;
                font-size: 11px;
                text-align: left;
                vertical-align: top;
                height: auto;
                line-height: 1.0;
            }
            .aod-items-table td:first-child {
                border-left: none;
            }
            .aod-items-table td:last-child {
                border-right: none;
            }
            .aod-items-table tbody tr {
                background-color: white;
                page-break-inside: avoid;
                page-break-after: avoid;
            }
            .aod-items-table tbody tr:empty {
                height: auto;
            }
            .aod-items-table tbody tr:first-child td {
                padding-top: 10px;
            }
            .aod-items-table tbody tr:last-child td {
                border-bottom: 1px solid #000;
                padding-bottom: 30px;
            }
            .aod-items-table tbody td {
                padding-top: 0px;
                padding-bottom: 0px;
            }
            .aod-footer {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-top: auto;
                padding-top: 0px;
                page-break-inside: avoid;
                flex-shrink: 0;
            }
            .aod-footer-left {
                flex: 1;
            }
            .aod-footer-right {
                flex: 1;
                text-align: right;
                display: flex;
                flex-direction: column;
                align-items: flex-end;
            }
            .aod-signature-label {
                font-size: 11px;
                color: #000;
                margin-bottom: 5px;
            }
            .aod-footer-top-label {
                font-size: 11px;
                color: #000;
                margin-bottom: 60px;
            }
            .aod-signature-line {
                border-top: 1px dotted #000;
                margin-top: 0px;
                margin-bottom: 5px;
                height: 0;
            }
            .aod-signature-line-short {
                width: 120px;
            }
            .aod-signature-line-long {
                width: 120px;
            }
            
        </style>
        <div class="aod-print-container">
            <!-- Header Section -->
            <div class="aod-header">
                <!-- Company Section (Left) -->
                <div class="aod-company-section">
                    <div class="aod-company-top">
                        <div class="aod-logo-area">
                            <img src="{{ asset('src/images/logo/client-logo.png') }}" alt="Company Logo" style="max-width: 100%; height: auto; display: block;">
                        </div>
                        <div class="aod-company-info">
                            <div class="aod-company-slogan-image">
                                <img src="{{ asset('src/images/logo/slogan.png') }}" alt="Company Slogan" style="max-width: 100%; height: auto; display: block;">
                            </div>
                            <div class="aod-company-details">
                                {{ $companyAddress }}<br>
                                Tel: {{ $companyPhone }}<br>
                                E Mail: {{ $companyEmail }}<br>
                                Web: {{ $companyWebsite }}<br>
                                <span class="aod-vat-reg">VAT Reg No:</span> {{ $vatRegNo }}
                            </div>
                        </div>
                    </div>
                    <div class="aod-aod-box">
                        <div class="aod-detail-box">
                            <div class="aod-detail-label">AOD NO: {{ $deliveryNote->dn_number }}</div>
                           
                            <div class="aod-detail-label" style="margin-top: 10px;">AOD DATE:{{ \App\Helpers\DateFormatHelper::format($deliveryNote->dispatch_date) }}</div>
                           
                        </div>
                    </div>
                </div>
                
                <!-- Title Section (Right) -->
                <div class="aod-title-section">
                    <div class="aod-title">ADVICE OF DISPATCH</div>
                    <div class="aod-right-section">
                    <div class="aod-detail-box" style="margin-bottom: 15px;">
                        <div class="aod-detail-label">CUSTOMER:</div>
                        <div class="aod-detail-content">
                            {{ optional($deliveryNote->jobOrder)->customer->name ?? 'N/A' }}<br>
                            @if(optional($deliveryNote->jobOrder)->customer && optional($deliveryNote->jobOrder)->customer->address)
                                @php
                                    $customerAddress = optional($deliveryNote->jobOrder)->customer->address;
                                    $addressLines = explode("\n", $customerAddress);
                                @endphp
                                @foreach($addressLines as $line)
                                    {{ trim($line) }}@if(!$loop->last),<br>@endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="aod-detail-box">
                        <div class="aod-detail-label">DELIVER TO:</div>
                        <div class="aod-detail-content">
                            @if($deliveryNote->delivery_address)
                                @php
                                    $deliveryAddress = $deliveryNote->delivery_address;
                                    $deliveryLines = explode("\n", $deliveryAddress);
                                @endphp
                                @foreach($deliveryLines as $line)
                                    {{ trim($line) }}@if(!$loop->last),<br>@endif
                                @endforeach
                            @else
                                {{ optional($deliveryNote->jobOrder)->customer->address ?? 'N/A' }}
                            @endif
                        </div>
                    </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <table class="aod-items-table">
                <thead>
                    <tr>
                        <th>CUSTOMER PO</th>
                        <th>DESCRIPTION</th>
                        <th>QTY</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deliveryNote->items as $item)
                    <tr>
                        <td class="text-center">{{ optional($deliveryNote->jobOrder)->supplier_po_number ?? optional($deliveryNote->jobOrder)->job_number ?? '-' }}</td>
                        <td>{{ $item->description }}</td>
                        <td class="text-center">{{ number_format($item->quantity, 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Footer Section -->
            <div class="aod-footer">
                <div class="aod-footer-left">
                    <div class="aod-footer-top-label">Vehicle No :</div>
                    <div class="aod-signature-line aod-signature-line-short"></div>
                    <div class="aod-signature-label">Dispatched by</div>
                </div>
                <div class="aod-footer-right">
                    <div class="aod-footer-top-label" style="text-align: right;">Received the items detailed above</div>
                    <div class="aod-signature-line aod-signature-line-long" style="margin-left: auto;"></div>
                    <div class="aod-signature-label" style="text-align: right;">Signature of Recipient</div>
                </div>
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
