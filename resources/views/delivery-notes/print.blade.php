<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Note - {{ $deliveryNote->dn_no }}</title>
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
        .delivery-note-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-top: 20px;
        }
        .delivery-info {
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
        }
        .print-button:hover {
            background: #2563EB;
        }
        @media print {
            .print-button {
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
        <div class="delivery-note-title">DELIVERY NOTE</div>
    </div>

    <div class="delivery-info">
        <div class="info-section">
            <div class="section-title">Delivery Information</div>
            <div class="info-item">
                <span class="info-label">DN Number:</span>
                {{ $deliveryNote->dn_no }}
            </div>
            <div class="info-item">
                <span class="info-label">Delivery Date:</span>
                {{ $deliveryNote->delivery_date->format('M d, Y') }}
            </div>
            <div class="info-item">
                <span class="info-label">FG Code:</span>
                {{ $deliveryNote->fg_code }}
            </div>
            <div class="info-item">
                <span class="info-label">Quantity:</span>
                {{ $deliveryNote->qty_delivered }} PCS
            </div>
        </div>

        <div class="info-section">
            <div class="section-title">Customer Information</div>
            <div class="info-item">
                <span class="info-label">Customer:</span>
                {{ $deliveryNote->customerOrder->customer->name }}
            </div>
            <div class="info-item">
                <span class="info-label">Order No:</span>
                {{ $deliveryNote->customerOrder->order_no }}
            </div>
            <div class="info-item">
                <span class="info-label">Email:</span>
                {{ $deliveryNote->customerOrder->customer->email }}
            </div>
            <div class="info-item">
                <span class="info-label">Phone:</span>
                {{ $deliveryNote->customerOrder->customer->phone ?? 'N/A' }}
            </div>
            <div class="info-item">
                <span class="info-label">Address:</span>
                {{ $deliveryNote->customerOrder->customer->address ?? 'N/A' }}
            </div>
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                <th>Item Description</th>
                <th>Size (mm)</th>
                <th>Ply</th>
                <th>Quantity Delivered</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $deliveryNote->customerOrder->item_desc }}</td>
                <td>{{ $deliveryNote->customerOrder->size_mm }}</td>
                <td>{{ $deliveryNote->customerOrder->ply }}</td>
                <td>{{ $deliveryNote->qty_delivered }}</td>
                <td>PCS</td>
            </tr>
        </tbody>
    </table>

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
        window.onload = function() {
            // Remove any browser-generated content
            document.title = 'Delivery Note - {{ $deliveryNote->dn_no }}';
            
            // Try to hide URL by manipulating the page
            document.body.style.marginBottom = '100px';
            
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
                    }
                });
            }
        }
        
        // Auto-print when page loads (optional)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>
