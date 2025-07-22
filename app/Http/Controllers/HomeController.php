<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\IncomingItem;
use App\Models\OutgoingItem;
use App\Models\Producer;
use App\Models\User;
use App\Models\Category; // Added this import
use App\Models\ReturnedItem;
use Carbon\Carbon;
use App\Exports\ItemReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB; // Added this import

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();

        // Data for Summary Cards
        $incomingToday = IncomingItem::whereDate('tanggal_masuk_barang', $today)->sum('jumlah_barang');
        $outgoingToday = OutgoingItem::whereDate('tanggal_keluar_barang', $today)->sum('jumlah_barang');
        $salesTransactionsToday = OutgoingItem::whereDate('tanggal_keluar_barang', $today)->distinct('nota_transaksi')->count();
        $purchaseTransactionsToday = IncomingItem::whereDate('tanggal_masuk_barang', $today)->distinct('nota_transaksi')->count();

        // Data for Stock Condition Chart (Top 10 Lowest Stock Items)
        $lowStockItems = IncomingItem::where('jumlah_barang', '>', 0)
                                   ->orderBy('jumlah_barang', 'asc')
                                   ->take(10)
                                   ->get();
        $stockItemLabels = $lowStockItems->pluck('nama_barang');
        $stockItemData = $lowStockItems->pluck('jumlah_barang');
        
        // Data for Sales and Purchase Trend Chart (Last 7 Days based on latest data)
        $latestIncomingDate = IncomingItem::max('tanggal_masuk_barang');
        $latestOutgoingDate = OutgoingItem::max('tanggal_keluar_barang');
        $latestDateString = max($latestIncomingDate, $latestOutgoingDate);

        $endDate = $latestDateString ? Carbon::parse($latestDateString)->endOfDay() : Carbon::now()->endOfDay();
        $startDate = $endDate->copy()->subDays(6)->startOfDay();

        // Generate all dates for the last 7 days to use as keys
        $dateRange = collect();
        for ($i = 0; $i < 7; $i++) {
            $date = $startDate->copy()->addDays($i);
            $dateRange->put($date->toDateString(), 0);
        }

        $daysOfWeek = $dateRange->keys()->map(function ($date) {
            return Carbon::parse($date)->isoFormat('dddd');
        })->values()->all();

        // Query for incoming items, grouped by day
        $incomingData = IncomingItem::select(
            DB::raw('DATE(tanggal_masuk_barang) as date'),
            DB::raw('SUM(jumlah_barang) as total')
        )
            ->whereBetween('tanggal_masuk_barang', [$startDate, $endDate])
            ->groupBy('date')
            ->pluck('total', 'date');

        // Query for outgoing items, grouped by day
        $outgoingData = OutgoingItem::select(
            DB::raw('DATE(tanggal_keluar_barang) as date'),
            DB::raw('SUM(jumlah_barang) as total')
        )
            ->whereBetween('tanggal_keluar_barang', [$startDate, $endDate])
            ->groupBy('date')
            ->pluck('total', 'date');

        // Merge the queried data with the full date range, ensuring days with no activity are 0
        $purchaseData = $dateRange->merge($incomingData)->values()->all();
        $salesData = $dateRange->merge($outgoingData)->values()->all();

        // Data untuk notifikasi stok rendah (khusus untuk manager)
        $lowStockNotifications = [];
        $outOfStockNotifications = [];
        if ($user->role === 'manager') {
            // Barang dengan stok kritis (0 unit)
            $outOfStockItems = IncomingItem::where('jumlah_barang', 0)
                ->select('nama_barang', 'kategori_barang', 'lokasi_rak_barang')
                ->get();
            
            // Barang dengan stok rendah (1-10 unit)
            $lowStockItems = IncomingItem::whereBetween('jumlah_barang', [1, 10])
                ->select('nama_barang', 'kategori_barang', 'jumlah_barang', 'lokasi_rak_barang')
                ->orderBy('jumlah_barang', 'asc')
                ->get();

            $lowStockNotifications = $lowStockItems;
            $outOfStockNotifications = $outOfStockItems;
        }

        if ($user->role === 'manager') {
            return view('dashboard.manager_dashboard', [
                'incomingToday' => $incomingToday,
                'outgoingToday' => $outgoingToday,
                'salesTransactionsToday' => $salesTransactionsToday,
                'purchaseTransactionsToday' => $purchaseTransactionsToday,
                'stockItemLabels' => $stockItemLabels,
                'stockItemData' => $stockItemData,
                'chartLabels' => $daysOfWeek,
                'purchaseTrendData' => $purchaseData,
                'salesTrendData' => $salesData,
                'chartPeriod' => $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y'),
                'lowStockNotifications' => $lowStockNotifications,
                'outOfStockNotifications' => $outOfStockNotifications,
            ]);
        } elseif ($user->role === 'admin') {
            return view('dashboard.staff_admin_dashboard', [
                'incomingToday' => $incomingToday,
                'outgoingToday' => $outgoingToday,
                'salesTransactionsToday' => $salesTransactionsToday,
                'purchaseTransactionsToday' => $purchaseTransactionsToday,
                'chartLabels' => $daysOfWeek,
                'purchaseTrendData' => $purchaseData,
                'salesTrendData' => $salesData,
                'chartPeriod' => $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y'),
            ]);
        }
        
        return redirect()->route('login')->with('error', 'Role tidak dikenali.');
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

        // Get producers data
        $producers = Producer::orderBy('nama_produsen_supplier')->get();

        // Get categories data
        $categories = Category::orderBy('nama_kategori')->get();

        // --- Data untuk Grafik Tren Penjualan/Pembelian ---
        // Anda dapat menyesuaikan rentang tanggal ini sesuai kebutuhan
        $startDate = Carbon::now()->subDays(6)->startOfDay(); // 7 hari terakhir
        $endDate = Carbon::now()->endOfDay();

        $daysOfWeek = [];
        for ($i = 0; $i < 7; $i++) {
            $daysOfWeek[] = $startDate->copy()->addDays($i)->isoFormat('dddd'); // Nama hari dalam bahasa Indonesia
        }

        $purchaseData = array_fill(0, 7, 0);
        $salesData = array_fill(0, 7, 0);

        $incomingItemsForChart = IncomingItem::whereBetween('tanggal_masuk_barang', [$startDate, $endDate])
                                            ->get();
        foreach ($incomingItemsForChart as $item) {
            $dayOfWeek = $item->tanggal_masuk_barang->dayOfWeekIso; // 1 (Senin) sampai 7 (Minggu)
            $purchaseData[$dayOfWeek - 1] += $item->jumlah_barang;
        }

        $outgoingItemsForChart = OutgoingItem::whereBetween('tanggal_keluar_barang', [$startDate, $endDate])
                                            ->get();
        foreach ($outgoingItemsForChart as $item) {
            $dayOfWeek = $item->tanggal_keluar_barang->dayOfWeekIso;
            $salesData[$dayOfWeek - 1] += $item->jumlah_barang;
        }
        // --- Akhir Data untuk Grafik ---

        // --- Data untuk Status Stok (Banyak, Sedikit, Habis) ---
        // Data ini tidak lagi diperlukan di sini karena sudah dipindahkan ke index()
        $plentyStockCount = 0;
        $lowStockCount = 0;
        $outOfStockCount = 0;

        foreach ($incomingItems as $item) {
            if ($item->jumlah_barang > 50) { // Asumsi: Banyak jika > 50 unit
                $plentyStockCount++;
            } elseif ($item->jumlah_barang > 0 && $item->jumlah_barang <= 50) { // Asumsi: Sedikit jika 1-50 unit
                $lowStockCount++;
            } else { // Asumsi: Habis jika 0 unit
                $outOfStockCount++;
            }
        }
        // --- Akhir Data untuk Status Stok ---

        return view('dashboard.report_stock', [
            'incomingItems' => $incomingItems,
            'outgoingItems' => $outgoingItems,
            'producers' => $producers,
            'categories' => $categories,
            'chartLabels' => $daysOfWeek,
            'purchaseTrendData' => $purchaseData,
            'salesTrendData' => $salesData,
            'chartPeriod' => $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y'),
            'plentyStockCount' => $plentyStockCount,
            'lowStockCount' => $lowStockCount,
            'outOfStockCount' => $outOfStockCount,
        ]);
    }

    /**
     * Show the order items page (Daftar Mitra Produsen).
     */
    public function showOrderItems()
    {
        // Mengambil data produsen dari database
        $producers = Producer::orderBy('nama_produsen_supplier')->get();
        $returnedItems = ReturnedItem::orderBy('created_at', 'desc')->get();

        return view('dashboard.order_items', [
            'producers' => $producers,
            'returnedItems' => $returnedItems,
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
        $roles = ['admin', 'manager']; // Contoh peran yang tersedia (hanya admin dan manager)

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
            'role' => 'required|in:admin,manager', // Hanya izinkan role 'admin' atau 'manager'
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
            'role' => $request->role, // Akan selalu 'admin' atau 'manager' karena validasi
            'phone_number' => $request->phone_number,
        ]);

        return redirect()->route('employee.accounts')->with('success', 'Akun pegawai berhasil ditambahkan!');
    }

    public function exportCsv()
    {
        $fileName = 'stock_report.csv';
        $items = IncomingItem::all();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID', 'Nama Barang', 'Kategori', 'Tanggal Masuk', 'Jumlah', 'Nama Produsen', 'Total Bayar'];

        $callback = function() use($items, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($items as $item) {
                $row['ID']  = $item->id;
                $row['Nama Barang']    = $item->nama_barang;
                $row['Kategori']    = $item->kategori_barang;
                $row['Tanggal Masuk']  = $item->tanggal_masuk_barang->format('d M Y');
                $row['Jumlah']  = $item->jumlah_barang;
                $row['Nama Produsen'] = $item->nama_produsen;
                $row['Total Bayar'] = (float) preg_replace('/[^\d.]/', '', $item->pembayaran_transaksi);


                fputcsv($file, [
                    $row['ID'],
                    $row['Nama Barang'],
                    $row['Kategori'],
                    $row['Tanggal Masuk'],
                    $row['Jumlah'],
                    $row['Nama Produsen'],
                    $row['Total Bayar']
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function printStockReport()
    {
        $incomingItems = IncomingItem::all();
        // For the print view, we just need the data.
        // The view itself will handle the presentation.
        return view('exports.stock_report_print', ['incomingItems' => $incomingItems]);
    }

    public function showReturnedItems()
    {
        $returnedItems = ReturnedItem::orderBy('created_at', 'desc')->get();
        return view('dashboard.returned_items', ['returnedItems' => $returnedItems]);
    }
}
