<?php

use App\Http\Controllers\BranchController;
use App\Http\Controllers\BranchStockController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\StocktakeController;
use App\Http\Controllers\StockTransferController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'page.access'])->group(function (): void {
    Route::get('/branch-stock', [BranchStockController::class, 'index'])->name('branch_stock.index');
    Route::post('/branch-stock/adjust', [BranchStockController::class, 'adjust'])->name('branch_stock.adjust');
    Route::put('/branch-stock/{branchStock}/reorder-level', [BranchStockController::class, 'updateReorderLevel'])
        ->whereNumber('branchStock')->name('branch_stock.reorder-level');

    Route::post('/branches', [BranchController::class, 'store'])->name('branch_stock.branches.store');
    Route::put('/branches/{branch}', [BranchController::class, 'update'])->whereNumber('branch')->name('branch_stock.branches.update');
    Route::delete('/branches/{branch}', [BranchController::class, 'destroy'])->whereNumber('branch')->name('branch_stock.branches.destroy');

    Route::get('/stock-movements', [StockMovementController::class, 'index'])->name('stock_movements.index');

    Route::get('/stock-transfers', [StockTransferController::class, 'index'])->name('branch_stock.transfers.index');
    Route::post('/stock-transfers', [StockTransferController::class, 'store'])->name('branch_stock.transfers.store');
    Route::put('/stock-transfers/{transfer}', [StockTransferController::class, 'update'])->whereNumber('transfer')->name('branch_stock.transfers.update');
    Route::post('/stock-transfers/{transfer}/send', [StockTransferController::class, 'send'])->whereNumber('transfer')->name('branch_stock.transfers.send');
    Route::post('/stock-transfers/{transfer}/receive', [StockTransferController::class, 'receive'])->whereNumber('transfer')->name('branch_stock.transfers.receive');
    Route::post('/stock-transfers/{transfer}/cancel', [StockTransferController::class, 'cancel'])->whereNumber('transfer')->name('branch_stock.transfers.cancel');
    Route::delete('/stock-transfers/{transfer}', [StockTransferController::class, 'destroy'])->whereNumber('transfer')->name('branch_stock.transfers.destroy');

    Route::get('/stocktakes', [StocktakeController::class, 'index'])->name('stocktake.index');
    Route::post('/stocktakes', [StocktakeController::class, 'store'])->name('stocktake.store');
    Route::get('/stocktakes/{stocktake}', [StocktakeController::class, 'show'])->whereNumber('stocktake')->name('stocktake.show');
    Route::put('/stocktakes/{stocktake}/items/{item}', [StocktakeController::class, 'count'])->whereNumber('stocktake')->whereNumber('item')->name('stocktake.count');
    Route::post('/stocktakes/{stocktake}/review', [StocktakeController::class, 'review'])->whereNumber('stocktake')->name('stocktake.review');
    Route::post('/stocktakes/{stocktake}/approve', [StocktakeController::class, 'approve'])->whereNumber('stocktake')->name('stocktake.approve');
    Route::post('/stocktakes/{stocktake}/cancel', [StocktakeController::class, 'cancel'])->whereNumber('stocktake')->name('stocktake.cancel');
});
