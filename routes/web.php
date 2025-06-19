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

Route::get('/sales/{sale}/pdf', [SaleController::class, 'downloadPdf'])->name('sales.downloadPdf');


Route::prefix('items')->name('items.')->group(function () {
    Route::get('/', [ItemController::class, 'index'])->name('index');
    Route::get('/create', [ItemController::class, 'create'])->name('create');
    Route::post('/', [ItemController::class, 'store'])->name('store');
    Route::get('/{item}', [ItemController::class, 'show'])->name('show');
    Route::get('/{item}/edit', [ItemController::class, 'edit'])->name('edit');
    Route::put('/{item}', [ItemController::class, 'update'])->name('update');
    Route::delete('/{item}', [ItemController::class, 'destroy'])->name('destroy');

    // New export and stock management routes
    Route::get('/export/csv', [ItemController::class, 'export'])->name('export');
    Route::patch('/{item}/update-stock', [ItemController::class, 'updateStock'])->name('updateStock');
    Route::post('/bulk-stock-update', [ItemController::class, 'bulkStockUpdate'])->name('bulkStockUpdate');

    // Dashboard route (optional)
    Route::get('/dashboard', [ItemController::class, 'dashboard'])->name('dashboard');

    // Existing additional routes
    Route::get('/status/low-stock', [ItemController::class, 'lowStock'])->name('lowStock');
    Route::get('/status/out-of-stock', [ItemController::class, 'outOfStock'])->name('outOfStock');
    Route::get('/status/trashed', [ItemController::class, 'trashed'])->name('trashed');
    Route::post('/{id}/restore', [ItemController::class, 'restore'])->name('restore');
    Route::delete('/{id}/force-delete', [ItemController::class, 'forceDelete'])->name('forceDelete');
});

Route::prefix('customers')->name('customers.')->group(function () {
    Route::get('/', [CustomerController::class, 'index'])->name('index');
    Route::get('/create', [CustomerController::class, 'create'])->name('create');
    Route::post('/', [CustomerController::class, 'store'])->name('store');
    Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
    Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
    Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');

    // New export route
    Route::get('/export/csv', [CustomerController::class, 'export'])->name('export');

    // Dashboard route (optional)
    Route::get('/dashboard', [CustomerController::class, 'dashboard'])->name('dashboard');

    // Existing additional routes
    Route::get('/trashed', [CustomerController::class, 'trashed'])->name('trashed');
    Route::post('/{id}/restore', [CustomerController::class, 'restore'])->name('restore');
    Route::delete('/{id}/force-delete', [CustomerController::class, 'forceDelete'])->name('forceDelete');
    Route::post('/{customer}/clear-loan', [CustomerController::class, 'clearLoan'])->name('clearLoan');
});
Route::prefix('sales')->name('sales.')->group(function () {
    Route::get('/', [SaleController::class, 'index'])->name('index');
    Route::get('/create', [SaleController::class, 'create'])->name('create');
    Route::post('/', [SaleController::class, 'store'])->name('store');
    Route::get('/{sale}', [SaleController::class, 'show'])->name('show');
    Route::get('/{sale}/edit', [SaleController::class, 'edit'])->name('edit');
    Route::put('/{sale}', [SaleController::class, 'update'])->name('update');
    Route::delete('/{sale}', [SaleController::class, 'destroy'])->name('destroy');

    // New export route
    Route::get('/export/csv', [SaleController::class, 'export'])->name('export');

    // Existing additional routes
    Route::post('/{id}/restore', [SaleController::class, 'restore'])->name('restore');
    Route::delete('/{id}/force-delete', [SaleController::class, 'forceDelete'])->name('forceDelete');
    Route::get('/customer/{customer}/history', [SaleController::class, 'history'])->name('history');
    Route::get('/{sale}/pdf', [SaleController::class, 'downloadPDF'])->name('downloadPDF');
});

// Customer loan clearing route
Route::post('/customers/{customer}/clear-loan', [CustomerController::class, 'clearLoan'])->name('customers.clearLoan');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/users', [AuthController::class, 'users'])->name('users.index');
    Route::post('/users/{id}/restore', [AuthController::class, 'restore'])->name('users.restore');
    Route::get('/users/{id}/edit', [AuthController::class, 'edit'])->name('users.edit');
    Route::post('users', [AuthController::class, 'store'])->name('users.store');


    Route::get('customers/trashed',         [CustomerController::class, 'trashed'])->name('customers.trashed');
    Route::post('customers/{id}/restore',   [CustomerController::class, 'restore'])->name('customers.restore');
    Route::delete('customers/{id}/force',   [CustomerController::class, 'forceDelete'])->name('customers.forceDelete');


    Route::get('users/create', [AuthController::class, 'create'])->name('users.create');
   Route::get('users/{id}/edit', [AuthController::class, 'edit'])->name('users.edit');  Route::patch('users/{user}', [AuthController::class, 'update'])->name('users.update');

    Route::delete('/users/{user}', [AuthController::class, 'destroy'])->name('users.destroy');


    Route::get('users/index', [AuthController::class, 'users'])->name('users.index');

    // Customers
    Route::resource('customers', CustomerController::class);
    Route::post('customers/{customer}/clear-loan', [CustomerController::class, 'clearLoan'])->name('customers.clearLoan');

    // Sales
    Route::resource('sales', SaleController::class);
    Route::get('sales/{customer}/history', [SaleController::class, 'history'])->name('sales.history');
    Route::post('sales/{sale}/restore', [SaleController::class, 'restore'])->name('sales.restore');
    Route::delete('sales/{sale}/force-delete', [SaleController::class, 'forceDelete'])->name('sales.forceDelete');

    // Items CRUD (without show)
    Route::get('items', [ItemController::class, 'index'])->name('items.index');
    Route::get('items/create', [ItemController::class, 'create'])->name('items.create');
    Route::post('items', [ItemController::class, 'store'])->name('items.store');
    Route::get('items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::put('items/{item}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');

    // Item soft-delete support
    Route::post('items/{id}/restore', [ItemController::class, 'restore'])->name('items.restore');
    Route::delete('items/{id}/force-delete', [ItemController::class, 'forceDelete'])->name('items.forceDelete');

    // Item extra filters
    Route::get('items/low-stock', [ItemController::class, 'lowStock'])->name('items.lowStock');
    Route::get('items/out-of-stock', [ItemController::class, 'outOfStock'])->name('items.outOfStock');
    Route::get('items/trashed', [ItemController::class, 'trashed'])->name('items.trashed');

});

// Authenticated admin routes
