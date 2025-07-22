<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IncomingItem;
use App\Models\OutgoingItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardApiController extends Controller
{
    /**
     * Get dashboard statistics summary
     */
    public function getDashboardStats(Request $request): JsonResponse
    {
        try {
            $today = Carbon::today();

            // Data for dashboard cards
            $stats = [
                'barang_masuk_hari_ini' => IncomingItem::whereDate('tanggal_masuk_barang', $today)->sum('jumlah_barang'),
                'barang_keluar_hari_ini' => OutgoingItem::whereDate('tanggal_keluar_barang', $today)->sum('jumlah_barang'),
                'transaksi_penjualan_hari_ini' => OutgoingItem::whereDate('tanggal_keluar_barang', $today)->distinct('nota_transaksi')->count(),
                'transaksi_pembelian_hari_ini' => IncomingItem::whereDate('tanggal_masuk_barang', $today)->distinct('nota_transaksi')->count(),
                
                // Additional stats
                'total_stok_tersedia' => IncomingItem::sum('jumlah_barang'),
                'total_kategori' => IncomingItem::distinct('kategori_barang')->count('kategori_barang'),
                'total_barang_masuk' => IncomingItem::count(),
                'total_barang_keluar' => OutgoingItem::count(),
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Statistik dashboard berhasil diambil',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil statistik dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get low stock warning items
     * Items with stock less than specified threshold (default: 10)
     */
    public function getLowStockWarning(Request $request): JsonResponse
    {
        try {
            $threshold = $request->get('threshold', 10); // Default threshold 10

            $lowStockItems = IncomingItem::select([
                'id',
                'nama_barang',
                'kategori_barang',
                'jumlah_barang',
                'foto_barang',
                'lokasi_rak_barang'
            ])
            ->where('jumlah_barang', '>', 0)
            ->where('jumlah_barang', '<=', $threshold)
            ->orderBy('jumlah_barang', 'asc')
            ->get();

            $lowStockFormatted = $lowStockItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_barang' => $item->nama_barang,
                    'kategori_barang' => $item->kategori_barang,
                    'jumlah_barang' => $item->jumlah_barang,
                    'foto_barang' => $item->foto_barang ? url('storage/' . $item->foto_barang) : null,
                    'lokasi_rak_barang' => $item->lokasi_rak_barang,
                    'status_warning' => $item->jumlah_barang <= 5 ? 'critical' : 'warning'
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Daftar barang dengan stok rendah berhasil diambil',
                'data' => $lowStockFormatted,
                'total_items' => $lowStockFormatted->count(),
                'threshold' => $threshold
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data stok rendah',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get weekly statistics for incoming/outgoing items
     * Same data as manager dashboard chart
     */
    public function getWeeklyStats(Request $request): JsonResponse
    {
        try {
            // Get the latest date from both tables to base our week calculation
            $latestIncomingDate = IncomingItem::max('tanggal_masuk_barang');
            $latestOutgoingDate = OutgoingItem::max('tanggal_keluar_barang');
            $latestDateString = max($latestIncomingDate, $latestOutgoingDate);

            $endDate = $latestDateString ? 
                Carbon::parse($latestDateString)->endOfDay() : 
                Carbon::now()->endOfDay();
            $startDate = $endDate->copy()->subDays(6)->startOfDay();

            // Generate all dates for the last 7 days
            $dateRange = collect();
            for ($i = 0; $i < 7; $i++) {
                $date = $startDate->copy()->addDays($i);
                $dateRange->put($date->toDateString(), 0);
            }

            // Get day names in Indonesian
            $daysOfWeek = $dateRange->keys()->map(function ($date) {
                return Carbon::parse($date)->locale('id')->isoFormat('dddd');
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

            // Merge the queried data with the full date range
            $purchaseData = $dateRange->merge($incomingData)->values()->all();
            $salesData = $dateRange->merge($outgoingData)->values()->all();

            $weeklyStats = [
                'period' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'readable_period' => $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y')
                ],
                'labels' => $daysOfWeek,
                'data' => [
                    'barang_masuk' => $purchaseData,
                    'barang_keluar' => $salesData
                ],
                'summary' => [
                    'total_barang_masuk_minggu_ini' => array_sum($purchaseData),
                    'total_barang_keluar_minggu_ini' => array_sum($salesData),
                    'rata_rata_masuk_per_hari' => round(array_sum($purchaseData) / 7, 2),
                    'rata_rata_keluar_per_hari' => round(array_sum($salesData) / 7, 2)
                ]
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Statistik mingguan berhasil diambil',
                'data' => $weeklyStats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil statistik mingguan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get monthly statistics 
     */
    public function getMonthlyStats(Request $request): JsonResponse
    {
        try {
            $year = $request->get('year', Carbon::now()->year);
            $month = $request->get('month', Carbon::now()->month);

            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            $monthlyStats = [
                'period' => [
                    'year' => $year,
                    'month' => $month,
                    'month_name' => $startDate->locale('id')->isoFormat('MMMM YYYY')
                ],
                'barang_masuk_bulan_ini' => IncomingItem::whereBetween('tanggal_masuk_barang', [$startDate, $endDate])->sum('jumlah_barang'),
                'barang_keluar_bulan_ini' => OutgoingItem::whereBetween('tanggal_keluar_barang', [$startDate, $endDate])->sum('jumlah_barang'),
                'transaksi_pembelian_bulan_ini' => IncomingItem::whereBetween('tanggal_masuk_barang', [$startDate, $endDate])->distinct('nota_transaksi')->count(),
                'transaksi_penjualan_bulan_ini' => OutgoingItem::whereBetween('tanggal_keluar_barang', [$startDate, $endDate])->distinct('nota_transaksi')->count(),
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Statistik bulanan berhasil diambil',
                'data' => $monthlyStats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil statistik bulanan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get complete dashboard data in one request
     * Combines all dashboard data for efficiency
     */
    public function getCompleteDashboard(Request $request): JsonResponse
    {
        try {
            $today = Carbon::today();

            // Main dashboard statistics
            $stats = [
                'barang_masuk_hari_ini' => IncomingItem::whereDate('tanggal_masuk_barang', $today)->sum('jumlah_barang'),
                'barang_keluar_hari_ini' => OutgoingItem::whereDate('tanggal_keluar_barang', $today)->sum('jumlah_barang'),
                'transaksi_penjualan_hari_ini' => OutgoingItem::whereDate('tanggal_keluar_barang', $today)->distinct('nota_transaksi')->count(),
                'transaksi_pembelian_hari_ini' => IncomingItem::whereDate('tanggal_masuk_barang', $today)->distinct('nota_transaksi')->count(),
            ];

            // Low stock items (threshold: 10)
            $lowStockItems = IncomingItem::select(['id', 'nama_barang', 'kategori_barang', 'jumlah_barang'])
                ->where('jumlah_barang', '>', 0)
                ->where('jumlah_barang', '<=', 10)
                ->orderBy('jumlah_barang', 'asc')
                ->take(5)
                ->get();

            // Recent activities
            $recentIncoming = IncomingItem::select(['nama_barang', 'jumlah_barang', 'tanggal_masuk_barang'])
                ->orderBy('tanggal_masuk_barang', 'desc')
                ->take(5)
                ->get();

            $recentOutgoing = OutgoingItem::select(['nama_barang', 'jumlah_barang', 'tanggal_keluar_barang'])
                ->orderBy('tanggal_keluar_barang', 'desc')
                ->take(5)
                ->get();

            $completeDashboard = [
                'statistics' => $stats,
                'low_stock_warning' => [
                    'count' => $lowStockItems->count(),
                    'items' => $lowStockItems
                ],
                'recent_activities' => [
                    'recent_incoming' => $recentIncoming,
                    'recent_outgoing' => $recentOutgoing
                ],
                'last_updated' => Carbon::now()->toISOString()
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Data dashboard lengkap berhasil diambil',
                'data' => $completeDashboard
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get stock notifications for manager dashboard
     * Returns both out-of-stock and low-stock notifications
     */
    public function getStockNotifications(Request $request): JsonResponse
    {
        try {
            $lowStockThreshold = $request->get('low_stock_threshold', 10);
            
            // Barang dengan stok habis (0 unit)
            $outOfStockItems = IncomingItem::where('jumlah_barang', 0)
                ->select([
                    'id',
                    'nama_barang',
                    'kategori_barang',
                    'lokasi_rak_barang',
                    'foto_barang'
                ])
                ->orderBy('nama_barang', 'asc')
                ->get();
            
            // Barang dengan stok rendah (1 sampai threshold)
            $lowStockItems = IncomingItem::whereBetween('jumlah_barang', [1, $lowStockThreshold])
                ->select([
                    'id',
                    'nama_barang',
                    'kategori_barang',
                    'jumlah_barang',
                    'lokasi_rak_barang',
                    'foto_barang'
                ])
                ->orderBy('jumlah_barang', 'asc')
                ->get();

            // Format data untuk response
            $outOfStockFormatted = $outOfStockItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_barang' => $item->nama_barang,
                    'kategori_barang' => $item->kategori_barang,
                    'jumlah_barang' => 0,
                    'lokasi_rak_barang' => $item->lokasi_rak_barang,
                    'foto_barang' => $item->foto_barang ? url('storage/' . $item->foto_barang) : null,
                    'status' => 'out_of_stock'
                ];
            });

            $lowStockFormatted = $lowStockItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_barang' => $item->nama_barang,
                    'kategori_barang' => $item->kategori_barang,
                    'jumlah_barang' => $item->jumlah_barang,
                    'lokasi_rak_barang' => $item->lokasi_rak_barang,
                    'foto_barang' => $item->foto_barang ? url('storage/' . $item->foto_barang) : null,
                    'status' => $item->jumlah_barang <= 5 ? 'critical_low' : 'low_stock'
                ];
            });

            $notifications = [
                'out_of_stock' => [
                    'count' => $outOfStockFormatted->count(),
                    'items' => $outOfStockFormatted
                ],
                'low_stock' => [
                    'count' => $lowStockFormatted->count(),
                    'items' => $lowStockFormatted
                ],
                'summary' => [
                    'total_notifications' => $outOfStockFormatted->count() + $lowStockFormatted->count(),
                    'critical_count' => $outOfStockFormatted->count() + $lowStockFormatted->where('status', 'critical_low')->count(),
                    'needs_attention' => $outOfStockFormatted->count() > 0 || $lowStockFormatted->count() > 0
                ]
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Notifikasi stok berhasil diambil',
                'data' => $notifications,
                'timestamp' => Carbon::now()->toISOString()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil notifikasi stok',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}