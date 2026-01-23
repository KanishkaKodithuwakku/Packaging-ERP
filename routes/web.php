<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Quotations\QuotationsCrud;
use App\Livewire\Customers\CustomerOrdersCrud;
use App\Livewire\JobOrders\JobOrdersCrud;
use App\Livewire\Suppliers\SupplierOrdersCrud;
use App\Livewire\GRN\GRNsCrud;
use App\Livewire\GRN\GRNDetail;
use App\Livewire\MaterialRequests\MaterialRequestsCrud;
use App\Livewire\Inventory\InventoryDashboard;
use App\Livewire\Inventory\InventoryTransactionsHistory;
use App\Livewire\Accounting\ChartOfAccountsCrud;
use App\Livewire\Accounting\CurrencyManagement;
use App\Livewire\Accounting\ExchangeRateManagement;
use App\Livewire\Accounting\EntryTypeManagement;
use App\Livewire\Accounting\JournalEntryCrud;
use App\Livewire\Suppliers\SuppliersManagement;
use App\Livewire\Suppliers\SupplierDetail;
use App\Livewire\Customers\CustomersManagement;
use App\Livewire\Permissions\RolePermissionEditor;
use App\Livewire\ConsumableItemsCrud;
use App\Livewire\TaxManagement;

// Redirect root to dashboard (protected)
Route::get('/', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Dashboard - accessible to all authenticated users
Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Quotations
Route::get('/quotations', QuotationsCrud::class)
    ->middleware(['auth', 'permission:view quotations'])
    ->name('quotations');

// Customer Orders
Route::get('/customer-orders', CustomerOrdersCrud::class)
    ->middleware(['auth', 'permission:view customer orders'])
    ->name('customer-orders');

// Job Orders
Route::get('/job-orders', JobOrdersCrud::class)
    ->middleware(['auth', 'permission:view job orders'])
    ->name('job-orders');

// New Job Order Management
Route::get('/job-order-management', \App\Livewire\JobOrders\JobOrderManagement::class)
    ->middleware(['auth', 'permission:view job orders'])
    ->name('job-order-management');

// Job Order Detail Page
Route::get('/job-order-detail/{id}', \App\Livewire\JobOrders\JobOrderDetail::class)
    ->middleware(['auth', 'permission:view job orders'])
    ->name('job-order-detail');

// Purchase Order Management
Route::get('/purchase-order-management', \App\Livewire\PurchaseOrders\PurchaseOrderManagement::class)
    ->middleware(['auth', 'permission:view purchase orders'])
    ->name('purchase-order-management');

// Production Order Management
Route::get('/production-order-management', \App\Livewire\ProductionOrders\ProductionOrderManagement::class)
    ->middleware(['auth', 'permission:view production orders'])
    ->name('production-order-management');

// Production Order Detail Page
Route::get('/production-order-detail/{id}', \App\Livewire\ProductionOrders\ProductionOrderDetail::class)
    ->middleware(['auth', 'permission:view production orders'])
    ->name('production-order-detail');

// Supplier Orders
Route::get('/supplier-orders', SupplierOrdersCrud::class)
    ->middleware(['auth', 'permission:view supplier orders'])
    ->name('supplier-orders');

// GRNs
Route::get('/grns', GRNsCrud::class)
    ->middleware(['auth', 'permission:view grns'])
    ->name('grns');

// GRN Detail
Route::get('/grns/{id}', GRNDetail::class)
    ->middleware(['auth', 'permission:view grns'])
    ->name('grn-detail');

// GRN Overall Print
Route::get('/grns/{id}/print', [\App\Http\Controllers\GRNPrintController::class, 'printGRN'])
    ->middleware(['auth', 'permission:view grns'])
    ->name('grn-print');

// GRN Processing Receipt Print
Route::get('/grns/{id}/print-processing/{batchId}', \App\Http\Controllers\GRNPrintController::class)
    ->middleware(['auth', 'permission:view grns'])
    ->name('grn-print-processing');

// Test GRN
Route::get('/test-grn/{id}', \App\Livewire\GRN\TestGRN::class)
    ->middleware(['auth', 'permission:view grns'])
    ->name('test-grn');

// Material Requests
Route::get('/material-requests', MaterialRequestsCrud::class)
    ->middleware(['auth', 'permission:view material requests'])
    ->name('material-requests');

// Delivery Notes
Route::get('/delivery-notes', \App\Livewire\DeliveryNotes\DeliveryNotesManagement::class)
    ->middleware(['auth'])
    ->name('delivery-notes-management');

Route::get('/delivery-notes/create', \App\Livewire\DeliveryNotes\CreateDeliveryNote::class)
    ->middleware(['auth'])
    ->name('create-delivery-note');

Route::get('/delivery-notes/{id}', \App\Livewire\DeliveryNotes\DeliveryNoteDetail::class)
    ->middleware(['auth'])
    ->name('delivery-note-detail');

// Invoices
Route::get('/invoices', \App\Livewire\Invoices\InvoiceManagement::class)
    ->middleware(['auth'])
    ->name('invoices');

Route::get('/invoices/{id}', \App\Livewire\Invoices\InvoiceDetail::class)
    ->middleware(['auth'])
    ->name('invoice-detail');

Route::get('/invoices/{id}/print', \App\Http\Controllers\InvoicePrintController::class)
    ->middleware(['auth'])
    ->name('invoice-print');

// Inventory Dashboard
Route::get('/inventory-dashboard', InventoryDashboard::class)
    ->middleware(['auth', 'permission:view inventory'])
    ->name('inventory-dashboard');

// Inventory Transactions
Route::get('/inventory-transactions', InventoryTransactionsHistory::class)
    ->middleware(['auth', 'permission:view inventory transactions'])
    ->name('inventory-transactions');

// Consumable Items Master
Route::get('/consumable-items', ConsumableItemsCrud::class)
    ->middleware(['auth', 'permission:view inventory'])
    ->name('consumable-items');

// Accounting Routes
Route::get('/accounting/chart-of-accounts', ChartOfAccountsCrud::class)
    ->middleware(['auth', 'permission:view accounting'])
    ->name('accounting.chart-of-accounts');

Route::get('/accounting/journal-entries', JournalEntryCrud::class)
    ->middleware(['auth', 'permission:view accounting'])
    ->name('accounting.journal-entries');

Route::get('/accounting/entries', \App\Livewire\Accounting\EntriesManagement::class)
    ->middleware(['auth', 'permission:view accounting'])
    ->name('accounting.entries');

Route::get('/accounting/search', \App\Livewire\Accounting\EntrySearch::class)
    ->middleware(['auth', 'permission:view accounting'])
    ->name('accounting.search');

Route::get('/accounting/dashboard', \App\Livewire\Accounting\AccountingDashboard::class)
    ->middleware(['auth', 'permission:view accounting'])
    ->name('accounting.dashboard');

Route::get('/accounting/currencies', CurrencyManagement::class)
    ->middleware(['auth', 'permission:view accounting'])
    ->name('accounting.currencies');

Route::get('/accounting/exchange-rates', ExchangeRateManagement::class)
    ->middleware(['auth', 'permission:view accounting'])
    ->name('accounting.exchange-rates');

// Reports Routes
Route::get('/accounting/reports/balance-sheet', \App\Livewire\Reports\BalanceSheetReport::class)
    ->middleware(['auth', 'permission:view accounting reports'])
    ->name('accounting.reports.balance-sheet');

Route::get('/accounting/reports/profit-loss', \App\Livewire\Reports\ProfitLossReport::class)
    ->middleware(['auth', 'permission:view accounting reports'])
    ->name('accounting.reports.profit-loss');

Route::get('/accounting/reports/trial-balance', \App\Livewire\Reports\TrialBalanceReport::class)
    ->middleware(['auth', 'permission:view accounting reports'])
    ->name('accounting.reports.trial-balance');

Route::get('/accounting/reports/ledger-statement', \App\Livewire\Reports\LedgerStatementReport::class)
    ->middleware(['auth', 'permission:view accounting reports'])
    ->name('accounting.reports.ledger-statement');

Route::get('/accounting/reports/ledger-entries', \App\Livewire\Reports\LedgerEntriesReport::class)
    ->middleware(['auth', 'permission:view accounting reports'])
    ->name('accounting.reports.ledger-entries');

Route::get('/accounting/reports/reconciliation', \App\Livewire\Reports\ReconciliationReport::class)
    ->middleware(['auth', 'permission:view accounting reports'])
    ->name('accounting.reports.reconciliation');

Route::get('/accounting/reports/stock-movement', \App\Livewire\Reports\StockMovementReport::class)
    ->middleware(['auth', 'permission:view accounting reports'])
    ->name('accounting.reports.stock-movement');

Route::get('/accounting/entry-types', EntryTypeManagement::class)
    ->middleware(['auth', 'permission:view accounting'])
    ->name('accounting.entry-types');

// Debug modal test
Route::get('/debug-modal', \App\Livewire\Test\DebugModal::class)
    ->name('debug-modal');


Route::get('/test-modal-simple', \App\Livewire\Test\TestModalSimple::class)
    ->name('test-modal-simple');

// Profile - accessible to all authenticated users
Route::view('profile', 'profile.profile')
    ->middleware(['auth'])
    ->name('profile');

// Profile related routes
Route::get('/change-password', function () {
    return view('profile.change-password');
})->middleware(['auth'])->name('change-password');

Route::get('/account-settings', function () {
    return view('profile.account-settings');
})->middleware(['auth'])->name('account-settings');

Route::get('/activity-log', function () {
    return view('administration.activity-log');
})->middleware(['auth'])->name('activity-log');

// Profile form submission routes
Route::put('/change-password', function () {
    // Handle password change logic here
    return redirect()->route('profile')->with('success', 'Password updated successfully!');
})->middleware(['auth'])->name('password.update');

Route::put('/account-settings', function () {
    // Handle account settings update logic here
    return redirect()->route('profile')->with('success', 'Account settings updated successfully!');
})->middleware(['auth'])->name('profile.update');

// Role Management Routes
Route::get('/role-management', function () {
    return view('administration.role-management');
})->middleware(['auth', 'permission:view role management'])->name('role-management');

Route::get('/user-management', function () {
    return view('administration.user-management');
})->middleware(['auth', 'permission:view user management'])->name('user-management');

Route::get('/permission-management', function () {
    return view('administration.permission-management');
})->middleware(['auth', 'permission:view permission management'])->name('permission-management');

// UOM Management Routes
Route::get('/uom-management', App\Livewire\UOM\UomManagement::class)
    ->middleware(['auth', 'permission:view uom management'])
    ->name('uom-management');

Route::get('/uom-conversion-management', App\Livewire\UOM\UomConversionManagement::class)
    ->middleware(['auth', 'permission:view uom management'])
    ->name('uom-conversion-management');

Route::get('/uom-conversion-profile-management', App\Livewire\UOM\UomConversionProfileManagement::class)
    ->middleware(['auth', 'permission:view uom management'])
    ->name('uom-conversion-profile-management');

// UOM Dashboard
Route::get('/uom-dashboard', function () {
    return view('uom.uom-dashboard');
})->middleware(['auth', 'permission:view uom management'])
  ->name('uom-dashboard');

// UOM Conversion Examples
Route::get('/uom-conversion-examples', function () {
    return view('uom.uom-conversion-example');
})->middleware(['auth', 'permission:view uom management'])
  ->name('uom-conversion-examples');

// Configuration Management
Route::get('/configuration-management', \App\Livewire\Configuration\ConfigurationManagement::class)
    ->middleware(['auth'])
    ->name('configuration-management');

// Tax Management
Route::get('/tax-management', TaxManagement::class)
    ->middleware(['auth'])
    ->name('tax-management');

// Purchase Order Creation
Route::get('/create-purchase-order', \App\Livewire\PurchaseOrders\CreatePurchaseOrder::class)
    ->middleware(['auth', 'permission:create purchase orders'])
    ->name('create-purchase-order');

// Suppliers Management
Route::get('/suppliers', SuppliersManagement::class)
    ->middleware(['auth', 'permission:view suppliers'])
    ->name('suppliers');

Route::get('/suppliers/{id}', SupplierDetail::class)
    ->middleware(['auth', 'permission:view suppliers'])
    ->name('supplier-detail');

// Customers Management
Route::get('/customers', CustomersManagement::class)
    ->middleware(['auth', 'permission:view customers'])
    ->name('customers');

require __DIR__.'/auth.php';

// Role Permission Editor
Route::get('/role-management/{role}/permissions', RolePermissionEditor::class)
    ->middleware(['auth','role:admin'])
    ->name('role-permissions.edit');
