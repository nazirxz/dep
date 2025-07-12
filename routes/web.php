<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SplashController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Splash screen route (root)
Route::get('/', [SplashController::class, 'index'])->name('splash');

// Redirect to login after splash
Route::get('/enter', function () {
    return redirect()->route('login');
})->name('enter');

// Guest routes (only accessible when not logged in)
Route::middleware('guest')->group(function () {
    // Authentication Routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Authenticated routes (only accessible when logged in)
Route::middleware('auth')->group(function () {
    // Home route (after login)
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Route untuk Laporan Stok Barang
    Route::get('/report/stock', [HomeController::class, 'showStockReport'])->name('report.stock');

    // Route untuk Pemesanan Barang
    Route::get('/order/items', [HomeController::class, 'showOrderItems'])->name('order.items');

    // Route baru untuk Akun Pegawai
    Route::get('/employee/accounts', [HomeController::class, 'showEmployeeAccounts'])->name('employee.accounts');
    // Route untuk menyimpan data akun pegawai baru (POST request)
    Route::post('/employee/accounts', [HomeController::class, 'storeEmployeeAccount'])->name('employee.accounts.store');

    // Logout route
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// Optional: Redirect authenticated users from root to home
Route::get('/dashboard', function () {
    return redirect()->route('home');
})->middleware('auth');
