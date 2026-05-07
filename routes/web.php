<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Product & Category Routes
Route::middleware(['auth'])->group(function () {
    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class);
});

// User Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Admin Dashboard
Route::middleware(['auth', 'role:Admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/transactions', [AdminController::class, 'transactions'])->name('admin.transactions');
    
    // User / Staff Management
    Route::resource('users', UserController::class);
});

// Cashier Dashboard
Route::middleware(['auth', 'role:Cashier'])->group(function () {
    Route::get('/cashier', [CashierController::class, 'dashboard'])->name('cashier.dashboard');
    Route::get('/cashier/pos', [CashierController::class, 'pos'])->name('cashier.pos');
    Route::get('/cashier/transactions', [CashierController::class, 'transactions'])->name('cashier.transactions');
    Route::get('/cashier/barcode', [CashierController::class, 'searchBarcode'])->name('cashier.barcode');
    Route::post('/cashier/checkout', [CashierController::class, 'checkout'])->name('cashier.checkout');
    Route::get('/cashier/receipt/{id}', [CashierController::class, 'receipt'])->name('cashier.receipt');
});

