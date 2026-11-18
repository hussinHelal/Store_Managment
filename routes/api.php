<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\BranchStockController;
use App\Http\Controllers\Api\CashierController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DamagedItemController;
use App\Http\Controllers\Api\InstallmentController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PersonalDebtController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\SalesController;
use App\Http\Controllers\Api\SalesReportController;
use App\Http\Controllers\Api\StocktakeController;
use App\Http\Controllers\Api\StockTransferController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WalletCashierController;
use App\Http\Controllers\Api\WalletController;
use App\Http\Controllers\BackupController;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Support\Facades\Route;

Route::get('/user', [UserController::class, 'legacy'])
    ->middleware(['auth:sanctum', EnsureUserIsActive::class]);

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::post('/auth/token', [AuthController::class, 'store'])
        ->middleware('throttle:login')
        ->name('auth.token');

    Route::middleware(['auth:sanctum', EnsureUserIsActive::class])->group(function (): void {
        Route::get('/devices', [App\Http\Controllers\Api\TrackedDeviceController::class, 'index'])->middleware(['abilities:page.devices.view', 'can:page.devices.view'])->name('devices.index');
        Route::get('/devices/{device}', [App\Http\Controllers\Api\TrackedDeviceController::class, 'show'])->whereNumber('device')->middleware(['abilities:page.devices.view', 'can:page.devices.view'])->name('devices.show');
        Route::post('/devices', [App\Http\Controllers\Api\TrackedDeviceController::class, 'store'])->middleware(['abilities:page.devices.manage', 'can:page.devices.manage', 'throttle:api'])->name('devices.store');
        Route::put('/devices/{device}', [App\Http\Controllers\Api\TrackedDeviceController::class, 'update'])->whereNumber('device')->middleware(['abilities:page.devices.manage', 'can:page.devices.manage', 'throttle:api'])->name('devices.update');
        Route::delete('/devices/{device}', [App\Http\Controllers\Api\TrackedDeviceController::class, 'destroy'])->whereNumber('device')->middleware(['abilities:page.devices.manage', 'can:page.devices.manage', 'throttle:api'])->name('devices.destroy');

        Route::get('/used-phones', [App\Http\Controllers\Api\UsedPhoneController::class, 'index'])
            ->middleware(['abilities:page.used_phones.view', 'can:page.used_phones.view'])->name('used_phones.index');
        Route::post('/used-phones', [App\Http\Controllers\Api\UsedPhoneController::class, 'store'])
            ->middleware(['abilities:page.used_phones.manage', 'can:page.used_phones.manage', 'throttle:api'])->name('used_phones.store');
        Route::put('/used-phones/{usedPhone}', [App\Http\Controllers\Api\UsedPhoneController::class, 'update'])->whereNumber('usedPhone')
            ->middleware(['abilities:page.used_phones.manage', 'can:page.used_phones.manage', 'throttle:api'])->name('used_phones.update');
        Route::delete('/used-phones/{usedPhone}', [App\Http\Controllers\Api\UsedPhoneController::class, 'destroy'])->whereNumber('usedPhone')
            ->middleware(['abilities:page.used_phones.manage', 'can:page.used_phones.manage', 'throttle:api'])->name('used_phones.destroy');

        Route::get('/installation-appointments', [App\Http\Controllers\Api\InstallationAppointmentController::class, 'index'])
            ->middleware(['abilities:page.installations.view', 'can:page.installations.view'])->name('installations.index');
        Route::post('/installation-appointments', [App\Http\Controllers\Api\InstallationAppointmentController::class, 'store'])
            ->middleware(['abilities:page.installations.manage', 'can:page.installations.manage', 'throttle:api'])->name('installations.store');
        Route::put('/installation-appointments/{appointment}', [App\Http\Controllers\Api\InstallationAppointmentController::class, 'update'])->whereNumber('appointment')
            ->middleware(['abilities:page.installations.manage', 'can:page.installations.manage', 'throttle:api'])->name('installations.update');
        Route::delete('/installation-appointments/{appointment}', [App\Http\Controllers\Api\InstallationAppointmentController::class, 'destroy'])->whereNumber('appointment')
            ->middleware(['abilities:page.installations.manage', 'can:page.installations.manage', 'throttle:api'])->name('installations.destroy');
        Route::get('/branches', [BranchController::class, 'index'])
            ->middleware(['abilities:page.branch_stock.view', 'can:page.branch_stock.view'])->name('branch_stock.branches.index');
        Route::post('/branches', [BranchController::class, 'store'])
            ->middleware(['abilities:page.branch_stock.manage', 'can:page.branch_stock.manage', 'throttle:api'])->name('branch_stock.branches.store');
        Route::put('/branches/{branch}', [BranchController::class, 'update'])->whereNumber('branch')
            ->middleware(['abilities:page.branch_stock.manage', 'can:page.branch_stock.manage', 'throttle:api'])->name('branch_stock.branches.update');
        Route::delete('/branches/{branch}', [BranchController::class, 'destroy'])->whereNumber('branch')
            ->middleware(['abilities:page.branch_stock.manage', 'can:page.branch_stock.manage', 'throttle:api'])->name('branch_stock.branches.destroy');
        Route::get('/branch-stock', [BranchStockController::class, 'index'])
            ->middleware(['abilities:page.branch_stock.view', 'can:page.branch_stock.view'])->name('branch_stock.index');
        Route::put('/branch-stock/{branchStock}/reorder-level', [BranchStockController::class, 'reorderLevel'])->whereNumber('branchStock')
            ->middleware(['abilities:page.branch_stock.manage', 'can:page.branch_stock.manage', 'throttle:api'])->name('branch_stock.reorder');
        Route::get('/stock-movements', [BranchStockController::class, 'movements'])
            ->middleware(['abilities:page.stock_movements.view', 'can:page.stock_movements.view'])->name('stock_movements.index');
        Route::post('/stock-movements/adjust', [BranchStockController::class, 'adjust'])
            ->middleware(['abilities:page.stock_movements.manage', 'can:page.stock_movements.manage', 'throttle:api'])->name('stock_movements.adjust');
        Route::get('/stock-transfers', [StockTransferController::class, 'index'])
            ->middleware(['abilities:page.branch_stock.view', 'can:page.branch_stock.view'])->name('branch_stock.transfers.index');
        Route::post('/stock-transfers', [StockTransferController::class, 'store'])
            ->middleware(['abilities:page.branch_stock.manage', 'can:page.branch_stock.manage', 'throttle:api'])->name('branch_stock.transfers.store');
        Route::put('/stock-transfers/{transfer}', [StockTransferController::class, 'update'])->whereNumber('transfer')
            ->middleware(['abilities:page.branch_stock.manage', 'can:page.branch_stock.manage', 'throttle:api'])->name('branch_stock.transfers.update');
        Route::post('/stock-transfers/{transfer}/send', [StockTransferController::class, 'send'])->whereNumber('transfer')
            ->middleware(['abilities:page.branch_stock.manage', 'can:page.branch_stock.manage', 'throttle:api'])->name('branch_stock.transfers.send');
        Route::post('/stock-transfers/{transfer}/receive', [StockTransferController::class, 'receive'])->whereNumber('transfer')
            ->middleware(['abilities:page.branch_stock.manage', 'can:page.branch_stock.manage', 'throttle:api'])->name('branch_stock.transfers.receive');
        Route::post('/stock-transfers/{transfer}/cancel', [StockTransferController::class, 'cancel'])->whereNumber('transfer')
            ->middleware(['abilities:page.branch_stock.manage', 'can:page.branch_stock.manage', 'throttle:api'])->name('branch_stock.transfers.cancel');
        Route::delete('/stock-transfers/{transfer}', [StockTransferController::class, 'destroy'])->whereNumber('transfer')
            ->middleware(['abilities:page.branch_stock.manage', 'can:page.branch_stock.manage', 'throttle:api'])->name('branch_stock.transfers.destroy');
        Route::get('/stocktakes', [StocktakeController::class, 'index'])
            ->middleware(['abilities:page.stocktake.view', 'can:page.stocktake.view'])->name('stocktake.index');
        Route::post('/stocktakes', [StocktakeController::class, 'store'])
            ->middleware(['abilities:page.stocktake.manage', 'can:page.stocktake.manage', 'throttle:api'])->name('stocktake.store');
        Route::get('/stocktakes/{stocktake}', [StocktakeController::class, 'show'])->whereNumber('stocktake')
            ->middleware(['abilities:page.stocktake.view', 'can:page.stocktake.view'])->name('stocktake.show');
        Route::put('/stocktakes/{stocktake}/items/{item}', [StocktakeController::class, 'count'])->whereNumber('stocktake')->whereNumber('item')
            ->middleware(['abilities:page.stocktake.manage', 'can:page.stocktake.manage', 'throttle:api'])->name('stocktake.count');
        Route::post('/stocktakes/{stocktake}/review', [StocktakeController::class, 'review'])->whereNumber('stocktake')
            ->middleware(['abilities:page.stocktake.manage', 'can:page.stocktake.manage', 'throttle:api'])->name('stocktake.review');
        Route::post('/stocktakes/{stocktake}/approve', [StocktakeController::class, 'approve'])->whereNumber('stocktake')
            ->middleware(['abilities:page.stocktake.approve', 'can:page.stocktake.approve', 'throttle:api'])->name('stocktake.approve');
        Route::post('/stocktakes/{stocktake}/cancel', [StocktakeController::class, 'cancel'])->whereNumber('stocktake')
            ->middleware(['abilities:page.stocktake.manage', 'can:page.stocktake.manage', 'throttle:api'])->name('stocktake.cancel');
        Route::get('/me', [UserController::class, 'show'])
            ->middleware('abilities:api:me')
            ->name('me');
        Route::get('/profile', [ProfileController::class, 'show'])
            ->middleware('abilities:api:me')
            ->name('profile.show');
        Route::get('/backup', [BackupController::class, 'apiExport'])
            ->middleware(['abilities:page.backup.manage', 'can:page.backup.manage'])
            ->name('backup.export');
        Route::post('/backup/import', [BackupController::class, 'apiImport'])
            ->middleware(['abilities:page.backup.manage', 'can:page.backup.manage', 'throttle:api'])
            ->name('backup.import');
        Route::get('/damaged/products', [DamagedItemController::class, 'products'])
            ->middleware(['abilities:page.damaged.view', 'can:page.damaged.view'])
            ->name('damaged.products');
        Route::get('/damaged', [DamagedItemController::class, 'index'])
            ->middleware(['abilities:page.damaged.view', 'can:page.damaged.view'])
            ->name('damaged.index');
        Route::post('/damaged', [DamagedItemController::class, 'store'])
            ->middleware(['abilities:page.damaged.manage', 'can:page.damaged.manage'])
            ->name('damaged.store');
        Route::get('/damaged/{item}', [DamagedItemController::class, 'show'])
            ->whereNumber('item')
            ->middleware(['abilities:page.damaged.view', 'can:page.damaged.view'])
            ->name('damaged.show');
        Route::put('/damaged/{item}', [DamagedItemController::class, 'update'])
            ->whereNumber('item')
            ->middleware(['abilities:page.damaged.manage', 'can:page.damaged.manage'])
            ->name('damaged.update');
        Route::post('/damaged/{item}/void', [DamagedItemController::class, 'void'])
            ->whereNumber('item')
            ->middleware(['abilities:page.damaged.manage', 'can:page.damaged.manage'])
            ->name('damaged.void');
        Route::put('/profile', [ProfileController::class, 'update'])
            ->middleware('abilities:api:me')
            ->name('profile.update');
        Route::get('/products', [ProductController::class, 'index'])
            ->middleware(['abilities:page.products.view', 'can:view-products'])
            ->name('products.index');
        Route::post('/products', [ProductController::class, 'store'])
            ->middleware(['abilities:page.products.manage', 'can:create-products'])
            ->name('products.store');
        Route::put('/products/{product}', [ProductController::class, 'update'])
            ->whereNumber('product')
            ->middleware(['abilities:page.products.manage', 'can:update-products,product'])
            ->name('products.update');
        Route::post('/products/{product}/image', [ProductController::class, 'updateImage'])
            ->whereNumber('product')
            ->middleware(['abilities:page.products.manage', 'can:update-products,product'])
            ->name('products.image.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])
            ->whereNumber('product')
            ->middleware(['abilities:page.products.manage', 'can:delete-products,product'])
            ->name('products.destroy');
        Route::get('/products/{product}', [ProductController::class, 'show'])
            ->whereNumber('product')
            ->middleware(['abilities:page.products.view', 'can:view-products'])
            ->name('products.show');
        Route::get('/categories', [CategoryController::class, 'index'])
            ->middleware(['abilities:page.categories.view', 'can:view-category'])
            ->name('categories.index');
        Route::get('/categories/{category}', [CategoryController::class, 'show'])
            ->whereNumber('category')
            ->middleware(['abilities:page.categories.view', 'can:view-category'])
            ->name('categories.show');
        Route::post('/categories', [CategoryController::class, 'store'])
            ->middleware(['abilities:page.categories.manage', 'can:create-category'])
            ->name('categories.store');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])
            ->whereNumber('category')
            ->middleware(['abilities:page.categories.manage', 'can:update-category,category'])
            ->name('categories.update');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])
            ->whereNumber('category')
            ->middleware(['abilities:page.categories.manage', 'can:delete-category,category'])
            ->name('categories.destroy');
        Route::get('/customers', [CustomerController::class, 'index'])
            ->middleware(['abilities:page.customers.view', 'can:view-customers'])
            ->name('customers.index');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])
            ->whereNumber('customer')
            ->middleware(['abilities:page.customers.view', 'can:view-customers'])
            ->name('customers.show');
        Route::post('/customers', [CustomerController::class, 'store'])
            ->middleware(['abilities:page.customers.manage', 'can:create-customers'])
            ->name('customers.store');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])
            ->whereNumber('customer')
            ->middleware(['abilities:page.customers.manage', 'can:update-customers,customer'])
            ->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])
            ->whereNumber('customer')
            ->middleware(['abilities:page.customers.manage', 'can:delete-customers,customer'])
            ->name('customers.destroy');
        Route::get('/invoices', [InvoiceController::class, 'index'])
            ->middleware(['abilities:page.invoices.view', 'can:view-invoice'])
            ->name('invoices.index');
        Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])
            ->whereNumber('invoice')
            ->middleware(['abilities:page.invoices.view', 'can:view-invoice'])
            ->name('invoices.pdf');
        Route::get('/cashier/products', [CashierController::class, 'products'])
            ->middleware(['abilities:page.invoices.manage', 'can:create-invoice'])
            ->name('cashier.products');
        Route::post('/cashier/checkout', [CashierController::class, 'checkout'])
            ->middleware(['abilities:page.invoices.manage', 'can:create-invoice'])
            ->name('cashier.checkout');
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])
            ->whereNumber('invoice')
            ->middleware(['abilities:page.invoices.view', 'can:view-invoice'])
            ->name('invoices.show');
        Route::put('/invoices/{invoice}', [InvoiceController::class, 'update'])
            ->whereNumber('invoice')
            ->middleware(['abilities:page.invoices.manage', 'can:update-invoice,invoice'])
            ->name('invoices.update');
        Route::post('/invoices/{invoice}/pay', [InvoiceController::class, 'pay'])
            ->whereNumber('invoice')
            ->middleware(['abilities:page.invoices.manage', 'can:create-invoice'])
            ->name('invoices.pay');
        Route::post('/invoices/{invoice}/refund', [InvoiceController::class, 'refund'])
            ->whereNumber('invoice')
            ->middleware(['abilities:page.invoices.manage', 'can:update-invoice,invoice'])
            ->name('invoices.refund');
        Route::get('/installments', [InstallmentController::class, 'index'])
            ->middleware(['abilities:page.installments.view', 'can:view-installments'])
            ->name('installments.index');
        Route::get('/personal-debts', [PersonalDebtController::class, 'index'])
            ->middleware(['abilities:page.installments.view', 'can:view-installments'])
            ->name('personal-debts.index');
        Route::post('/personal-debts', [PersonalDebtController::class, 'store'])
            ->middleware(['abilities:page.installments.manage', 'can:create-installments'])
            ->name('personal-debts.store');
        Route::get('/personal-debts/{debt}', [PersonalDebtController::class, 'show'])
            ->whereNumber('debt')
            ->middleware(['abilities:page.installments.view', 'can:view-installments'])
            ->name('personal-debts.show');
        Route::put('/personal-debts/{debt}', [PersonalDebtController::class, 'update'])
            ->whereNumber('debt')
            ->middleware(['abilities:page.installments.manage', 'can:update-installments'])
            ->name('personal-debts.update');
        Route::delete('/personal-debts/{debt}', [PersonalDebtController::class, 'destroy'])
            ->whereNumber('debt')
            ->middleware(['abilities:page.installments.manage', 'can:delete-installments'])
            ->name('personal-debts.destroy');
        Route::post('/personal-debts/{debt}/pay', [PersonalDebtController::class, 'pay'])
            ->whereNumber('debt')
            ->middleware(['abilities:page.installments.manage', 'can:update-installments'])
            ->name('personal-debts.pay');
        Route::get('/installments/{installment}', [InstallmentController::class, 'show'])
            ->whereNumber('installment')
            ->middleware(['abilities:page.installments.view', 'can:view-installments'])
            ->name('installments.show');
        Route::post('/installments', [InstallmentController::class, 'store'])
            ->middleware(['abilities:page.installments.manage', 'can:create-installments'])
            ->name('installments.store');
        Route::put('/installments/{installment}', [InstallmentController::class, 'update'])
            ->whereNumber('installment')
            ->middleware(['abilities:page.installments.manage', 'can:update-installments,installment'])
            ->name('installments.update');
        Route::post('/installments/{installment}/pay', [InstallmentController::class, 'pay'])
            ->whereNumber('installment')
            ->middleware(['abilities:page.installments.manage', 'can:update-installments,installment'])
            ->name('installments.pay');
        Route::post('/installments/{installment}/status', [InstallmentController::class, 'changeStatus'])
            ->whereNumber('installment')
            ->middleware(['abilities:page.installments.manage', 'can:update-installments,installment'])
            ->name('installments.status');
        Route::delete('/installments/{installment}', [InstallmentController::class, 'destroy'])
            ->whereNumber('installment')
            ->middleware(['abilities:page.installments.manage', 'can:delete-installments,installment'])
            ->name('installments.destroy');
        Route::get('/suppliers', [SupplierController::class, 'index'])
            ->middleware(['abilities:page.suppliers.view', 'can:page.suppliers.view'])
            ->name('suppliers.index');
        Route::get('/suppliers/receipt-products', [SupplierController::class, 'receiptProducts'])
            ->middleware(['abilities:page.suppliers.manage', 'can:page.suppliers.manage'])
            ->name('suppliers.receipt-products');
        Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])
            ->whereNumber('supplier')
            ->middleware(['abilities:page.suppliers.view', 'can:page.suppliers.view'])
            ->name('suppliers.show');
        Route::get('/suppliers/{supplier}/statement', [SupplierController::class, 'statement'])
            ->whereNumber('supplier')
            ->middleware(['abilities:page.suppliers.view', 'can:page.suppliers.view'])
            ->name('suppliers.statement');
        Route::post('/suppliers/{supplier}/receipts', [SupplierController::class, 'receive'])
            ->whereNumber('supplier')
            ->middleware(['abilities:page.suppliers.manage', 'can:page.suppliers.manage', 'throttle:api'])
            ->name('suppliers.receipts.store');
        Route::post('/suppliers', [SupplierController::class, 'store'])
            ->middleware(['abilities:page.suppliers.manage', 'can:page.suppliers.manage'])
            ->name('suppliers.store');
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])
            ->whereNumber('supplier')
            ->middleware(['abilities:page.suppliers.manage', 'can:page.suppliers.manage'])
            ->name('suppliers.update');
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])
            ->whereNumber('supplier')
            ->middleware(['abilities:page.suppliers.manage', 'can:page.suppliers.manage'])
            ->name('suppliers.destroy');
        Route::post('/invoices', [InvoiceController::class, 'store'])
            ->middleware(['abilities:page.invoices.manage', 'can:create-invoice'])
            ->name('invoices.store');
        Route::get('/wallets', [WalletController::class, 'index'])
            ->middleware(['abilities:page.wallets.view', 'can:page.wallets.view'])
            ->name('wallets.index');
        Route::post('/wallets', [WalletController::class, 'store'])
            ->middleware(['abilities:page.wallets.manage', 'can:page.wallets.manage'])
            ->name('wallets.store');
        Route::post('/wallets/{wallet}/toggle', [WalletController::class, 'toggle'])
            ->whereNumber('wallet')
            ->middleware(['abilities:page.wallets.manage', 'can:page.wallets.manage'])
            ->name('wallets.toggle');
        Route::post('/wallets/{wallet}/adjust', [WalletController::class, 'adjust'])
            ->whereNumber('wallet')
            ->middleware(['abilities:page.wallets.manage', 'can:page.wallets.manage'])
            ->name('wallets.adjust');
        Route::get('/sales', [SalesController::class, 'index'])
            ->middleware(['abilities:page.sales.view', 'can:page.sales.view'])
            ->name('sales.index');
        Route::get('/sales-reports', [SalesReportController::class, 'index'])
            ->middleware(['abilities:page.sales_reports.view', 'can:page.sales_reports.view'])
            ->name('sales_reports.index');
        Route::get('/notifications', [NotificationController::class, 'index'])
            ->middleware(['abilities:page.notifications.view', 'can:page.notifications.view'])
            ->name('notifications.index');
        Route::get('/maintenance', [MaintenanceController::class, 'index'])
            ->middleware(['abilities:page.maintenance.view', 'can:page.maintenance.view'])
            ->name('maintenance.index');
        Route::get('/maintenance/{maintenance}', [MaintenanceController::class, 'show'])
            ->whereNumber('maintenance')
            ->middleware(['abilities:page.maintenance.view', 'can:page.maintenance.view'])
            ->name('maintenance.show');
        Route::post('/maintenance', [MaintenanceController::class, 'store'])
            ->middleware(['abilities:page.maintenance.manage', 'can:page.maintenance.manage'])
            ->name('maintenance.store');
        Route::put('/maintenance/{maintenance}', [MaintenanceController::class, 'update'])
            ->whereNumber('maintenance')
            ->middleware(['abilities:page.maintenance.manage', 'can:page.maintenance.manage'])
            ->name('maintenance.update');
        Route::delete('/maintenance/{maintenance}', [MaintenanceController::class, 'destroy'])
            ->whereNumber('maintenance')
            ->middleware(['abilities:page.maintenance.manage', 'can:page.maintenance.manage'])
            ->name('maintenance.destroy');
        Route::get('/wallet-cashier', [WalletCashierController::class, 'index'])
            ->middleware(['abilities:page.wallet_cashier.view', 'can:page.wallet_cashier.view'])
            ->name('wallet-cashier.index');
        Route::post('/wallet-cashier', [WalletCashierController::class, 'store'])
            ->middleware(['abilities:page.wallet_cashier.manage', 'can:page.wallet_cashier.manage'])
            ->name('wallet-cashier.store');
        Route::post('/wallet-cashier/{transaction}/settle', [WalletCashierController::class, 'settle'])
            ->middleware(['abilities:page.wallet_cashier.manage', 'can:page.wallet_cashier.manage'])
            ->name('wallet-cashier.settle');
        Route::post('/wallet-cashier/{transaction}/reverse', [WalletCashierController::class, 'reverse'])
            ->middleware(['abilities:page.wallets.manage', 'can:page.wallets.manage'])
            ->name('wallet-cashier.reverse');
        Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])
            ->middleware(['abilities:page.notifications.view', 'can:page.notifications.view'])
            ->name('notifications.read-all');
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])
            ->whereNumber('notification')
            ->middleware(['abilities:page.notifications.view', 'can:page.notifications.view'])
            ->name('notifications.read');
        Route::delete('/auth/token', [AuthController::class, 'destroy'])
            ->name('auth.token.revoke');
    });
});
