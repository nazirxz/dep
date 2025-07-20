<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OutgoingItem;
use App\Models\IncomingItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OutgoingItemApiController extends Controller
{
    /**
     * Get list of all outgoing items (barang keluar)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Parameters for pagination and filtering
            $perPage = $request->get('per_page', 15);
            $kategori = $request->get('kategori');
            $search = $request->get('search');
            $sortBy = $request->get('sort_by', 'tanggal_keluar_barang');
            $sortOrder = $request->get('sort_order', 'desc');

            // Build query
            $query = OutgoingItem::select([
                'id',
                'nama_barang',
                'kategori_barang',
                'jumlah_barang',
                'tanggal_keluar_barang',
                'tujuan_distribusi',
                'lokasi_rak_barang',
                'nama_produsen',
                'metode_bayar',
                'foto_barang',
                'pembayaran_transaksi',
                'nota_transaksi'
            ]);

            // Filter by kategori
            if ($kategori && $kategori !== 'all') {
                $query->where('kategori_barang', $kategori);
            }

            // Search functionality
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nama_barang', 'LIKE', "%{$search}%")
                      ->orWhere('kategori_barang', 'LIKE', "%{$search}%")
                      ->orWhere('tujuan_distribusi', 'LIKE', "%{$search}%")
                      ->orWhere('nama_produsen', 'LIKE', "%{$search}%");
                });
            }

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $outgoingItems = $query->paginate($perPage);

            // Transform data
            $formattedItems = $outgoingItems->getCollection()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_barang' => $item->nama_barang,
                    'kategori_barang' => $item->kategori_barang,
                    'jumlah_barang' => $item->jumlah_barang,
                    'tanggal_keluar_barang' => $item->tanggal_keluar_barang,
                    'tujuan_distribusi' => $item->tujuan_distribusi,
                    'lokasi_rak_barang' => $item->lokasi_rak_barang,
                    'nama_produsen' => $item->nama_produsen,
                    'metode_bayar' => $item->metode_bayar,
                    'foto_barang' => $item->foto_barang ? url('storage/' . $item->foto_barang) : null,
                    'pembayaran_transaksi' => $item->pembayaran_transaksi ? url('storage/' . $item->pembayaran_transaksi) : null,
                    'nota_transaksi' => $item->nota_transaksi ? url('storage/' . $item->nota_transaksi) : null,
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Data barang keluar berhasil diambil',
                'data' => $formattedItems,
                'pagination' => [
                    'current_page' => $outgoingItems->currentPage(),
                    'last_page' => $outgoingItems->lastPage(),
                    'per_page' => $outgoingItems->perPage(),
                    'total' => $outgoingItems->total(),
                    'from' => $outgoingItems->firstItem(),
                    'to' => $outgoingItems->lastItem()
                ],
                'filters' => [
                    'kategori' => $kategori,
                    'search' => $search,
                    'sort_by' => $sortBy,
                    'sort_order' => $sortOrder
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data barang keluar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get outgoing items by category
     */
    public function getByCategory(Request $request, $kategori): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search');

            $query = OutgoingItem::select([
                'id',
                'nama_barang',
                'kategori_barang',
                'jumlah_barang',
                'tanggal_keluar_barang',
                'tujuan_distribusi',
                'nama_produsen',
                'foto_barang'
            ])
            ->where('kategori_barang', $kategori);

            if ($search) {
                $query->where('nama_barang', 'LIKE', "%{$search}%");
            }

            $outgoingItems = $query->orderBy('tanggal_keluar_barang', 'desc')
                                  ->paginate($perPage);

            $formattedItems = $outgoingItems->getCollection()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_barang' => $item->nama_barang,
                    'kategori_barang' => $item->kategori_barang,
                    'jumlah_barang' => $item->jumlah_barang,
                    'tanggal_keluar_barang' => $item->tanggal_keluar_barang,
                    'tujuan_distribusi' => $item->tujuan_distribusi,
                    'nama_produsen' => $item->nama_produsen,
                    'foto_barang' => $item->foto_barang ? url('storage/' . $item->foto_barang) : null,
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => "Data barang keluar kategori {$kategori} berhasil diambil",
                'data' => $formattedItems,
                'pagination' => [
                    'current_page' => $outgoingItems->currentPage(),
                    'last_page' => $outgoingItems->lastPage(),
                    'per_page' => $outgoingItems->perPage(),
                    'total' => $outgoingItems->total()
                ],
                'kategori' => $kategori
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data barang keluar berdasarkan kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single outgoing item detail
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $outgoingItem = OutgoingItem::find($id);

            if (!$outgoingItem) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data barang keluar tidak ditemukan'
                ], 404);
            }

            $formattedItem = [
                'id' => $outgoingItem->id,
                'nama_barang' => $outgoingItem->nama_barang,
                'kategori_barang' => $outgoingItem->kategori_barang,
                'jumlah_barang' => $outgoingItem->jumlah_barang,
                'tanggal_keluar_barang' => $outgoingItem->tanggal_keluar_barang,
                'tujuan_distribusi' => $outgoingItem->tujuan_distribusi,
                'lokasi_rak_barang' => $outgoingItem->lokasi_rak_barang,
                'nama_produsen' => $outgoingItem->nama_produsen,
                'metode_bayar' => $outgoingItem->metode_bayar,
                'foto_barang' => $outgoingItem->foto_barang ? url('storage/' . $outgoingItem->foto_barang) : null,
                'pembayaran_transaksi' => $outgoingItem->pembayaran_transaksi ? url('storage/' . $outgoingItem->pembayaran_transaksi) : null,
                'nota_transaksi' => $outgoingItem->nota_transaksi ? url('storage/' . $outgoingItem->nota_transaksi) : null,
                'created_at' => $outgoingItem->created_at,
                'updated_at' => $outgoingItem->updated_at,
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Detail barang keluar berhasil diambil',
                'data' => $formattedItem
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil detail barang keluar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all available categories from outgoing items
     */
    public function getCategories(Request $request): JsonResponse
    {
        try {
            $categories = OutgoingItem::select('kategori_barang')
                ->distinct()
                ->orderBy('kategori_barang', 'asc')
                ->pluck('kategori_barang')
                ->filter()
                ->values();

            return response()->json([
                'status' => 'success',
                'message' => 'Data kategori barang keluar berhasil diambil',
                'data' => $categories,
                'total_categories' => $categories->count()
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get weekly sales statistics
     */
    public function getWeeklySalesStats(Request $request): JsonResponse
    {
        try {
            // Get date range (default: last 7 days from latest data)
            $latestDate = OutgoingItem::max('tanggal_keluar_barang');
            $endDate = $latestDate ? 
                Carbon::parse($latestDate)->endOfDay() : 
                Carbon::now()->endOfDay();
            $startDate = $endDate->copy()->subDays(6)->startOfDay();

            // Custom date range if provided
            if ($request->has('start_date') && $request->has('end_date')) {
                $startDate = Carbon::parse($request->start_date)->startOfDay();
                $endDate = Carbon::parse($request->end_date)->endOfDay();
            }

            // Generate all dates for the range
            $dateRange = collect();
            $daysDiff = $startDate->diffInDays($endDate) + 1;
            
            for ($i = 0; $i < $daysDiff; $i++) {
                $date = $startDate->copy()->addDays($i);
                $dateRange->put($date->toDateString(), 0);
            }

            // Get day names
            $daysOfWeek = $dateRange->keys()->map(function ($date) {
                return Carbon::parse($date)->locale('id')->isoFormat('dddd');
            })->values()->all();

            // Query for outgoing items by day
            $dailySales = OutgoingItem::select(
                DB::raw('DATE(tanggal_keluar_barang) as date'),
                DB::raw('SUM(jumlah_barang) as total_quantity'),
                DB::raw('COUNT(DISTINCT nota_transaksi) as total_transactions')
            )
            ->whereBetween('tanggal_keluar_barang', [$startDate, $endDate])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

            // Merge with date range
            $salesQuantity = [];
            $salesTransactions = [];
            
            foreach ($dateRange->keys() as $date) {
                $salesQuantity[] = $dailySales->has($date) ? (int)$dailySales[$date]->total_quantity : 0;
                $salesTransactions[] = $dailySales->has($date) ? (int)$dailySales[$date]->total_transactions : 0;
            }

            // Get category breakdown for the period
            $categoryBreakdown = OutgoingItem::select(
                'kategori_barang',
                DB::raw('SUM(jumlah_barang) as total_quantity'),
                DB::raw('COUNT(*) as total_items')
            )
            ->whereBetween('tanggal_keluar_barang', [$startDate, $endDate])
            ->groupBy('kategori_barang')
            ->orderBy('total_quantity', 'desc')
            ->get();

            // Get top selling products
            $topProducts = OutgoingItem::select(
                'nama_barang',
                'kategori_barang',
                DB::raw('SUM(jumlah_barang) as total_sold')
            )
            ->whereBetween('tanggal_keluar_barang', [$startDate, $endDate])
            ->groupBy('nama_barang', 'kategori_barang')
            ->orderBy('total_sold', 'desc')
            ->take(10)
            ->get();

            $weeklySalesStats = [
                'period' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'readable_period' => $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y'),
                    'total_days' => $daysDiff
                ],
                'chart_data' => [
                    'labels' => $daysOfWeek,
                    'sales_quantity' => $salesQuantity,
                    'sales_transactions' => $salesTransactions
                ],
                'summary' => [
                    'total_items_sold' => array_sum($salesQuantity),
                    'total_transactions' => array_sum($salesTransactions),
                    'average_items_per_day' => round(array_sum($salesQuantity) / $daysDiff, 2),
                    'average_transactions_per_day' => round(array_sum($salesTransactions) / $daysDiff, 2)
                ],
                'category_breakdown' => $categoryBreakdown,
                'top_selling_products' => $topProducts
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Statistik penjualan mingguan berhasil diambil',
                'data' => $weeklySalesStats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil statistik penjualan mingguan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search outgoing items
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q', '');
            $kategori = $request->get('kategori');
            $perPage = $request->get('per_page', 15);
            
            if (empty($query)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Parameter pencarian (q) tidak boleh kosong'
                ], 400);
            }

            $searchQuery = OutgoingItem::select([
                'id',
                'nama_barang',
                'kategori_barang',
                'jumlah_barang',
                'tanggal_keluar_barang',
                'tujuan_distribusi',
                'foto_barang'
            ])
            ->where(function($q) use ($query) {
                $q->where('nama_barang', 'LIKE', "%{$query}%")
                  ->orWhere('kategori_barang', 'LIKE', "%{$query}%")
                  ->orWhere('tujuan_distribusi', 'LIKE', "%{$query}%");
            });

            // Filter by category if provided
            if ($kategori && $kategori !== 'all') {
                $searchQuery->where('kategori_barang', $kategori);
            }

            $results = $searchQuery->orderBy('tanggal_keluar_barang', 'desc')
                                  ->paginate($perPage);

            $formattedResults = $results->getCollection()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_barang' => $item->nama_barang,
                    'kategori_barang' => $item->kategori_barang,
                    'jumlah_barang' => $item->jumlah_barang,
                    'tanggal_keluar_barang' => $item->tanggal_keluar_barang,
                    'tujuan_distribusi' => $item->tujuan_distribusi,
                    'foto_barang' => $item->foto_barang ? url('storage/' . $item->foto_barang) : null,
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => "Hasil pencarian untuk '{$query}'",
                'data' => $formattedResults,
                'pagination' => [
                    'current_page' => $results->currentPage(),
                    'last_page' => $results->lastPage(),
                    'per_page' => $results->perPage(),
                    'total' => $results->total()
                ],
                'search_query' => $query,
                'kategori_filter' => $kategori
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mencari barang keluar',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}