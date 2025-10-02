<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ItemSaleController;
use App\Http\Controllers\InventoryAdjustmentController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\VersionNotificationController;
use App\Http\Controllers\OwnerController;


Route::get('register', [AuthController::class, 'showRegisterForm'])->name('register.form');
Route::post('register', [AuthController::class, 'register'])->name('register');


Route::get('login', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::post('login', [AuthController::class, 'login'])->name('login');

Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// Redirect to dashboard
Route::get('/', fn () => redirect()->route('dashboard'));

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/owner-equity', [DashboardController::class, 'addOwnerEquity'])->name('dashboard.owner-equity');

    // Users management
    Route::get('/users', [AuthController::class, 'users'])->name('users.index');
    Route::get('users/create', [AuthController::class, 'create'])->name('users.create');
    Route::post('users', [AuthController::class, 'store'])->name('users.store');
    Route::get('users/{id}/edit', [AuthController::class, 'edit'])->name('users.edit');
    Route::patch('users/{user}', [AuthController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AuthController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{id}/restore', [AuthController::class, 'restore'])->name('users.restore');

    // Items - Special routes BEFORE resource routes to avoid conflicts
    Route::get('/items/export/csv', [ItemController::class, 'export'])->name('items.export');
    Route::get('/items/status/low-stock', [ItemController::class, 'lowStock'])->name('items.lowStock');
    Route::get('/items/status/out-of-stock', [ItemController::class, 'outOfStock'])->name('items.outOfStock');
    Route::get('/items/status/trashed', [ItemController::class, 'trashed'])->name('items.trashed');
    Route::post('/items/bulk-stock-update', [ItemController::class, 'bulkStockUpdate'])->name('items.bulkStockUpdate');
    Route::post('/items/{id}/restore', [ItemController::class, 'restore'])->name('items.restore');
    Route::delete('/items/{id}/force-delete', [ItemController::class, 'forceDelete'])->name('items.forceDelete');
    Route::patch('/items/{item}/update-stock', [ItemController::class, 'updateStock'])->name('items.updateStock');

    // Items resource routes
    Route::resource('items', ItemController::class);

    // Customers
    Route::resource('customers', CustomerController::class);
    Route::get('/customers/export/csv', [CustomerController::class, 'export'])->name('customers.export');
    Route::get('/customers/trashed', [CustomerController::class, 'trashed'])->name('customers.trashed');
    Route::post('/customers/{id}/restore', [CustomerController::class, 'restore'])->name('customers.restore');
    Route::delete('/customers/{id}/force-delete', [CustomerController::class, 'forceDelete'])->name('customers.forceDelete');
    Route::post('/customers/{customer}/clear-loan', [CustomerController::class, 'clearLoan'])->name('customers.clearLoan');
    Route::post('/customers/{customer}/partial-payment', [CustomerController::class, 'partialPayment'])->name('customers.partialPayment');

    // Sales
    Route::resource('sales', SaleController::class);
    Route::get('/sales/export/csv', [SaleController::class, 'export'])->name('sales.export');
    Route::get('/sales/{sale}/pdf', [SaleController::class, 'downloadPdf'])->name('sales.downloadPdf');
    Route::get('/sales/customer/{customer}/history', [SaleController::class, 'history'])->name('sales.history');
    Route::post('/sales/{id}/restore', [SaleController::class, 'restore'])->name('sales.restore');
    Route::delete('/sales/{id}/force-delete', [SaleController::class, 'forceDelete'])->name('sales.forceDelete');

    // Suppliers
    Route::resource('suppliers', SupplierController::class);
    Route::post('/suppliers/{supplier}/toggle-status', [SupplierController::class, 'toggleStatus'])->name('suppliers.toggleStatus');

    // Purchases
    Route::resource('purchases', PurchaseController::class);
    Route::post('/purchases/{purchase}/complete', [PurchaseController::class, 'complete'])->name('purchases.complete');
    Route::get('/suppliers/{supplier}/history', [PurchaseController::class, 'supplierHistory'])->name('suppliers.history');

    // Financial Management
    Route::resource('income', IncomeController::class);
    Route::resource('expenses', ExpenseController::class);
    Route::resource('transactions', TransactionController::class);
    
    // Accounts
    Route::resource('accounts', AccountController::class);
    Route::post('/accounts/{account}/toggle-status', [AccountController::class, 'toggleStatus'])->name('accounts.toggleStatus');

    // Categories
    Route::resource('categories', CategoryController::class);
    Route::post('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggleStatus');

    // Owners
    Route::resource('owners', OwnerController::class);
    Route::post('/owners/{owner}/toggle-status', [OwnerController::class, 'toggleStatus'])->name('owners.toggleStatus');
    Route::get('/owners/{owner}/equity', [OwnerController::class, 'equity'])->name('owners.equity');

    // Item Sales (Profit/Loss Tracking)
    Route::resource('item-sales', ItemSaleController::class);

    // Inventory Adjustments
    Route::resource('inventory-adjustments', InventoryAdjustmentController::class);
    Route::post('/inventory-adjustments/quick-adjust', [InventoryAdjustmentController::class, 'quickAdjust'])->name('inventory-adjustments.quick-adjust');

    // Version Notifications
    Route::get('/version-notifications', [VersionNotificationController::class, 'index'])->name('version-notifications.index');
    Route::get('/version-notifications/check', [VersionNotificationController::class, 'checkPendingNotifications'])->name('version-notifications.check');
    Route::post('/version-notifications/mark-viewed', [VersionNotificationController::class, 'markAsViewed'])->name('version-notifications.mark-viewed');
    Route::post('/version-notifications/dismiss', [VersionNotificationController::class, 'dismiss'])->name('version-notifications.dismiss');
    Route::post('/version-notifications/dismiss-all', [VersionNotificationController::class, 'dismissAll'])->name('version-notifications.dismiss-all');
    
    // Version Notifications Admin
    Route::get('/admin/version-notifications', [VersionNotificationController::class, 'admin'])->name('version-notifications.admin');
    Route::get('/admin/version-notifications/create', [VersionNotificationController::class, 'create'])->name('version-notifications.create');
    Route::post('/admin/version-notifications', [VersionNotificationController::class, 'store'])->name('version-notifications.store');
    Route::get('/admin/version-notifications/{versionNotification}', [VersionNotificationController::class, 'show'])->name('version-notifications.show');
    Route::get('/admin/version-notifications/{versionNotification}/edit', [VersionNotificationController::class, 'edit'])->name('version-notifications.edit');
    Route::put('/admin/version-notifications/{versionNotification}', [VersionNotificationController::class, 'update'])->name('version-notifications.update');
    Route::delete('/admin/version-notifications/{versionNotification}', [VersionNotificationController::class, 'destroy'])->name('version-notifications.destroy');
    Route::post('/admin/version-notifications/{versionNotification}/toggle-status', [VersionNotificationController::class, 'toggleStatus'])->name('version-notifications.toggle-status');
});

// Authenticated admin routes
