<?php

use App\Http\Controllers\DamagedItemController;
use Illuminate\Support\Facades\Route;

/*
 * هالِك routes. Loaded from routes/web.php with:
 *     require __DIR__.'/damaged.php';
 *
 * EnsurePagePermission maps the "damaged." route-name prefix to
 * page.damaged.view (GET) and page.damaged.manage (create, store, void).
 */
Route::middleware(['auth:sanctum', 'page.access'])->prefix('damaged')->name('damaged.')->group(function (): void {
    Route::get('/', [DamagedItemController::class, 'index'])->name('index');
    Route::get('/create', [DamagedItemController::class, 'create'])->name('create');
    Route::post('/', [DamagedItemController::class, 'store'])->middleware('throttle:60,1')->name('store');
    Route::get('/report', [DamagedItemController::class, 'report'])->name('report');
    Route::get('/products/search', [DamagedItemController::class, 'searchProducts'])->name('products.search');
    Route::get('/{item}', [DamagedItemController::class, 'show'])->whereNumber('item')->name('show');
    Route::post('/{item}/void', [DamagedItemController::class, 'void'])->whereNumber('item')->name('void');
});
