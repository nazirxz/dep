<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SplashController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Auth\LoginController;
// use App\Http\Controllers\Auth\RegisterController; // Hapus baris ini

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

    // Registration Routes - Hapus blok ini
    // Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    // Route::post('/register', [RegisterController::class, 'register']);
});

// Authenticated routes (only accessible when logged in)
Route::middleware('auth')->group(function () {
    // Home route (after login)
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Logout route
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// Optional: Redirect authenticated users from root to home
Route::get('/dashboard', function () {
    return redirect()->route('home');
})->middleware('auth');