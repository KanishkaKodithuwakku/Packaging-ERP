<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GRN Processing Receipt - {{ $grn->formatted_grn_no }} - Batch #{{ $batch->id }}</title>
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
        .receipt-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-top: 20px;
        }
        .grn-info {
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
            width: 150px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th,
        .items-table td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
            font-size: 12px;
        }
        .items-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .items-table td {
            vertical-align: top;
        }
        .summary-section {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .summary-label {
            font-weight: bold;
        }
        .summary-value {
            font-weight: bold;
            color: #333;
        }
        .footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        .signature-section {
            width: 250px;
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
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <button class="close-button" onclick="window.close()">✕ Close</button>
    <button class="print-button" onclick="window.print()">🖨️ Print</button>
    
    <div class="header">
        <div class="company-info">
            <div class="company-name">Kings Packaging ERP System</div>
            <div class="company-address">
                123 Business Street<br>
                Industrial Area, City 12345<br>
                Phone: +1 (555) 123-4567<br>
                Email: info@kingspackaging.com
            </div>
        </div>
        <div class="receipt-title">GRN PROCESSING RECEIPT</div>
    </div>

    <div class="grn-info">
        <div class="info-section">
            <div class="section-title">GRN Details</div>
            <div class="info-item">
                <span class="info-label">GRN No:</span>
                @php
                    $grnNo = $grn->grn_no;
                    // Fix incorrectly formatted GRN numbers
                    if (strpos($grnNo, 'GRN-LOT-') === 0) {
                        $remaining = substr($grnNo, 8);
                        if (preg_match('/^([0-9]{6})/', $remaining, $matches)) {
                            $grnNo = 'GRN-' . $matches[1];
                        }
                    }
                @endphp
                {{ $grnNo }}
            </div>
            <div class="info-item">
                <span class="info-label">Date:</span>
                {{ $grn->received_date ? $grn->received_date->format('Y-m-d') : 'N/A' }}
            </div>
            <div class="info-item">
                <span class="info-label">Lot Code:</span>
                {{ $grn->lot_code ?? 'N/A' }}
            </div>
            <div class="info-item">
                <span class="info-label">Source:</span>
                @if($grn->isFromProductionOrder())
                    Production Order - {{ $grn->productionOrder->production_order_number ?? 'N/A' }}
                @elseif($grn->isFromPurchaseOrder())
                    Purchase Order - {{ $grn->purchaseOrder->po_number ?? 'N/A' }}
                @elseif($grn->supplierOrder)
                    Supplier PO - {{ $grn->supplierOrder->po_no ?? 'N/A' }}
                @else
                    Direct GRN
                    @if($grn->supplier)
                        - {{ $grn->supplier->name }}
                    @endif
                @endif
            </div>
        </div>

        <div class="info-section">
            <div class="section-title">Processing Information</div>
            <div class="info-item">
                <span class="info-label">Batch ID:</span>
                #{{ $batch->id }}
            </div>
            <div class="info-item">
                <span class="info-label">Processing Date:</span>
                {{ $batch->processed_at->format('Y-m-d H:i') }}
            </div>
            <div class="info-item">
                <span class="info-label">Processed By:</span>
                {{ $batch->processedBy->name ?? 'N/A' }}
            </div>
            @if($batch->notes)
            <div class="info-item">
                <span class="info-label">Notes:</span>
                {{ $batch->notes }}
            </div>
            @endif
        </div>
    </div>

    <table class="items-table">
        <thead>
            <tr>
                @if($grn->isFromPurchaseOrder())
                <th>Customer</th>
                <th>Job Order</th>
                @endif
                <th>Description</th>
                <th>Material Code</th>
                <th>Expected</th>
                <th>Received</th>
                <th>Previously Processed</th>
                <th>This Batch</th>
                <th>Remaining</th>
                <th>Unit Cost</th>
                <th>Total Cost</th>
            </tr>
        </thead>
        <tbody>
            @forelse($itemsWithRemaining as $itemData)
                @php
                    $itemBatch = $itemData['item_batch'];
                    $grnItem = $itemData['grn_item'];
                    
                    // Skip if grnItem is null
                    if (!$grnItem) {
                        continue;
                    }
                    
                    $purchaseOrderItem = $grn->isFromPurchaseOrder() ? $grnItem->getPurchaseOrderItem() : null;
                    $jobOrder = null;
                    $customer = null;
                    
                    if ($grn->isFromProductionOrder() && $grn->productionOrder) {
                        $jobOrder = $grn->productionOrder->jobOrder;
                        $customer = $jobOrder ? $jobOrder->customer : null;
                    } elseif ($purchaseOrderItem) {
                        $jobOrder = $purchaseOrderItem->jobOrder;
                        $customer = $jobOrder ? $jobOrder->customer : null;
                    }
                @endphp
                <tr>
                    @if($grn->isFromPurchaseOrder())
                    <td>{{ $customer ? $customer->name : 'N/A' }}</td>
                    <td>{{ $jobOrder ? ($jobOrder->supplier_po_number ?? $jobOrder->job_number ?? 'N/A') : 'N/A' }}</td>
                    @endif
                    <td>{{ $grnItem->description }}</td>
                    <td>{{ $grnItem->material_code }}</td>
                    <td class="text-right">{{ number_format($grnItem->qty_expected ?? 0, 2) }} {{ $grnItem->uom }}</td>
                    <td class="text-right">{{ number_format($grnItem->qty_received_partial ?? 0, 2) }} {{ $grnItem->uom }}</td>
                    <td class="text-right">{{ number_format($itemData['previously_processed'], 2) }} {{ $grnItem->uom }}</td>
                    <td class="text-right"><strong>{{ number_format($itemBatch->quantity_processed, 2) }} {{ $grnItem->uom }}</strong></td>
                    <td class="text-right">{{ number_format($itemData['remaining'], 2) }} {{ $grnItem->uom }}</td>
                    <td class="text-right">{{ $grn->getCurrencySymbol() }}{{ number_format($itemBatch->unit_cost, 2) }}</td>
                    <td class="text-right"><strong>{{ $grn->getCurrencySymbol() }}{{ number_format($itemBatch->total_cost, 2) }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $grn->isFromPurchaseOrder() ? '11' : '9' }}" class="text-center">No items processed in this batch</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary-section">
        <div class="summary-item">
            <span class="summary-label">Total Processed in This Batch:</span>
            <span class="summary-value">{{ number_format($totalProcessedQty, 2) }} PCS</span>
        </div>
        <div class="summary-item">
            <span class="summary-label">Total Value:</span>
            <span class="summary-value">{{ $grn->getCurrencySymbol() }}{{ number_format($totalValue, 2) }}</span>
        </div>
        @php
            $totalRemaining = collect($itemsWithRemaining)->sum('remaining');
        @endphp
        @if($totalRemaining > 0)
        <div class="summary-item">
            <span class="summary-label">Remaining to Process:</span>
            <span class="summary-value" style="color: #d97706;">{{ number_format($totalRemaining, 2) }} PCS</span>
        </div>
        @endif
    </div>

    <div class="footer">
        <div class="signature-section">
            <div class="signature-line">
                <strong>Prepared By:</strong><br>
                {{ $batch->processedBy->name ?? 'N/A' }}<br>
                <small>Name & Signature</small>
            </div>
        </div>
        <div class="signature-section">
            <div class="signature-line">
                <strong>Approved By:</strong><br>
                _________________<br>
                <small>Name & Signature</small>
            </div>
        </div>
        <div class="signature-section">
            <div class="signature-line">
                <strong>Received By:</strong><br>
                _________________<br>
                <small>Warehouse Signature</small>
            </div>
        </div>
    </div>
    
    <!-- White space to push content up and hide URL -->
    <div style="height: 100px; background: white; margin-top: 20px;"></div>

    <script>
        // Hide URL and improve print experience
        let printTriggered = false;
        let printDialogClosed = false;
        
        window.onload = function() {
            // Remove any browser-generated content
            document.title = 'GRN Processing Receipt - {{ $grn->formatted_grn_no }} - Batch #{{ $batch->id }}';
            
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
            
            // Detect when print dialog is closed/cancelled
            window.addEventListener('focus', function() {
                // If print was cancelled and window regains focus, close the window
                if (window.opener && printTriggered && !printDialogClosed) {
                    setTimeout(function() {
                        // Check if window is still open and user cancelled print
                        if (window.opener && !document.hidden) {
                            printDialogClosed = true;
                            // Optionally auto-close, or let user click close button
                            // window.close();
                        }
                    }, 100);
                }
            });
            
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
                        printDialogClosed = true;
                    }
                });
            }
        }
    </script>
</body>
</html>
