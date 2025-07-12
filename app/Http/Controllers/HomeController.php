<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; // Import Hash untuk mengenkripsi password
use Illuminate\Support\Facades\Validator; // Import Validator
use App\Models\IncomingItem;
use App\Models\OutgoingItem;
use App\Models\Producer;
use App\Models\User; // Import model User
use Carbon\Carbon;

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
        $incomingItems = IncomingItem::orderBy('tanggal_masuk_barang', 'desc')->get();

        // Mengambil data barang keluar dari database
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

    /**
     * Show the stock report page.
     */
    public function showStockReport()
    {
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        // Mengambil data barang masuk dari database
        $incomingItems = IncomingItem::orderBy('tanggal_masuk_barang', 'desc')->get();

        // Mengambil data barang keluar dari database
        $outgoingItems = OutgoingItem::orderBy('tanggal_keluar_barang', 'desc')->get();

        // --- Data untuk Grafik Tren Penjualan/Pembelian ---
        $startDate = Carbon::parse('2025-06-16')->startOfDay();
        $endDate = Carbon::parse('2025-06-22')->endOfDay();

        $daysOfWeek = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        $purchaseData = array_fill(0, 7, 0);
        $salesData = array_fill(0, 7, 0);

        $incomingItemsForChart = IncomingItem::whereBetween('tanggal_masuk_barang', [$startDate, $endDate])
                                            ->get();
        foreach ($incomingItemsForChart as $item) {
            $dayOfWeek = $item->tanggal_masuk_barang->dayOfWeekIso;
            $purchaseData[$dayOfWeek - 1] += $item->jumlah_barang;
        }

        $outgoingItemsForChart = OutgoingItem::whereBetween('tanggal_keluar_barang', [$startDate, $endDate])
                                            ->get();
        foreach ($outgoingItemsForChart as $item) {
            $dayOfWeek = $item->tanggal_keluar_barang->dayOfWeekIso;
            $salesData[$dayOfWeek - 1] += $item->jumlah_barang;
        }
        // --- Akhir Data untuk Grafik ---

        return view('dashboard.report_stock', [
            'incomingItems' => $incomingItems,
            'outgoingItems' => $outgoingItems,
            'chartLabels' => $daysOfWeek,
            'purchaseTrendData' => $purchaseData,
            'salesTrendData' => $salesData,
            'chartPeriod' => $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y'),
        ]);
    }

    /**
     * Show the order items page (Daftar Mitra Produsen).
     */
    public function showOrderItems()
    {
        // Mengambil data produsen dari database
        $producers = Producer::orderBy('nama_produsen_supplier')->get();

        return view('dashboard.order_items', [
            'producers' => $producers,
        ]);
    }

    /**
     * Show the employee accounts management page.
     */
    public function showEmployeeAccounts()
    {
        // Mengambil semua user dengan role 'admin' dari database
        $employeeAccounts = User::where('role', 'admin')->get();

        // Anda juga bisa meneruskan daftar peran yang tersedia jika ingin dinamis
        $roles = ['admin', 'manager', 'staff_admin']; // Contoh peran yang tersedia

        return view('dashboard.employee_accounts', [
            'employeeAccounts' => $employeeAccounts,
            'roles' => $roles, // Teruskan peran ke view
        ]);
    }

    /**
     * Store a new employee account.
     */
    public function storeEmployeeAccount(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed', // 'confirmed' akan mencari password_confirmation
            'role' => 'required|in:admin', // Hanya izinkan role 'admin'
            'phone_number' => 'nullable|string|max:20',
        ], [
            'full_name.required' => 'Nama Lengkap wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username ini sudah digunakan.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'password.required' => 'Kata Sandi wajib diisi.',
            'password.min' => 'Kata Sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi Kata Sandi tidak cocok.',
            'role.required' => 'Peran Pegawai wajib dipilih.',
            'role.in' => 'Peran Pegawai yang dipilih tidak valid.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Buat user baru
        User::create([
            'full_name' => $request->full_name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role, // Akan selalu 'admin' karena validasi
            'phone_number' => $request->phone_number,
        ]);

        return redirect()->route('employee.accounts')->with('success', 'Akun pegawai berhasil ditambahkan!');
    }
}
