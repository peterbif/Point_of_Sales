<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BalanceAdjustmentController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::get('/user',  'user');
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });

    // Table
    Route::prefix('table')->controller(TableController::class)->group(function () {
        Route::post('list', 'list');
        Route::get('edit/{id}',  'edit');
        Route::match(['post', 'put'], 'save', 'save');
        Route::delete('delete/{id}', 'delete');
    });

    // Product Category
    Route::prefix('product-category')->controller(ProductCategoryController::class)->group(function () {
        Route::post('list', 'list');
        Route::get('edit/{id}',  'edit');
        Route::match(['post', 'put'], 'save', 'save');
        Route::delete('delete/{id}', 'delete');
    });

    // Product
    Route::prefix('product')->controller(ProductController::class)->group(function () {
        Route::post('list', 'list');
        Route::get('edit/{id}',  'edit');
        Route::match(['post', 'put'], 'save', 'save');
        Route::delete('delete/{id}', 'delete');
        Route::get('category-list', 'categoryList');
    });

    // Balance Adjustment
    Route::prefix('balance-adjustment')->controller(BalanceAdjustmentController::class)->group(function () {
        Route::post('list', 'list');
        Route::get('edit/{id}',  'edit');
        Route::match(['post', 'put'], 'save', 'save');
        Route::delete('delete/{id}', 'delete');
    });

    // Cashier
    Route::prefix('cashier')->controller(CashierController::class)->group(function () {
        Route::get('/', 'index');
        Route::get('show-table/{id?}',  'showTable');
        Route::post('select-table',  'selectTable');
        Route::post('update-order-qty', 'updateOrderQty');
        Route::post('update-detail-discount', 'updateDetailDiscount');
        Route::post('update-order-discount', 'updateOrderDiscount');
        Route::delete('delete-order/{product_id}/{table_id}', 'deleteOrder');
        Route::post('add-to-order', 'addToOrder');
        Route::post('print-invoice', 'printInvoice');
        Route::post('confirm-payment', 'confirmPayment');
    });

    // System User
    Route::prefix('user')->controller(UserController::class)->group(function () {
        Route::post('list', 'list');
        Route::get('edit/{id}',  'edit');
        Route::match(['post', 'put'], 'save', 'save');
        Route::delete('delete/{id}', 'delete');
    });

    // Report
    Route::prefix('report')->controller(ReportController::class)->group(function () {
        Route::post('sale-summary', 'saleSummary');
        Route::post('product-summary', 'productSummary');
        Route::post('sale-history', 'saleHistory');
        Route::post('sale-history-summary', 'saleHistorySummary');
        Route::post('export-product-summary', 'exportProductSummary');
        Route::post('export-sale-history', 'exportSaleHistory');
        Route::get('show-order-detail/{id}', 'showOrderDetail');
    });

    // Dashboard
    Route::get('dashboard', DashboardController::class);
});
