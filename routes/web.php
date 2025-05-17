<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SaleController;

Route::get('/', fn () => redirect()->route('items.index'));

Route::resource('items', ItemController::class);
Route::resource('customers', CustomerController::class);
Route::resource('sales', SaleController::class);
