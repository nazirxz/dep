<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\DashboardApiController;

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
});