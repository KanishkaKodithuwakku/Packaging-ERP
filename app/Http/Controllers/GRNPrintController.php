<?php

namespace App\Http\Controllers;

use App\Models\GRN;
use App\Models\GRNProcessingBatch;
use Illuminate\Http\Request;

class GRNPrintController extends Controller
{
    /**
     * Print overall GRN with received quantities
     */
    public function printGRN($id)
    {
        $grn = GRN::with([
            'supplierOrder.supplier',
            'productionOrder.supplier',
            'productionOrder.jobOrder.customer',
            'purchaseOrder.supplier',
            'purchaseOrder.items.jobOrder.customer',
            'supplier',
            'items'
        ])->findOrFail($id);

        // Calculate totals
        $totalExpected = $grn->getTotalExpectedQuantity();
        $totalReceived = $grn->getTotalPartiallyReceivedQuantity();
        $totalProcessed = $grn->getTotalProcessedQuantity();
        $totalPending = $grn->getTotalPendingQuantity();

        return view('grn.print', [
            'grn' => $grn,
            'totalExpected' => $totalExpected,
            'totalReceived' => $totalReceived,
            'totalProcessed' => $totalProcessed,
            'totalPending' => $totalPending,
        ]);
    }

    /**
     * Print processing receipt for a specific batch
     */
    public function __invoke($id, $batchId)
    {
        $grn = GRN::with([
            'supplierOrder.supplier',
            'productionOrder.supplier',
            'productionOrder.jobOrder.customer',
            'purchaseOrder.supplier',
            'purchaseOrder.items.jobOrder.customer',
            'supplier',
            'items' // Load items with grn relationship
        ])->findOrFail($id);

        $batch = GRNProcessingBatch::with([
            'itemBatches.grnItem.grn', // Eager load grn relationship on grnItem
            'processedBy'
        ])->findOrFail($batchId);

        // Verify batch belongs to this GRN
        if ($batch->grn_id != $grn->id) {
            abort(404, 'Batch does not belong to this GRN');
        }

        // Refresh GRN items to get latest processed quantities and ensure grn relationship is loaded
        $grn->load('items');

        // Calculate batch totals
        $totalProcessedQty = $batch->itemBatches->sum('quantity_processed');
        $totalValue = $batch->total_value ?? $batch->itemBatches->sum('total_cost');
        
        // Calculate remaining to process for each item
        $itemsWithRemaining = [];
        foreach ($batch->itemBatches as $itemBatch) {
            // Get the GRN item from the refreshed GRN items collection to ensure we have latest data
            $grnItem = $grn->items->firstWhere('id', $itemBatch->grn_item_id);
            
            // Fallback to batch's grnItem if not found in GRN items
            if (!$grnItem) {
                $grnItem = $itemBatch->grnItem;
            }
            
            // Skip if grnItem is still null
            if (!$grnItem) {
                \Log::warning('GRN item not found for batch item', [
                    'grn_id' => $grn->id,
                    'batch_id' => $batch->id,
                    'item_batch_id' => $itemBatch->id,
                    'grn_item_id' => $itemBatch->grn_item_id
                ]);
                continue;
            }
            
            // Ensure grn relationship is set on the item
            if (!$grnItem->relationLoaded('grn')) {
                $grnItem->setRelation('grn', $grn);
            }
            
            // Get current processed quantity for this item
            $currentProcessed = $grnItem->qty_processed ?? 0;
            
            // Previously processed = current processed - this batch's quantity
            $previouslyProcessed = max(0, $currentProcessed - $itemBatch->quantity_processed);
            
            // Remaining = received - current processed
            $remaining = max(0, ($grnItem->qty_received_partial ?? 0) - $currentProcessed);
            
            $itemsWithRemaining[] = [
                'item_batch' => $itemBatch,
                'grn_item' => $grnItem,
                'previously_processed' => $previouslyProcessed,
                'this_batch_qty' => $itemBatch->quantity_processed,
                'remaining' => $remaining,
            ];
        }

        return view('grn.print-processing', [
            'grn' => $grn,
            'batch' => $batch,
            'totalProcessedQty' => $totalProcessedQty,
            'totalValue' => $totalValue,
            'itemsWithRemaining' => $itemsWithRemaining,
        ]);
    }
}
