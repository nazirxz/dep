<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\OutgoingItemApiController;
use App\Http\Controllers\Api\IncomingItemApiController;
use App\Http\Controllers\Api\ReturnItemApiController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\VoucherController;
use App\Http\Controllers\Api\ShippingMethodController;

// Authentication routes (public)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes (requires authentication)
Route::middleware('auth:sanctum')->group(function () {
    // User profile
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Products API routes for mobile
    Route::prefix('products')->group(function () {
        // GET /api/products - Get all products
        Route::get('/', [ProductController::class, 'index']);
        
        // GET /api/products/categories - Get all available categories (HARUS SEBELUM {id})
        Route::get('/categories', [ProductController::class, 'categories']);
        
        // GET /api/products/category/{kategori} - Get products by category
        Route::get('/category/{kategori}', [ProductController::class, 'category']);
        
        // GET /api/products/search?q=keyword - Search products
        Route::get('/search', [ProductController::class, 'search']);
        
        // GET /api/products/{id} - Get single product detail (HARUS PALING AKHIR)
        Route::get('/{id}', [ProductController::class, 'show']);
    });

    // Dashboard API routes for mobile
    Route::prefix('dashboard')->group(function () {
        // GET /api/dashboard/stats - Get dashboard statistics
        Route::get('/stats', [DashboardApiController::class, 'getDashboardStats']);
        
        // GET /api/dashboard/low-stock - Get low stock warning items
        Route::get('/low-stock', [DashboardApiController::class, 'getLowStockWarning']);
        
        // GET /api/dashboard/weekly-stats - Get weekly incoming/outgoing statistics
        Route::get('/weekly-stats', [DashboardApiController::class, 'getWeeklyStats']);
        
        // GET /api/dashboard/monthly-stats - Get monthly statistics
        Route::get('/monthly-stats', [DashboardApiController::class, 'getMonthlyStats']);
        
        // GET /api/dashboard/complete - Get complete dashboard data in one request
        Route::get('/complete', [DashboardApiController::class, 'getCompleteDashboard']);
    });

    // Outgoing Items (Barang Keluar) API routes for mobile
    Route::prefix('outgoing-items')->group(function () {
        // GET /api/outgoing-items - Get all outgoing items with filters
        Route::get('/', [OutgoingItemApiController::class, 'index']);
        
        // GET /api/outgoing-items/categories - Get all available categories
        Route::get('/categories', [OutgoingItemApiController::class, 'getCategories']);
        
        // GET /api/outgoing-items/category/{kategori} - Get outgoing items by category
        Route::get('/category/{kategori}', [OutgoingItemApiController::class, 'getByCategory']);
        
        // GET /api/outgoing-items/search?q=keyword - Search outgoing items
        Route::get('/search', [OutgoingItemApiController::class, 'search']);
        
        // GET /api/outgoing-items/weekly-sales-stats - Get weekly sales statistics
        Route::get('/weekly-sales-stats', [OutgoingItemApiController::class, 'getWeeklySalesStats']);
        
        // GET /api/outgoing-items/{id} - Get single outgoing item detail
        Route::get('/{id}', [OutgoingItemApiController::class, 'show']);
    });

    // Incoming Items (Barang Masuk) API routes for mobile
    Route::prefix('incoming-items')->group(function () {
        // GET /api/incoming-items - Get all incoming items with filters
        Route::get('/', [IncomingItemApiController::class, 'index']);
        
        // GET /api/incoming-items/categories - Get all available categories
        Route::get('/categories', [IncomingItemApiController::class, 'getCategories']);
        
        // GET /api/incoming-items/category/{kategori} - Get incoming items by category
        Route::get('/category/{kategori}', [IncomingItemApiController::class, 'getByCategory']);
        
        // GET /api/incoming-items/search?q=keyword - Search incoming items
        Route::get('/search', [IncomingItemApiController::class, 'search']);
        
        // GET /api/incoming-items/weekly-incoming-stats - Get weekly incoming statistics
        Route::get('/weekly-incoming-stats', [IncomingItemApiController::class, 'getWeeklyIncomingStats']);
        
        // GET /api/incoming-items/{id} - Get single incoming item detail
        Route::get('/{id}', [IncomingItemApiController::class, 'show']);
    });

    // Return Items (Barang Return) API routes for mobile
    Route::prefix('return-items')->group(function () {
        // GET /api/return-items - Get all returned items with filters
        Route::get('/', [ReturnItemApiController::class, 'index']);
        
        // GET /api/return-items/returnable-items - Get user's returnable order items
        Route::get('/returnable-items', [ReturnItemApiController::class, 'getReturnableOrderItems']);
        
        // POST /api/return-items - Create new returned item
        Route::post('/', [ReturnItemApiController::class, 'store']);
        
        // PUT /api/return-items/{id} - Update returned item
        Route::put('/{id}', [ReturnItemApiController::class, 'update']);
        
        // DELETE /api/return-items/{id} - Delete returned item
        Route::delete('/{id}', [ReturnItemApiController::class, 'destroy']);
        
        // GET /api/return-items/categories - Get all available categories
        Route::get('/categories', [ReturnItemApiController::class, 'getCategories']);
        
        // GET /api/return-items/category/{kategori} - Get returned items by category
        Route::get('/category/{kategori}', [ReturnItemApiController::class, 'getByCategory']);
        
        // GET /api/return-items/search?q=keyword - Search returned items
        Route::get('/search', [ReturnItemApiController::class, 'search']);
        
        // GET /api/return-items/weekly-return-stats - Get weekly return statistics
        Route::get('/weekly-return-stats', [ReturnItemApiController::class, 'getWeeklyReturnStats']);
        
        // GET /api/return-items/{id} - Get single returned item detail
        Route::get('/{id}', [ReturnItemApiController::class, 'show']);
    });

    // Orders API routes for mobile
    Route::prefix('orders')->group(function () {
        // GET /api/orders - Get user's orders
        Route::get('/', [OrderController::class, 'index']);
        
        // POST /api/orders - Create new order
        Route::post('/', [OrderController::class, 'store']);
        
        // GET /api/orders/stats - Get order statistics
        Route::get('/stats', [OrderController::class, 'getOrderStats']);
        
        // Admin routes
        Route::middleware('admin')->group(function () {
            // GET /api/orders/admin - Get all orders (admin only)
            Route::get('/admin', [OrderController::class, 'adminIndex']);
            
            // GET /api/orders/user/{userId} - Get orders by user ID (admin only)
            Route::get('/user/{userId}', [OrderController::class, 'getOrdersByUserId']);
        });

        // Sales routes (for admin and sales role)
        Route::middleware('auth:sanctum')->group(function () {
            // GET /api/orders/sales - Get orders ready for shipping (sales access)
            Route::get('/sales', [OrderController::class, 'salesIndex']);
            
            // PUT /api/orders/{id}/shipping-status - Update shipping status
            Route::put('/{id}/shipping-status', [OrderController::class, 'updateShippingStatus']);
        });
        
        // GET /api/orders/{id} - Get order detail
        Route::get('/{id}', [OrderController::class, 'show']);
    });

    // Vouchers API routes for mobile
    Route::prefix('vouchers')->group(function () {
        // GET /api/vouchers - Get available vouchers
        Route::get('/', [VoucherController::class, 'index']);
        
        // POST /api/vouchers/validate - Validate voucher code
        Route::post('/validate', [VoucherController::class, 'validate']);
    });
});

// Shipping Methods API routes (public - no auth required)
Route::prefix('shipping-methods')->group(function () {
    // GET /api/shipping-methods - Get available shipping methods
    Route::get('/', [ShippingMethodController::class, 'index']);
});