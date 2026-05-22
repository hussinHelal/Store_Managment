<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ProfileController;
// use App\Http\Controllers\DebtsController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InstallmentsController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MaintenanceController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::get('/showLogin', [AuthController::class, 'showLogin'])->name('showLogin');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::get('/showRegister', [AuthController::class, 'showRegister'])->name('showRegister');

Route::middleware(['auth:sanctum'])->group(function () {
    
    Route::resource('/users', UserController::class);

    Route::resource('/installments', InstallmentsController::class)->except(['store', 'update', 'destroy']);
    Route::put('/installments/{installment}', [InstallmentsController::class, 'update'])->can('update-installments')
    ->name('installments.update');
    Route::get('/installments/{id}/pay', [InstallmentsController::class, 'showPay'])->can('update-installments')
    ->name('installments.showPay');
    Route::put('/installments/{installment}/pay', [InstallmentsController::class, 'pay'])->can('update-installments')
    ->name('installments.pay');
     Route::post('/installments', [InstallmentsController::class, 'store'])->can('create-installments')
    ->name('installments.store');
     Route::delete('/installments/{installment}', [InstallmentsController::class, 'destroy'])->can('delete-installments')
    ->name('installments.destroy');
    // Route::get('/profile', [ProfileController::class, 'index'])->name('profile');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    // Route::resource('/debts', DebtsController::class);
    Route::resource('/customers', CustomersController::class)->except(['store', 'update', 'destroy']);
        Route::post('/customers', [CustomersController::class, 'store'])->can('create-customers')
    ->name('customers.store');
        Route::put('/customers/{customer}', [CustomersController::class, 'update'])->can('update-customers')
    ->name('customers.update');
        Route::delete('/customers/{customer}', [CustomersController::class, 'destroy'])->can('delete-customers')
    ->name('customers.destroy');

    Route::resource('/sales', SalesController::class);
    Route::resource('/categories', CategoryController::class)->except(['store', 'update', 'destroy']);
        Route::post('/categories', [CategoryController::class, 'store'])->can('create-category')
    ->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->can('update-category')
    ->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->can('delete-category')
    ->name('categories.destroy');

    Route::resource('/products', ProductsController::class)->except(['store', 'update', 'destroy']);
        Route::post('/products', [ProductsController::class, 'store'])->can('create-products')
    ->name('products.store');
        Route::put('/products/{product}', [ProductsController::class, 'update'])->can('update-products')
    ->name('products.update');
        Route::delete('/products/{product}', [ProductsController::class, 'destroy'])->can('delete-products')
    ->name('products.destroy');

    Route::resource('/invoices', InvoiceController::class)->except(['store', 'update', 'destroy']);
        Route::post('/invoices', [InvoiceController::class, 'store'])->can('create-invoice')
    ->name('invoices.store');
        Route::put('/invoices/{invoice}', [InvoiceController::class, 'update'])->can('update-invoice')
    ->name('invoices.update');
        Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->can('delete-invoice')
    ->name('invoices.destroy');
        Route::post('/invoices/{invoice}/refund', [InvoiceController::class, 'refund'])->can('refund-invoice', 'invoice')
    ->name('invoices.refund');

    Route::resource('/profile', ProfileController::class)->except(['store', 'update', 'destroy']);
        Route::post('/profile', [ProfileController::class, 'store'])->can('create-profile')
    ->name('profile.store');
        Route::put('/profile', [ProfileController::class, 'update'])->can('update-profile')
    ->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->can('delete-profile')
    ->name('profile.destroy');

    Route::resource('/maintenance', MaintenanceController::class);
    Route::get('/maintenance/{maintenance}/showRepaired', [MaintenanceController::class, 'showRepaired'])->name('maintenance.showRepaired');
    Route::put('/maintenance/{maintenance}/repaired', [MaintenanceController::class, 'repaired'])->name('maintenance.repaired');
});

    // Route::get('/user', [AuthController::class, 'user'])->name('user');


    // Route::post('/post', [PostController::class, 'store'])->middleware('can:create-post,App\Models\Post')->name('postStore');
    // Route::put('/post/{post}', [PostController::class, 'update'])->middleware('can:update-post,post')->name('postUpdate');
    // Route::delete('/post/{post}', [PostController::class, 'destroy'])->middleware('can:destroy-post,post')->name('postDestroy');