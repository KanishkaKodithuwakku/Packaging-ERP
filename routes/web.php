<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\QuotationsCrud;
use App\Livewire\CustomerOrdersCrud;
use App\Livewire\JobOrdersCrud;
use App\Livewire\SupplierOrdersCrud;
use App\Livewire\GRNsCrud;
use App\Livewire\MaterialRequestsCrud;
use App\Livewire\DeliveryNotesCrud;
use App\Livewire\InventoryDashboard;
use App\Livewire\InventoryTransactionsHistory;

Route::get('/', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

Route::get('/quotations', QuotationsCrud::class)->name('quotations');
Route::get('/customer-orders', CustomerOrdersCrud::class)->name('customer-orders');
Route::get('/job-orders', JobOrdersCrud::class)->name('job-orders');
Route::get('/supplier-orders', SupplierOrdersCrud::class)->name('supplier-orders');
Route::get('/grns', GRNsCrud::class)->name('grns');
Route::get('/material-requests', MaterialRequestsCrud::class)->name('material-requests');
Route::get('/delivery-notes', DeliveryNotesCrud::class)->name('delivery-notes');
Route::get('/delivery-notes/{id}/print', function ($id) {
    $deliveryNote = \App\Models\DeliveryNote::with('customerOrder.customer')->findOrFail($id);
    return view('delivery-notes.print', compact('deliveryNote'));
})->name('delivery-notes.print');
Route::get('/inventory-dashboard', InventoryDashboard::class)->name('inventory-dashboard');
Route::get('/inventory-transactions', InventoryTransactionsHistory::class)->name('inventory-transactions');
