<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InstallmentController;

Route::get('/', function () { return view('index'); })->name('home');

Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::get('/showLogin', [AuthController::class, 'showLogin'])->name('showLogin');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/user', [AuthController::class, 'user'])->name('user');
Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::get('/showRegister', [AuthController::class, 'showRegister'])->name('showRegister');

Route::get('/profile/{user}', [ProfileController::class, 'show'])->name('profile');
Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
Route::resource('/debts', DebtController::class);
Route::resource('/installments', InstallmentController::class);
Route::resource('/customers', CustomerController::class);
Route::resource('/sales', SalesController::class);
Route::resource('/categories', CategoryController::class);
Route::resource('/products', ProductController::class);