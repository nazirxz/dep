<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SplashController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ItemManagementController;
use App\Http\Controllers\EmployeeAccountController; // Import controller baru
use App\Http\Controllers\VerificationItemController;
use App\Http\Controllers\ProducerController;
use App\Http\Controllers\ForgotPasswordController;

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

// Password Reset Routes
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetPasswordForm'])->name('password.reset');
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('password.update');

// Routes yang memerlukan autentikasi
Route::middleware(['auth'])->group(function () {
    // Dashboard utama (dinamis berdasarkan role)
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    
    // Routes untuk Producer CRUD (accessible by both manager and admin)
    Route::resource('producers', ProducerController::class)->only(['store', 'update', 'destroy']);
    
    // Test route for debugging
    Route::get('/test-producers', function() {
        return 'Producer routes are working';
    });
    
    // Routes untuk Manager (role: manager)
    Route::middleware(['auth', 'role:manager'])->group(function () {
        Route::get('/report/stock', [HomeController::class, 'showStockReport'])->name('report.stock');
        Route::get('/report/stock/csv', [HomeController::class, 'exportCsv'])->name('report.stock.csv');
        Route::get('/report/stock/print', [HomeController::class, 'printStockReport'])->name('report.stock.print');
        Route::get('/order/items', [HomeController::class, 'showOrderItems'])->name('order.items');
        
        // Route untuk chart data AJAX
        Route::get('/chart/data', [HomeController::class, 'getChartData'])->name('chart.data');
        
        // Routes untuk Akun Pegawai (sekarang di EmployeeAccountController)
        Route::get('/employee/accounts', [EmployeeAccountController::class, 'showEmployeeAccounts'])->name('employee.accounts');
        Route::post('/employee/accounts', [EmployeeAccountController::class, 'storeEmployeeAccount'])->name('employee.accounts.store');
        Route::get('/employee/accounts/{user}/edit', [EmployeeAccountController::class, 'edit'])->name('employee.accounts.edit'); // Untuk ambil data edit
        Route::put('/employee/accounts/{user}', [EmployeeAccountController::class, 'update'])->name('employee.accounts.update');
        Route::delete('/employee/accounts/{user}', [EmployeeAccountController::class, 'destroy'])->name('employee.accounts.destroy');
        
        // Route untuk User Pengecer
        Route::get('/pengecer/users', [EmployeeAccountController::class, 'showPengecerUsers'])->name('pengecer.users');
        
    });
    
    // Routes untuk Staff Admin (role: admin)
    Route::middleware(['auth', 'role:admin,manager'])->group(function () {
        // Halaman utama data barang
        Route::get('/staff/items', [ItemManagementController::class, 'index'])->name('staff.items.index');
        
        // Halaman pengelolaan barang (tempat form tambah/edit)
        Route::get('/staff/item-management', [ItemManagementController::class, 'itemManagement'])->name('staff.item.management');
        
        // CRUD untuk Barang Masuk (Incoming Items)
        Route::post('/staff/incoming-items', [ItemManagementController::class, 'storeIncomingItem'])->name('staff.incoming_items.store');
        Route::put('/staff/incoming-items/{id}', [ItemManagementController::class, 'updateIncomingItem'])->name('staff.incoming_items.update');
        Route::delete('/staff/incoming-items/{id}', [ItemManagementController::class, 'deleteIncomingItem'])->name('staff.incoming_items.delete');
        Route::get('/staff/incoming-items/{id}', [ItemManagementController::class, 'getIncomingItem'])->name('staff.incoming_items.show'); // Untuk mendapatkan detail item

        // CRUD untuk Barang Keluar (Outgoing Items)
        Route::post('/staff/outgoing-items', [ItemManagementController::class, 'storeOutgoingItem'])->name('staff.outgoing_items.store');
        Route::put('/staff/outgoing-items/{id}', [ItemManagementController::class, 'updateOutgoingItem'])->name('staff.outgoing_items.update');
        Route::delete('/staff/outgoing-items/{id}', [ItemManagementController::class, 'deleteOutgoingItem'])->name('staff.outgoing_items.delete');
        Route::get('/staff/outgoing-items/{id}', [ItemManagementController::class, 'getOutgoingItem'])->name('staff.outgoing_items.show'); // Untuk mendapatkan detail item keluar

        // Rute baru untuk proses verifikasi barang
        Route::get('/staff/verification-items', [VerificationItemController::class, 'index'])->name('staff.verification_items.index');
        Route::post('/staff/verification-items', [VerificationItemController::class, 'store'])->name('staff.verification_items.store');
        Route::post('/staff/verification-items/{id}/verify', [VerificationItemController::class, 'verify'])->name('staff.verification_items.verify');

        // Fungsi Lainnya
        Route::get('/staff/items/search', [ItemManagementController::class, 'searchItems'])->name('staff.items.search');
        Route::get('/staff/items/category/{category}', [ItemManagementController::class, 'getItemsByCategory'])->name('staff.items.by_category');
        Route::get('/staff/dashboard/stats', [ItemManagementController::class, 'getDashboardStats'])->name('staff.dashboard.stats');
        Route::post('/staff/items/auto-assign-locations', [ItemManagementController::class, 'autoAssignLocations'])->name('staff.items.auto_assign_locations');
        Route::post('/staff/items/import-csv', [ItemManagementController::class, 'importFromCSV'])->name('staff.items.import_csv');
        Route::get('/staff/items/export-csv', [ItemManagementController::class, 'exportToCSV'])->name('staff.items.export_csv');
        Route::get('/staff/items/{id}/barcode', [ItemManagementController::class, 'generateBarcode'])->name('staff.items.generate_barcode');
        Route::get('/staff/items/{id}/qrcode', [ItemManagementController::class, 'generateQRCode'])->name('staff.items.generate_qrcode');
        Route::post('/staff/items/{id}/duplicate', [ItemManagementController::class, 'duplicateItem'])->name('staff.items.duplicate');
        Route::get('/staff/inventory/report', [ItemManagementController::class, 'getInventoryReport'])->name('staff.inventory.report');
        Route::get('/staff/items/{id}/movement-history', [ItemManagementController::class, 'getItemMovementHistory'])->name('staff.items.movement_history');
        Route::post('/staff/items/bulk-update', [ItemManagementController::class, 'bulkUpdate'])->name('staff.items.bulk_update');
        Route::get('/staff/locations/available', [ItemManagementController::class, 'getAvailableLocations'])->name('staff.locations.available');
        // Rute baru untuk verifikasi barang
        Route::get('/staff/items/pending-verification', [ItemManagementController::class, 'getPendingVerificationItems'])->name('staff.items.pending-verification');
        Route::post('/staff/items/verify', [ItemManagementController::class, 'verify'])->name('staff.items.verify');

        // Route for marking order as finished packing
        Route::put('/staff/orders/{orderId}/finished-packing', [ItemManagementController::class, 'markOrderAsFinishedPacking'])->name('staff.orders.finished-packing');
        
        // Route untuk Admin melihat Users Pengecer
        Route::get('/staff/users', [EmployeeAccountController::class, 'showPengecerUsersForAdmin'])->name('staff.users');

    });

    // Monitor Gudang (Accessible by both manager and admin)
    Route::middleware(['auth', 'role:manager,admin'])->group(function () {
        Route::get('/staff/warehouse-monitor', [ItemManagementController::class, 'showWarehouseMonitor'])->name('staff.warehouse_monitor');
    });

    // Verification routes
    Route::post('/verify-incoming-item/{id}', [VerificationItemController::class, 'verify'])->name('verify.incoming-item');
});

Route::middleware(['auth', 'role:manager'])->group(function () {
    // Rute yang hanya bisa diakses oleh manajer
});
