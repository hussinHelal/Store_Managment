<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DebtsController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InstallmentsController;
use App\Http\Controllers\InvoiceController;

Route::get('/', function () { return view('index'); })->name('home');

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::get('/showLogin', [AuthController::class, 'showLogin'])->name('showLogin');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::get('/showRegister', [AuthController::class, 'showRegister'])->name('showRegister');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::resource('/installments', InstallmentsController::class);
    // Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::resource('/debts', DebtsController::class);
    Route::resource('/customers', CustomersController::class);
    Route::resource('/sales', SalesController::class);
    Route::resource('/categories', CategoryController::class);
    Route::resource('/products', ProductsController::class);
    Route::resource('/invoices', InvoiceController::class);
    Route::resource('/profile', ProfileController::class)->names([
        'index' => 'profile.index',
        'edit' => 'profile.edit',
        'update' => 'profile.update',
    ]);
});

    // Route::get('/user', [AuthController::class, 'user'])->name('user');