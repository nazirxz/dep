<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Tambahkan ini

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index()
    {
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        // Memeriksa role user dan memuat tampilan yang sesuai
        if ($user->role === 'manager') {
            return view('home', ['dashboardView' => 'dashboard.manager_dashboard']);
        } elseif ($user->role === 'staff_admin') { // Asumsi Anda akan memiliki role 'staff_admin'
            return view('home', ['dashboardView' => 'dashboard.staff_admin_dashboard']);
        }
        // Tambahkan kondisi lain jika ada role berbeda
        
        // Default jika role tidak dikenali atau untuk user biasa
        return view('home', ['dashboardView' => 'dashboard.default_dashboard']);
    }
}