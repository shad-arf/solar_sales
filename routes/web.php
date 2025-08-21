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
use App\Http\Controllers\ItemSaleController;
use App\Http\Controllers\InventoryAdjustmentController;


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

    // Item Sales (Profit/Loss Tracking)
    Route::resource('item-sales', ItemSaleController::class);

    // Inventory Adjustments
    Route::resource('inventory-adjustments', InventoryAdjustmentController::class);
    Route::post('/inventory-adjustments/quick-adjust', [InventoryAdjustmentController::class, 'quickAdjust'])->name('inventory-adjustments.quick-adjust');
});

// Authenticated admin routes
