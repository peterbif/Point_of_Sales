<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BalanceAdjustmentController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockAlertsController;
use App\Http\Controllers\StockExpiryController;
use App\Http\Controllers\TableController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VatController;
use App\Models\StockAlerts;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);
// Route::post('/logout', [AuthController::class, 'logout']);

Route::middleware(['auth:sanctum', 'subscription'])->group(function () {


    // Route::get('/ping', function () {
    //     return response()->json(['status' => 'ok']);
    // });

    // Auth
    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user',  'user');
        Route::post('/change-password', [AuthController::class, 'changePassword']);
    });


// reset sub
    Route::prefix('reset-date')->controller(AuthController::class)->group(function () {
        Route::get('list', 'getUser');
        Route::put('edit/{id}',  'resetDate');   
     });

   

    // orders
    Route::prefix('orders')->controller(OrderController::class)->group(function () {
        Route::post('search', 'searchOrders');
        Route::get('edit/{id}',  'edit');
        Route::match(['post', 'put'], 'save', 'save');
        Route::delete('delete/{id}', 'deleteOrderDetail');
    });



    // vats
    Route::prefix('vats')->controller(VatController::class)->group(function () {

        Route::post('vat', 'list');
        Route::get('getVat', 'getVat');
        Route::match(['post', 'put'], 'save', 'save');
        Route::delete('delete/{id}', 'deleteVat');
    });



    // stock-alerts
    Route::prefix('stock-alerts')->controller(StockAlertsController::class)->group(function () {
        Route::post('list', 'list');
        Route::match(['post', 'put'], 'save', 'save');
        Route::delete('delete/{id}', 'deleteStockAlert');
        Route::get('stock-alerts', 'getStockAlerts');
        Route::get('expiry-alerts', 'getExpiryAlerts');
        Route::get('getAllAlerts', 'getAllAlerts');
        Route::get('stockAlert', 'stockAlert');


        


        
    });

    // Product Category
    Route::prefix('product-category')->controller(ProductCategoryController::class)->group(function () {
       
        Route::post('list', 'list');
    
        Route::post('searchCategory', 'searchCategory');

        Route::get('edit/{id}',  'edit');
        Route::match(['post', 'put'], 'save', 'save');
        Route::delete('delete/{id}', 'delete');
    });

    // Product
    Route::prefix('product')->controller(ProductController::class)->group(function () {
        Route::post('list', 'list');
        Route::post('product-deleted', 'productdeleted');

        Route::post( 'product-deleted/edit/{id}', 'editDeleted');

        Route::post('searchProduct', 'searchProduct');

        
        
        Route::get('getAllProducts', 'getAllProducts');
        Route::get('getAllProductCategories', 'getAllProductCategories');
        Route::get('stock-alerts', 'getStockAlerts');
        Route::get('expiry-alerts', 'getExpiryAlerts');

        

        Route::post('addQuantity', 'addQuantity');
        Route::post('productinventory', 'productinventory');

        

        Route::get('edit/{id}',  'edit');
        Route::match(['post', 'put'], 'save', 'save');
        Route::delete('delete/{id}', 'delete');
        Route::get('category-list', 'categoryList');
    });


    //   // stock alerts
    //   Route::prefix('products')->controller(StockAlertsController::class)->group(function () {
       
     

    // });

    // Balance Adjustment
    Route::prefix('balance-adjustment')->controller(BalanceAdjustmentController::class)->group(function () {
        Route::post('list', 'list');
        Route::get('edit/{id}',  'edit');
        Route::match(['post', 'put'], 'save', 'save');
        Route::delete('delete/{id}', 'delete');
    });

    // System User
    Route::prefix('user')->controller(UserController::class)->group(function () {
        Route::post('list', 'list');
        Route::get('edit/{id}',  'edit');
        Route::match(['post', 'put'], 'save', 'save');
        Route::delete('delete/{id}', 'delete');
    });

    // Cashier
    Route::prefix('cashier')->controller(CashierController::class)->group(function () {
        Route::post('/', 'index');
        // Route::get('show-table/{id?}',  'showTable');
        Route::get('completedOrders',  'completedOrders');
        Route::post('searchCompletedOrders',  'searchCompletedOrders');
      //  Route::post('addCompletedOrders',  'addCompletedOrders');

        Route::post('addCompletedOrders',  'addCompletedOrders');

        Route::get('draftedOrders',  'draftedOrders');

        Route::post('update-order-qty', 'updateOrderQty');
        Route::post('update-detail-discount', 'updateDetailDiscount');
        Route::post('update-order-discount', 'updateOrderDiscount');
        Route::delete('delete-order/{product_id}/{transaction_id}', 'deleteOrder');
        Route::post('add-to-order', 'addToOrder');
        Route::post('print-invoice', 'printInvoice');
        Route::post('confirm-payment', 'confirmPayment');
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
