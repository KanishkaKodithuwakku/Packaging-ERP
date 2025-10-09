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
use App\Livewire\Accounts\ChartOfAccounts;
use App\Livewire\Accounts\CreateGroup;
use App\Livewire\Accounts\EditGroup;

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
    ->middleware(['auth'])
    ->name('quotations');

// Customer Orders
Route::get('/customer-orders', CustomerOrdersCrud::class)
    ->middleware(['auth'])
    ->name('customer-orders');

// Job Orders
Route::get('/job-orders', JobOrdersCrud::class)
    ->middleware(['auth'])
    ->name('job-orders');

// Supplier Orders
Route::get('/supplier-orders', SupplierOrdersCrud::class)
    ->middleware(['auth'])
    ->name('supplier-orders');

// GRNs
Route::get('/grns', GRNsCrud::class)
    ->middleware(['auth'])
    ->name('grns');

// Material Requests
Route::get('/material-requests', MaterialRequestsCrud::class)
    ->middleware(['auth'])
    ->name('material-requests');

// Delivery Notes
Route::get('/delivery-notes', DeliveryNotesCrud::class)
    ->middleware(['auth'])
    ->name('delivery-notes');

// Delivery Notes Print
Route::get('/delivery-notes/{id}/print', function ($id) {
    $deliveryNote = \App\Models\DeliveryNote::with('customerOrder.customer')->findOrFail($id);
    return view('delivery-notes.print', compact('deliveryNote'));
})->middleware(['auth'])->name('delivery-notes.print');

// Inventory Dashboard
Route::get('/inventory-dashboard', InventoryDashboard::class)
    ->middleware(['auth'])
    ->name('inventory-dashboard');

// Inventory Transactions
Route::get('/inventory-transactions', InventoryTransactionsHistory::class)
    ->middleware(['auth'])
    ->name('inventory-transactions');

// Accounts Routes
Route::get('/chart-of-accounts', ChartOfAccounts::class)
    ->middleware(['auth'])
    ->name('chart-of-accounts');

Route::get('/create-group', CreateGroup::class)
    ->middleware(['auth'])
    ->name('create-group');

Route::get('/edit-group/{id}', EditGroup::class)
    ->middleware(['auth'])
    ->name('edit-group');

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
})->middleware(['auth'])->name('role-management');

Route::get('/user-management', function () {
    return view('user-management');
})->middleware(['auth'])->name('user-management');

Route::get('/permission-management', function () {
    return view('permission-management');
})->middleware(['auth'])->name('permission-management');

// UOM Management Routes
Route::get('/uom-management', App\Livewire\UomManagement::class)
    ->middleware(['auth', 'role:admin|planner'])
    ->name('uom-management');

Route::get('/uom-conversion-management', App\Livewire\UomConversionManagement::class)
    ->middleware(['auth', 'role:admin|planner'])
    ->name('uom-conversion-management');

Route::get('/uom-conversion-profile-management', App\Livewire\UomConversionProfileManagement::class)
    ->middleware(['auth', 'role:admin|planner'])
    ->name('uom-conversion-profile-management');

// UOM Dashboard
Route::get('/uom-dashboard', function () {
    return view('uom-dashboard');
})->middleware(['auth', 'role:admin|planner'])
  ->name('uom-dashboard');

// UOM Conversion Examples
Route::get('/uom-conversion-examples', function () {
    return view('uom-conversion-example');
})->middleware(['auth', 'role:admin|planner'])
  ->name('uom-conversion-examples');

require __DIR__.'/auth.php';
