<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\IncomingItem; // Import model IncomingItem
use App\Models\OutgoingItem; // Import model OutgoingItem

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index()
    {
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        // Mengambil data barang masuk dari database
        // Mengurutkan berdasarkan tanggal masuk terbaru
        $incomingItems = IncomingItem::orderBy('tanggal_masuk_barang', 'desc')->get();

        // Mengambil data barang keluar dari database
        // Mengurutkan berdasarkan tanggal keluar terbaru
        $outgoingItems = OutgoingItem::orderBy('tanggal_keluar_barang', 'desc')->get();

        // Memeriksa role user dan memuat tampilan yang sesuai
        if ($user->role === 'manager') {
            return view('home', [
                'dashboardView' => 'dashboard.manager_dashboard',
                'incomingItems' => $incomingItems, // Kirim data barang masuk ke view
                'outgoingItems' => $outgoingItems, // Kirim data barang keluar ke view
            ]);
        } elseif ($user->role === 'staff_admin') {
            return view('home', [
                'dashboardView' => 'dashboard.staff_admin_dashboard',
                'incomingItems' => $incomingItems,
                'outgoingItems' => $outgoingItems,
            ]);
        }
        
        // Default jika role tidak dikenali atau untuk user biasa
        return view('home', [
            'dashboardView' => 'dashboard.default_dashboard',
            'incomingItems' => $incomingItems,
            'outgoingItems' => $outgoingItems,
        ]);
    }
}
