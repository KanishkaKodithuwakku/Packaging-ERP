<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\QuotationsCrud;
use App\Livewire\CustomerOrdersCrud;
use App\Livewire\JobOrdersCrud;
use App\Livewire\SupplierOrdersCrud;
use App\Livewire\GRNsCrud;
use App\Livewire\GRNDetail;
use App\Livewire\MaterialRequestsCrud;
use App\Livewire\DeliveryNotesCrud;
use App\Livewire\InventoryDashboard;
use App\Livewire\InventoryTransactionsHistory;
use App\Livewire\ChartOfAccountsCrud;
use App\Livewire\CurrencyManagement;
use App\Livewire\ExchangeRateManagement;
use App\Livewire\EntryTypeManagement;
use App\Livewire\JournalEntryCrud;
use App\Livewire\SuppliersManagement;
use App\Livewire\SupplierDetail;
use App\Livewire\CustomersManagement;
use App\Livewire\RolePermissionEditor;

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
Route::get('/job-order-management', \App\Livewire\JobOrderManagement::class)
    ->middleware(['auth', 'permission:view job orders'])
    ->name('job-order-management');

// Job Order Detail Page
Route::get('/job-order-detail/{id}', \App\Livewire\JobOrderDetail::class)
    ->middleware(['auth', 'permission:view job orders'])
    ->name('job-order-detail');

// Purchase Order Management
Route::get('/purchase-order-management', \App\Livewire\PurchaseOrderManagement::class)
    ->middleware(['auth', 'permission:view purchase orders'])
    ->name('purchase-order-management');

// Production Order Management
Route::get('/production-order-management', \App\Livewire\ProductionOrderManagement::class)
    ->middleware(['auth', 'permission:view production orders'])
    ->name('production-order-management');

// Production Order Detail Page
Route::get('/production-order-detail/{id}', \App\Livewire\ProductionOrderDetail::class)
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

// Test GRN
Route::get('/test-grn/{id}', \App\Livewire\TestGRN::class)
    ->middleware(['auth', 'permission:view grns'])
    ->name('test-grn');

// Material Requests
Route::get('/material-requests', MaterialRequestsCrud::class)
    ->middleware(['auth', 'permission:view material requests'])
    ->name('material-requests');

// Delivery Notes
Route::get('/delivery-notes', \App\Livewire\DeliveryNotesManagement::class)
    ->middleware(['auth'])
    ->name('delivery-notes-management');

Route::get('/delivery-notes/create', \App\Livewire\CreateDeliveryNote::class)
    ->middleware(['auth'])
    ->name('create-delivery-note');

Route::get('/delivery-notes/{id}', \App\Livewire\DeliveryNoteDetail::class)
    ->middleware(['auth'])
    ->name('delivery-note-detail');

// Inventory Dashboard
Route::get('/inventory-dashboard', InventoryDashboard::class)
    ->middleware(['auth', 'permission:view inventory'])
    ->name('inventory-dashboard');

// Inventory Transactions
Route::get('/inventory-transactions', InventoryTransactionsHistory::class)
    ->middleware(['auth', 'permission:view inventory transactions'])
    ->name('inventory-transactions');

// Accounting Routes
Route::get('/accounting/chart-of-accounts', ChartOfAccountsCrud::class)
    ->middleware(['auth', 'permission:view accounting'])
    ->name('accounting.chart-of-accounts');

Route::get('/accounting/journal-entries', JournalEntryCrud::class)
    ->middleware(['auth', 'permission:view accounting'])
    ->name('accounting.journal-entries');

Route::get('/accounting/entries', \App\Livewire\EntriesManagement::class)
    ->middleware(['auth', 'permission:view accounting'])
    ->name('accounting.entries');

Route::get('/accounting/search', \App\Livewire\EntrySearch::class)
    ->middleware(['auth', 'permission:view accounting'])
    ->name('accounting.search');

Route::get('/accounting/dashboard', \App\Livewire\AccountingDashboard::class)
    ->middleware(['auth', 'permission:view accounting'])
    ->name('accounting.dashboard');

Route::get('/accounting/currencies', CurrencyManagement::class)
    ->middleware(['auth', 'permission:view accounting'])
    ->name('accounting.currencies');

