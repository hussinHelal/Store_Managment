<?php

use App\Http\Controllers\WalletAuditController;
use App\Http\Controllers\WalletCashierController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\WalletExpenseController;
use App\Http\Controllers\WalletReportController;
use Illuminate\Support\Facades\Route;

/*
 * Wallet module routes. Loaded from routes/web.php with:
 *     require __DIR__.'/wallets.php';
 *
 * EnsurePagePermission derives the page from the route-name prefix:
 *   wallets.*        -> page.wallets.view / page.wallets.manage
 *   wallet_cashier.* -> page.wallet_cashier.view / page.wallet_cashier.manage
 * GET = view, any other method (and create/edit) = manage.
 */
Route::middleware(['auth:sanctum', 'page.access'])->group(function (): void {
    // Wallets
    Route::get('/wallets', [WalletController::class, 'index'])->name('wallets.index');
    Route::get('/wallets/create', [WalletController::class, 'create'])->name('wallets.create');
    Route::post('/wallets', [WalletController::class, 'store'])->name('wallets.store');
    Route::get('/wallets/{wallet}/edit', [WalletController::class, 'edit'])->name('wallets.edit');
    Route::put('/wallets/{wallet}', [WalletController::class, 'update'])->name('wallets.update');
    Route::post('/wallets/{wallet}/toggle', [WalletController::class, 'toggle'])->name('wallets.toggle');
    Route::post('/wallets/{wallet}/adjust', [WalletController::class, 'adjust'])->name('wallets.adjust');

    // Expenses and cash drawer
    Route::get('/wallets/expenses', [WalletExpenseController::class, 'index'])->name('wallets.expenses.index');
    Route::post('/wallets/expenses', [WalletExpenseController::class, 'store'])->name('wallets.expenses.store');
    Route::post('/wallets/expenses/{expense}/void', [WalletExpenseController::class, 'void'])->whereNumber('expense')->name('wallets.expenses.void');
    Route::post('/wallets/cash', [WalletExpenseController::class, 'storeCash'])->name('wallets.cash.store');

    // Reports and audit log
    Route::get('/wallets/reports', [WalletReportController::class, 'index'])->name('wallets.reports.index');
    Route::get('/wallets/audit', [WalletAuditController::class, 'index'])->name('wallets.audit.index');

    // Cashier
    Route::get('/wallet-cashier', [WalletCashierController::class, 'index'])->name('wallet_cashier.index');
    Route::get('/wallet-cashier/debts', [WalletCashierController::class, 'debts'])->name('wallet_cashier.debts');
    Route::post('/wallet-cashier', [WalletCashierController::class, 'store'])
        ->middleware('throttle:60,1')->name('wallet_cashier.store');
    Route::post('/wallet-cashier/{transaction}/reverse', [WalletCashierController::class, 'reverse'])
        ->name('wallets.cashier_transactions.reverse');
    Route::post('/wallet-cashier/{transaction}/settle', [WalletCashierController::class, 'settle'])
        ->name('wallet_cashier.settle');
});
