<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: white;
        }
        
        /* Hide browser elements */
        @media print {
            @page {
                margin: 0;
                size: A4;
            }
            html, body {
                height: auto;
                overflow: visible;
                margin: 0;
                padding: 0;
            }
            /* Hide any browser-generated content */
            body::before,
            body::after {
                display: none !important;
            }
            /* Force hide URL and page numbers */
            @page {
                margin: 0;
                @bottom-center { content: ""; }
                @bottom-left { content: ""; }
                @bottom-right { content: ""; }
            }
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .company-info {
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .company-address {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
        .invoice-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-top: 20px;
        }
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .info-section {
            flex: 1;
            margin-right: 20px;
        }
        .info-section:last-child {
            margin-right: 0;
        }
        .section-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }
        .info-item {
            margin-bottom: 5px;
            font-size: 14px;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 120px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th,
        .items-table td {
            border: 1px solid #333;
            padding: 10px;
            text-align: left;
        }
        .items-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .items-table td.text-right {
            text-align: right;
        }
        .summary-section {
            margin-top: 20px;
            margin-bottom: 30px;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .summary-label {
            font-weight: bold;
        }
        .summary-value {
            font-weight: bold;
            font-size: 16px;
        }
        .footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .signature-section {
            width: 200px;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #3B82F6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }
        .print-button:hover {
            background: #2563EB;
        }
        .close-button {
            position: fixed;
            top: 20px;
            right: 80px;
            background: #6B7280;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }
        .close-button:hover {
            background: #4B5563;
        }
        @media print {
            .print-button,
            .close-button {
                display: none;
            }
            body {
                margin: 0;
                padding: 0;
            }
            /* Hide URL and page info when printing */
            body::after {
                display: none !important;
            }
        }
        
        /* Hide URL and browser elements */
        @media print {
            @page {
                margin: 0;
                size: A4;
            }
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                margin: 0;
                padding: 0;
            }
            /* Hide any URL or browser elements */
            * {
                -webkit-print-color-adjust: exact;
            }
            /* Add white overlay to hide URL */
            body::after {
                content: "";
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                height: 50px;
                background: white;
                z-index: 9999;
            }
        }
    </style>
</head>
<body>
    <button class="close-button" onclick="window.close()">✕ Close</button>
    <button class="print-button" onclick="window.print()">🖨️ Print</button>
    
    <div class="header">
        <div class="company-info">
            <div class="company-name">Packaging ERP System</div>
            <div class="company-address">
                123 Business Street<br>
                Industrial Area, City 12345<br>
                Phone: +1 (555) 123-4567<br>
                Email: info@packagingerp.com
            </div>
        </div>
        <div class="invoice-title">INVOICE</div>
    </div>

    <div class="invoice-info">
        <div class="info-section">
            <div class="section-title">Invoice Information</div>
            <div class="info-item">
                <span class="info-label">Invoice No:</span>
                {{ $invoice->invoice_number }}
            </div>
            <div class="info-item">
                <span class="info-label">Invoice Date:</span>
                {{ $invoice->invoice_date->format('M d, Y') }}
            </div>
            @if($invoice->due_date)
            <div class="info-item">
                <span class="info-label">Due Date:</span>
                {{ $invoice->due_date->format('M d, Y') }}
            </div>
            @endif
            @if($invoice->deliveryNote)
            <div class="info-item">
                <span class="info-label">Delivery Note:</span>
                {{ $invoice->deliveryNote->dn_number }}
            </div>
            @endif
            @if($invoice->jobOrder)
            <div class="info-item">
                <span class="info-label">Job Order:</span>
                {{ $invoice->jobOrder->job_number }}
            </div>
            @endif
        </div>

        <div class="info-section">
            <div class="section-title">Bill To</div>
            <div class="info-item">
                <span class="info-label">Customer:</span>
                {{ $invoice->customer->name ?? 'N/A' }}
            </div>
            @if($invoice->customer && $invoice->customer->address)
            <div class="info-item">
                <span class="info-label">Address:</span>
                {{ $invoice->customer->address }}
            </div>
            @endif
            @if($invoice->customer && $invoice->customer->phone)
            <div class="info-item">
                <span class="info-label">Phone:</span>
                {{ $invoice->customer->phone }}
            </div>
            @endif
            @if($invoice->customer && $invoice->customer->email)
            <div class="info-item">
                <span class="info-label">Email:</span>
                {{ $invoice->customer->email }}
            </div>
            @endif
        </div>
    </div>

    @php
        // Get customer currency symbol
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

    <table class="items-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Description</th>
                <th>Material Code</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->material_code }}</td>
                    <td class="text-right">{{ number_format($item->quantity, 0) }}</td>
                    <td class="text-right">{{ $currencySymbol }}{{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right"><strong>{{ $currencySymbol }}{{ number_format($item->line_total, 2) }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-section">
        @if($invoice->subtotal)
        <div class="summary-item">
            <span class="summary-label">Subtotal:</span>
            <span class="summary-value">{{ $currencySymbol }}{{ number_format($invoice->subtotal, 2) }}</span>
        </div>
        @endif
        @if($invoice->tax_amount > 0)
        <div class="summary-item">
            <span class="summary-label">Tax:</span>
            <span class="summary-value">{{ $currencySymbol }}{{ number_format($invoice->tax_amount, 2) }}</span>
        </div>
        @endif
        @if($invoice->discount_amount > 0)
        <div class="summary-item">
            <span class="summary-label">Discount:</span>
            <span class="summary-value">-{{ $currencySymbol }}{{ number_format($invoice->discount_amount, 2) }}</span>
        </div>
        @endif
        <div class="summary-item" style="border-top: 2px solid #333; padding-top: 10px; margin-top: 10px;">
            <span class="summary-label" style="font-size: 18px;">Total Amount:</span>
            <span class="summary-value" style="font-size: 20px;">{{ $currencySymbol }}{{ number_format($invoice->total_amount, 2) }}</span>
        </div>
    </div>

    @if($invoice->notes)
    <div style="margin-bottom: 20px;">
        <div class="section-title">Notes</div>
        <p style="font-size: 14px; color: #666;">{{ $invoice->notes }}</p>
    </div>
    @endif

    @if($invoice->terms)
    <div style="margin-bottom: 20px;">
        <div class="section-title">Terms & Conditions</div>
        <p style="font-size: 14px; color: #666;">{{ $invoice->terms }}</p>
    </div>
    @endif

    <div class="footer">
        <div class="signature-section">
            <div class="signature-line">
                <strong>Prepared By:</strong><br>
                _________________<br>
                <small>Name & Signature</small>
            </div>
        </div>
        <div class="signature-section">
            <div class="signature-line">
                <strong>Authorized By:</strong><br>
                _________________<br>
                <small>Name & Signature</small>
            </div>
        </div>
        <div class="signature-section">
            <div class="signature-line">
                <strong>Received By:</strong><br>
                _________________<br>
                <small>Customer Signature</small>
            </div>
        </div>
    </div>
    
    <!-- White space to push content up and hide URL -->
    <div style="height: 100px; background: white; margin-top: 20px;"></div>

    <script>
        // Hide URL and improve print experience
        let printTriggered = false;
        
        window.onload = function() {
            // Remove any browser-generated content
            document.title = 'Invoice - {{ $invoice->invoice_number }}';
            
            // Try to hide URL by manipulating the page
            document.body.style.marginBottom = '100px';
            
            // Auto-trigger print dialog when page loads
            // Only trigger if opened in popup window, NOT in iframe
            // When in iframe, parent page handles the print trigger
            if (window.opener && window.self === window.top && !printTriggered) {
                printTriggered = true;
                setTimeout(function() {
                    window.print();
                }, 500);
            }
            
            // Hide URL in print
            if (window.matchMedia) {
                var mediaQueryList = window.matchMedia('print');
                mediaQueryList.addListener(function(mql) {
                    if (mql.matches) {
                        // Hide browser elements when printing
                        document.body.style.overflow = 'visible';
                        document.body.style.marginBottom = '100px';
                        // Add white space at bottom
                        var whiteSpace = document.createElement('div');
                        whiteSpace.style.height = '100px';
                        whiteSpace.style.background = 'white';
                        whiteSpace.style.position = 'fixed';
                        whiteSpace.style.bottom = '0';
                        whiteSpace.style.left = '0';
                        whiteSpace.style.right = '0';
                        whiteSpace.style.zIndex = '9999';
                        document.body.appendChild(whiteSpace);
                    } else {
                        // Print dialog was closed
                    }
                });
            }
        }
    </script>
</body>
</html>