Route::get('/accounting/exchange-rates', ExchangeRateManagement::class)
    ->middleware(['auth', 'permission:view accounting'])
    ->name('accounting.exchange-rates');

// Reports Routes
Route::get('/accounting/reports/balance-sheet', \App\Livewire\BalanceSheetReport::class)
    ->middleware(['auth', 'permission:view accounting reports'])
    ->name('accounting.reports.balance-sheet');

Route::get('/accounting/reports/profit-loss', \App\Livewire\ProfitLossReport::class)
    ->middleware(['auth', 'permission:view accounting reports'])
    ->name('accounting.reports.profit-loss');

Route::get('/accounting/reports/trial-balance', \App\Livewire\TrialBalanceReport::class)
    ->middleware(['auth', 'permission:view accounting reports'])
    ->name('accounting.reports.trial-balance');

Route::get('/accounting/reports/ledger-statement', \App\Livewire\LedgerStatementReport::class)
    ->middleware(['auth', 'permission:view accounting reports'])
    ->name('accounting.reports.ledger-statement');

Route::get('/accounting/reports/ledger-entries', \App\Livewire\LedgerEntriesReport::class)
    ->middleware(['auth', 'permission:view accounting reports'])
    ->name('accounting.reports.ledger-entries');

Route::get('/accounting/reports/reconciliation', \App\Livewire\ReconciliationReport::class)
    ->middleware(['auth', 'permission:view accounting reports'])
    ->name('accounting.reports.reconciliation');

Route::get('/accounting/reports/stock-movement', \App\Livewire\StockMovementReport::class)
    ->middleware(['auth', 'permission:view accounting reports'])
    ->name('accounting.reports.stock-movement');

Route::get('/accounting/entry-types', EntryTypeManagement::class)
    ->middleware(['auth', 'permission:view accounting'])
    ->name('accounting.entry-types');

// Debug modal test
Route::get('/debug-modal', \App\Livewire\DebugModal::class)
    ->name('debug-modal');


Route::get('/test-modal-simple', \App\Livewire\TestModalSimple::class)
    ->name('test-modal-simple');

// Profile - accessible to all authenticated users
Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Profile related routes
Route::get('/change-password', function () {
    return view('change-password');
})->middleware(['auth'])->name('change-password');

Route::get('/account-settings', function () {
    return view('account-settings');
})->middleware(['auth'])->name('account-settings');

Route::get('/activity-log', function () {
    return view('activity-log');
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
    return view('role-management');
})->middleware(['auth', 'permission:view role management'])->name('role-management');

Route::get('/user-management', function () {
    return view('user-management');
})->middleware(['auth', 'permission:view user management'])->name('user-management');

Route::get('/permission-management', function () {
    return view('permission-management');
})->middleware(['auth', 'permission:view permission management'])->name('permission-management');

// UOM Management Routes
Route::get('/uom-management', App\Livewire\UomManagement::class)
    ->middleware(['auth', 'permission:view uom management'])
    ->name('uom-management');

Route::get('/uom-conversion-management', App\Livewire\UomConversionManagement::class)
    ->middleware(['auth', 'permission:view uom management'])
    ->name('uom-conversion-management');

Route::get('/uom-conversion-profile-management', App\Livewire\UomConversionProfileManagement::class)
    ->middleware(['auth', 'permission:view uom management'])
    ->name('uom-conversion-profile-management');

// UOM Dashboard
Route::get('/uom-dashboard', function () {
    return view('uom-dashboard');
})->middleware(['auth', 'permission:view uom management'])
  ->name('uom-dashboard');

// UOM Conversion Examples
Route::get('/uom-conversion-examples', function () {
    return view('uom-conversion-example');
})->middleware(['auth', 'permission:view uom management'])
  ->name('uom-conversion-examples');

// Configuration Management
Route::get('/configuration-management', \App\Livewire\ConfigurationManagement::class)
    ->middleware(['auth', 'permission:view configuration'])
    ->name('configuration-management');

// Purchase Order Creation
Route::get('/create-purchase-order', \App\Livewire\CreatePurchaseOrder::class)
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
