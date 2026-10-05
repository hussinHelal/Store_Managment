<?php

use App\Http\Controllers\CashierController;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Support\Facades\Route;

/*
 * Invoice cashier (point of sale). Loaded from routes/web.php with:
 *     require __DIR__.'/cashier.php';
 *
 * Anyone who may create invoices (the existing create-invoice gate) can use it.
 * EnsureUserIsActive signs out and blocks a deactivated account.
 */
Route::middleware(['auth:web', EnsureUserIsActive::class, 'can:create-invoice', 'can:page.invoices.manage'])
    ->prefix('cashier')->name('cashier.')->group(function (): void {
        Route::get('/', [CashierController::class, 'index'])->name('index');
        Route::get('/products/search', [CashierController::class, 'search'])->name('products.search');
        Route::post('/checkout', [CashierController::class, 'checkout'])
            ->middleware('throttle:60,1')->name('checkout');
    });
