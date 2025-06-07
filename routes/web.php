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
