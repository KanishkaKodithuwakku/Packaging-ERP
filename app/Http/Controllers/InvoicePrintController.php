<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoicePrintController extends Controller
{
    /**
     * Print invoice
     */
    public function __invoke($id)
    {
        $invoice = Invoice::with([
            'customer.taxes',
            'deliveryNote',
            'jobOrder',
            'items'
        ])->findOrFail($id);

        return view('invoices.print', [
            'invoice' => $invoice,
        ]);
    }
}
