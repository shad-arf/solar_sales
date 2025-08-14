<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\AuthController;


Route::get('register', [AuthController::class, 'showRegisterForm'])->name('register.form');
Route::post('register', [AuthController::class, 'register'])->name('register');


Route::get('login', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::post('login', [AuthController::class, 'login'])->name('login');

Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// Redirect to items page
Route::get('/', fn () => redirect()->route('items.index'));

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // Users management
    Route::get('/users', [AuthController::class, 'users'])->name('users.index');
    Route::get('users/create', [AuthController::class, 'create'])->name('users.create');
    Route::post('users', [AuthController::class, 'store'])->name('users.store');
    Route::get('users/{id}/edit', [AuthController::class, 'edit'])->name('users.edit');
    Route::patch('users/{user}', [AuthController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AuthController::class, 'destroy'])->name('users.destroy');
    Route::post('/users/{id}/restore', [AuthController::class, 'restore'])->name('users.restore');

    // Items
    Route::resource('items', ItemController::class);
    Route::get('/items/export/csv', [ItemController::class, 'export'])->name('items.export');
    Route::patch('/items/{item}/update-stock', [ItemController::class, 'updateStock'])->name('items.updateStock');
    Route::post('/items/bulk-stock-update', [ItemController::class, 'bulkStockUpdate'])->name('items.bulkStockUpdate');
    Route::get('/items/status/low-stock', [ItemController::class, 'lowStock'])->name('items.lowStock');
    Route::get('/items/status/out-of-stock', [ItemController::class, 'outOfStock'])->name('items.outOfStock');
    Route::get('/items/status/trashed', [ItemController::class, 'trashed'])->name('items.trashed');
    Route::post('/items/{id}/restore', [ItemController::class, 'restore'])->name('items.restore');
    Route::delete('/items/{id}/force-delete', [ItemController::class, 'forceDelete'])->name('items.forceDelete');

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
});

// Authenticated admin routes
