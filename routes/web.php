<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SplashController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ItemManagementController;
use App\Http\Middleware\RoleMiddleware;

// Route untuk splash screen
Route::get('/', [SplashController::class, 'index'])->name('splash');

// Route untuk masuk ke aplikasi (redirect ke login jika belum login)
Route::get('/enter', function () {
    if (auth()->check()) {
        return redirect()->route('home');
    }
    return redirect()->route('login');
})->name('enter');

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Routes yang memerlukan autentikasi
Route::middleware(['auth'])->group(function () {
    // Dashboard utama (dinamis berdasarkan role)
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    
    // Routes untuk Manager (role: manager)
    Route::middleware([RoleMiddleware::class.':manager'])->group(function () {
        Route::get('/report/stock', [HomeController::class, 'showStockReport'])->name('report.stock');
        Route::get('/order/items', [HomeController::class, 'showOrderItems'])->name('order.items');
        Route::get('/employee/accounts', [HomeController::class, 'showEmployeeAccounts'])->name('employee.accounts');
        Route::post('/employee/accounts', [HomeController::class, 'storeEmployeeAccount'])->name('employee.accounts.store');
    });
    
    // Routes untuk Staff Admin (role: admin)
    Route::middleware([RoleMiddleware::class.':admin'])->group(function () {
        // Data Barang
        Route::get('/staff/items', [ItemManagementController::class, 'index'])->name('staff.items.index');
        
        // Pengelolaan Barang
        Route::get('/staff/item-management', [ItemManagementController::class, 'itemManagement'])->name('staff.item.management');
        Route::post('/staff/item-management', [ItemManagementController::class, 'storeItem'])->name('staff.item.store');
        Route::get('/staff/warehouse-monitor', [ItemManagementController::class, 'showWarehouseMonitor'])->name('staff.warehouse_monitor');

        // Item Management Routes
        Route::get('/staff/items/create', [ItemManagementController::class, 'create'])->name('staff.items.create');
        Route::post('/staff/items', [ItemManagementController::class, 'store'])->name('staff.items.store');
        Route::get('/staff/items/{item}/edit', [ItemManagementController::class, 'edit'])->name('staff.items.edit');
        Route::put('/staff/items/{item}', [ItemManagementController::class, 'update'])->name('staff.items.update');
        Route::delete('/staff/items/{item}', [ItemManagementController::class, 'destroy'])->name('staff.items.destroy');
    });
});