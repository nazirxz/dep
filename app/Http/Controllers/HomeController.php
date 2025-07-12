<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\IncomingItem; // Import model IncomingItem
use App\Models\OutgoingItem; // Import model OutgoingItem
use Carbon\Carbon; // Import Carbon untuk manipulasi tanggal

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

        // --- Data untuk Grafik Tren Penjualan/Pembelian ---
        // Definisikan periode minggu untuk grafik (contoh: minggu saat ini atau minggu tertentu)
        // Untuk demo, kita akan menggunakan minggu yang mencakup data seeder
        // Asumsi minggu dimulai dari Senin (ISO 8601 day of week)
        $startDate = Carbon::parse('2025-06-16')->startOfDay(); // Senin, 16 Juni 2025
        $endDate = Carbon::parse('2025-06-22')->endOfDay();   // Minggu, 22 Juni 2025

        $daysOfWeek = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        // Inisialisasi array data grafik dengan nilai 0 untuk setiap hari
        $purchaseData = array_fill(0, 7, 0); // Data untuk tren pembelian
        $salesData = array_fill(0, 7, 0);    // Data untuk tren penjualan

        // Hitung data tren pembelian (barang masuk)
        $incomingItemsForChart = IncomingItem::whereBetween('tanggal_masuk_barang', [$startDate, $endDate])
                                            ->get();
        foreach ($incomingItemsForChart as $item) {
            // dayOfWeekIso mengembalikan 1 untuk Senin, 7 untuk Minggu
            $dayOfWeek = $item->tanggal_masuk_barang->dayOfWeekIso;
            $purchaseData[$dayOfWeek - 1] += $item->jumlah_barang;
        }

        // Hitung data tren penjualan (barang keluar)
        $outgoingItemsForChart = OutgoingItem::whereBetween('tanggal_keluar_barang', [$startDate, $endDate])
                                            ->get();
        foreach ($outgoingItemsForChart as $item) {
            // dayOfWeekIso mengembalikan 1 untuk Senin, 7 untuk Minggu
            $dayOfWeek = $item->tanggal_keluar_barang->dayOfWeekIso;
            $salesData[$dayOfWeek - 1] += $item->jumlah_barang;
        }

        // --- Akhir Data untuk Grafik ---

        // Memeriksa role user dan memuat tampilan yang sesuai
        if ($user->role === 'manager') {
            return view('home', [
                'dashboardView' => 'dashboard.manager_dashboard',
                'incomingItems' => $incomingItems,
                'outgoingItems' => $outgoingItems,
                'chartLabels' => $daysOfWeek,         // Label hari untuk grafik
                'purchaseTrendData' => $purchaseData, // Data tren pembelian
                'salesTrendData' => $salesData,       // Data tren penjualan
                'chartPeriod' => $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y'), // Periode grafik
            ]);
        } elseif ($user->role === 'staff_admin') {
            // Jika ada dashboard khusus untuk staff_admin, teruskan data chart juga
            return view('home', [
                'dashboardView' => 'dashboard.staff_admin_dashboard',
                'incomingItems' => $incomingItems,
                'outgoingItems' => $outgoingItems,
                'chartLabels' => $daysOfWeek,
                'purchaseTrendData' => $purchaseData,
                'salesTrendData' => $salesData,
                'chartPeriod' => $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y'),
            ]);
        }
        
        // Default jika role tidak dikenali atau untuk user biasa
        return view('home', [
            'dashboardView' => 'dashboard.default_dashboard',
            'incomingItems' => $incomingItems,
            'outgoingItems' => $outgoingItems,
            'chartLabels' => $daysOfWeek,
            'purchaseTrendData' => $purchaseData,
            'salesTrendData' => $salesData,
            'chartPeriod' => $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y'),
        ]);
    }
}
