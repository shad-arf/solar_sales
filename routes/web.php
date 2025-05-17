<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SaleController;

Route::get('/', fn () => redirect()->route('items.index'));
Route::post('customers/{customer}/clear-loan', [CustomerController::class, 'clearLoan'])->name('customers.clearLoan');
Route::get('sales/{customer}/history', [SaleController::class, 'history'])->name('sales.history');

Route::post('customers/{customer}/clear-loan', [CustomerController::class, 'clearLoan'])->name('customers.clearLoan');
Route::post('sales/{sale}/restore', [SaleController::class, 'restore'])->name('sales.restore');
Route::delete('sales/{sale}/force-delete', [SaleController::class, 'forceDelete'])->name('sales.forceDelete');

// Item CRUD routes without `show`
Route::get('items', [ItemController::class, 'index'])->name('items.index');
Route::get('items/create', [ItemController::class, 'create'])->name('items.create');
Route::post('items', [ItemController::class, 'store'])->name('items.store');
Route::get('items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
Route::put('items/{item}', [ItemController::class, 'update'])->name('items.update');
Route::delete('items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');

Route::resource('customers', CustomerController::class);
Route::resource('sales', SaleController::class);
// Soft delete support routes
Route::post('items/{id}/restore', [ItemController::class, 'restore'])->name('items.restore');
Route::delete('items/{id}/force-delete', [ItemController::class, 'forceDelete'])->name('items.forceDelete');


Route::get('/items/low-stock', [ItemController::class, 'lowStock'])->name('items.lowStock');
Route::get('/items/out-of-stock', [ItemController::class, 'outOfStock'])->name('items.outOfStock');
Route::get('/items/trashed', [ItemController::class, 'trashed'])->name('items.trashed');
