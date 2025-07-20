<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IncomingItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class IncomingItemApiController extends Controller
{
    /**
     * Get list of all incoming items (barang masuk)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Parameters for pagination and filtering
            $perPage = $request->get('per_page', 15);
            $kategori = $request->get('kategori');
            $search = $request->get('search');
            $sortBy = $request->get('sort_by', 'tanggal_masuk_barang');
            $sortOrder = $request->get('sort_order', 'desc');
            $stockFilter = $request->get('stock_filter'); // all, available, empty

            // Build query
            $query = IncomingItem::select([
                'id',
                'nama_barang',
                'kategori_barang',
                'category_id',
                'producer_id',
                'jumlah_barang',
                'harga_jual',
                'tanggal_masuk_barang',
                'lokasi_rak_barang',
                'metode_bayar',
                'foto_barang',
                'pembayaran_transaksi',
                'nota_transaksi'
            ])
            ->with(['category:id,nama_kategori', 'producer:id,nama_produsen_supplier']);

            // Filter by kategori
            if ($kategori && $kategori !== 'all') {
                $query->where('kategori_barang', $kategori);
            }

            // Filter by stock
            if ($stockFilter) {
                switch ($stockFilter) {
                    case 'available':
                        $query->where('jumlah_barang', '>', 0);
                        break;
                    case 'empty':
                        $query->where('jumlah_barang', '=', 0);
                        break;
                    case 'low_stock':
                        $query->where('jumlah_barang', '>', 0)->where('jumlah_barang', '<=', 10);
                        break;
                }
            }

            // Search functionality
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nama_barang', 'LIKE', "%{$search}%")
                      ->orWhere('kategori_barang', 'LIKE', "%{$search}%")
                      ->orWhere('lokasi_rak_barang', 'LIKE', "%{$search}%");
                });
            }

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $incomingItems = $query->paginate($perPage);

            // Transform data
            $formattedItems = $incomingItems->getCollection()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_barang' => $item->nama_barang,
                    'kategori_barang' => $item->kategori_barang,
                    'category_name' => $item->category ? $item->category->nama_kategori : null,
                    'producer_name' => $item->producer ? $item->producer->nama_produsen_supplier : null,
                    'jumlah_barang' => $item->jumlah_barang,
                    'harga_jual' => $item->harga_jual ? (float) $item->harga_jual : 0,
                    'tanggal_masuk_barang' => $item->tanggal_masuk_barang,
                    'lokasi_rak_barang' => $item->lokasi_rak_barang,
                    'metode_bayar' => $item->metode_bayar,
                    'foto_barang' => $item->foto_barang ? url('storage/' . $item->foto_barang) : null,
                    'pembayaran_transaksi' => $item->pembayaran_transaksi ? url('storage/' . $item->pembayaran_transaksi) : null,
                    'nota_transaksi' => $item->nota_transaksi ? url('storage/' . $item->nota_transaksi) : null,
                    'stock_status' => $this->getStockStatus($item->jumlah_barang),
                    'estimated_value' => $item->harga_jual ? (float)($item->harga_jual * $item->jumlah_barang) : 0
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Data barang masuk berhasil diambil',
                'data' => $formattedItems,
                'pagination' => [
                    'current_page' => $incomingItems->currentPage(),
                    'last_page' => $incomingItems->lastPage(),
                    'per_page' => $incomingItems->perPage(),
                    'total' => $incomingItems->total(),
                    'from' => $incomingItems->firstItem(),
                    'to' => $incomingItems->lastItem()
                ],
                'filters' => [
                    'kategori' => $kategori,
                    'search' => $search,
                    'stock_filter' => $stockFilter,
                    'sort_by' => $sortBy,
                    'sort_order' => $sortOrder
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data barang masuk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get incoming items by category
     */
    public function getByCategory(Request $request, $kategori): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search');
            $stockFilter = $request->get('stock_filter');

            $query = IncomingItem::select([
                'id',
                'nama_barang',
                'kategori_barang',
                'jumlah_barang',
                'harga_jual',
                'tanggal_masuk_barang',
                'lokasi_rak_barang',
                'foto_barang'
            ])
            ->where('kategori_barang', $kategori);

            // Apply stock filter
            if ($stockFilter === 'available') {
                $query->where('jumlah_barang', '>', 0);
            } elseif ($stockFilter === 'empty') {
                $query->where('jumlah_barang', '=', 0);
            }

            if ($search) {
                $query->where('nama_barang', 'LIKE', "%{$search}%");
            }

            $incomingItems = $query->orderBy('tanggal_masuk_barang', 'desc')
                                  ->paginate($perPage);

            $formattedItems = $incomingItems->getCollection()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_barang' => $item->nama_barang,
                    'kategori_barang' => $item->kategori_barang,
                    'jumlah_barang' => $item->jumlah_barang,
                    'harga_jual' => $item->harga_jual ? (float) $item->harga_jual : 0,
                    'tanggal_masuk_barang' => $item->tanggal_masuk_barang,
                    'lokasi_rak_barang' => $item->lokasi_rak_barang,
                    'foto_barang' => $item->foto_barang ? url('storage/' . $item->foto_barang) : null,
                    'stock_status' => $this->getStockStatus($item->jumlah_barang),
                    'estimated_value' => $item->harga_jual ? (float)($item->harga_jual * $item->jumlah_barang) : 0
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => "Data barang masuk kategori {$kategori} berhasil diambil",
                'data' => $formattedItems,
                'pagination' => [
                    'current_page' => $incomingItems->currentPage(),
                    'last_page' => $incomingItems->lastPage(),
                    'per_page' => $incomingItems->perPage(),
                    'total' => $incomingItems->total()
                ],
                'kategori' => $kategori
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data barang masuk berdasarkan kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single incoming item detail
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $incomingItem = IncomingItem::with(['category:id,nama_kategori', 'producer:id,nama_produsen_supplier'])
                                      ->find($id);

            if (!$incomingItem) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data barang masuk tidak ditemukan'
                ], 404);
            }

            $formattedItem = [
                'id' => $incomingItem->id,
                'nama_barang' => $incomingItem->nama_barang,
                'kategori_barang' => $incomingItem->kategori_barang,
                'category_name' => $incomingItem->category ? $incomingItem->category->nama_kategori : null,
                'producer_name' => $incomingItem->producer ? $incomingItem->producer->nama_produsen_supplier : null,
                'jumlah_barang' => $incomingItem->jumlah_barang,
                'harga_jual' => $incomingItem->harga_jual ? (float) $incomingItem->harga_jual : 0,
                'tanggal_masuk_barang' => $incomingItem->tanggal_masuk_barang,
                'lokasi_rak_barang' => $incomingItem->lokasi_rak_barang,
                'metode_bayar' => $incomingItem->metode_bayar,
                'foto_barang' => $incomingItem->foto_barang ? url('storage/' . $incomingItem->foto_barang) : null,
                'pembayaran_transaksi' => $incomingItem->pembayaran_transaksi ? url('storage/' . $incomingItem->pembayaran_transaksi) : null,
                'nota_transaksi' => $incomingItem->nota_transaksi ? url('storage/' . $incomingItem->nota_transaksi) : null,
                'stock_status' => $this->getStockStatus($incomingItem->jumlah_barang),
                'estimated_value' => $incomingItem->harga_jual ? (float)($incomingItem->harga_jual * $incomingItem->jumlah_barang) : 0,
                'created_at' => $incomingItem->created_at,
                'updated_at' => $incomingItem->updated_at,
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Detail barang masuk berhasil diambil',
                'data' => $formattedItem
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil detail barang masuk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all available categories from incoming items
     */
    public function getCategories(Request $request): JsonResponse
    {
        try {
            $categories = IncomingItem::select('kategori_barang')
                ->distinct()
                ->orderBy('kategori_barang', 'asc')
                ->pluck('kategori_barang')
                ->filter()
                ->values();

            return response()->json([
                'status' => 'success',
                'message' => 'Data kategori barang masuk berhasil diambil',
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
     * Get weekly incoming statistics
     */
    public function getWeeklyIncomingStats(Request $request): JsonResponse
    {
        try {
            // Get date range (default: last 7 days from latest data)
            $latestDate = IncomingItem::max('tanggal_masuk_barang');
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

            // Query for incoming items by day
            $dailyIncoming = IncomingItem::select(
                DB::raw('DATE(tanggal_masuk_barang) as date'),
                DB::raw('SUM(jumlah_barang) as total_quantity'),
                DB::raw('COUNT(DISTINCT nota_transaksi) as total_transactions'),
                DB::raw('SUM(CASE WHEN harga_jual IS NOT NULL THEN harga_jual * jumlah_barang ELSE 0 END) as total_value')
            )
            ->whereBetween('tanggal_masuk_barang', [$startDate, $endDate])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

            // Merge with date range
            $incomingQuantity = [];
            $incomingTransactions = [];
            $incomingValue = [];
            
            foreach ($dateRange->keys() as $date) {
                $incomingQuantity[] = $dailyIncoming->has($date) ? (int)$dailyIncoming[$date]->total_quantity : 0;
                $incomingTransactions[] = $dailyIncoming->has($date) ? (int)$dailyIncoming[$date]->total_transactions : 0;
                $incomingValue[] = $dailyIncoming->has($date) ? (float)$dailyIncoming[$date]->total_value : 0;
            }

            // Get category breakdown for the period
            $categoryBreakdown = IncomingItem::select(
                'kategori_barang',
                DB::raw('SUM(jumlah_barang) as total_quantity'),
                DB::raw('COUNT(*) as total_items'),
                DB::raw('SUM(CASE WHEN harga_jual IS NOT NULL THEN harga_jual * jumlah_barang ELSE 0 END) as total_value')
            )
            ->whereBetween('tanggal_masuk_barang', [$startDate, $endDate])
            ->groupBy('kategori_barang')
            ->orderBy('total_quantity', 'desc')
            ->get();

            // Get top incoming products
            $topProducts = IncomingItem::select(
                'nama_barang',
                'kategori_barang',
                DB::raw('SUM(jumlah_barang) as total_received'),
                DB::raw('AVG(harga_jual) as average_price')
            )
            ->whereBetween('tanggal_masuk_barang', [$startDate, $endDate])
            ->groupBy('nama_barang', 'kategori_barang')
            ->orderBy('total_received', 'desc')
            ->take(10)
            ->get();

            // Get suppliers/producers activity
            $producerActivity = IncomingItem::select(
                'producer_id',
                DB::raw('COUNT(*) as total_deliveries'),
                DB::raw('SUM(jumlah_barang) as total_items')
            )
            ->with('producer:id,nama_produsen_supplier')
            ->whereBetween('tanggal_masuk_barang', [$startDate, $endDate])
            ->groupBy('producer_id')
            ->orderBy('total_items', 'desc')
            ->take(10)
            ->get()
            ->map(function($item) {
                return [
                    'producer_name' => $item->producer ? $item->producer->nama_produsen_supplier : 'Unknown',
                    'total_deliveries' => $item->total_deliveries,
                    'total_items' => $item->total_items
                ];
            });

            $weeklyIncomingStats = [
                'period' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'readable_period' => $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y'),
                    'total_days' => $daysDiff
                ],
                'chart_data' => [
                    'labels' => $daysOfWeek,
                    'incoming_quantity' => $incomingQuantity,
                    'incoming_transactions' => $incomingTransactions,
                    'incoming_value' => $incomingValue
                ],
                'summary' => [
                    'total_items_received' => array_sum($incomingQuantity),
                    'total_transactions' => array_sum($incomingTransactions),
                    'total_inventory_value' => array_sum($incomingValue),
                    'average_items_per_day' => round(array_sum($incomingQuantity) / $daysDiff, 2),
                    'average_transactions_per_day' => round(array_sum($incomingTransactions) / $daysDiff, 2),
                    'average_value_per_day' => round(array_sum($incomingValue) / $daysDiff, 2)
                ],
                'category_breakdown' => $categoryBreakdown,
                'top_incoming_products' => $topProducts,
                'producer_activity' => $producerActivity
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Statistik penerimaan barang mingguan berhasil diambil',
                'data' => $weeklyIncomingStats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil statistik penerimaan barang mingguan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search incoming items
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q', '');
            $kategori = $request->get('kategori');
            $stockFilter = $request->get('stock_filter');
            $perPage = $request->get('per_page', 15);
            
            if (empty($query)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Parameter pencarian (q) tidak boleh kosong'
                ], 400);
            }

            $searchQuery = IncomingItem::select([
                'id',
                'nama_barang',
                'kategori_barang',
                'jumlah_barang',
                'harga_jual',
                'tanggal_masuk_barang',
                'lokasi_rak_barang',
                'foto_barang'
            ])
            ->where(function($q) use ($query) {
                $q->where('nama_barang', 'LIKE', "%{$query}%")
                  ->orWhere('kategori_barang', 'LIKE', "%{$query}%")
                  ->orWhere('lokasi_rak_barang', 'LIKE', "%{$query}%");
            });

            // Filter by category if provided
            if ($kategori && $kategori !== 'all') {
                $searchQuery->where('kategori_barang', $kategori);
            }

            // Filter by stock if provided
            if ($stockFilter === 'available') {
                $searchQuery->where('jumlah_barang', '>', 0);
            } elseif ($stockFilter === 'empty') {
                $searchQuery->where('jumlah_barang', '=', 0);
            }

            $results = $searchQuery->orderBy('tanggal_masuk_barang', 'desc')
                                  ->paginate($perPage);

            $formattedResults = $results->getCollection()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'nama_barang' => $item->nama_barang,
                    'kategori_barang' => $item->kategori_barang,
                    'jumlah_barang' => $item->jumlah_barang,
                    'harga_jual' => $item->harga_jual ? (float) $item->harga_jual : 0,
                    'tanggal_masuk_barang' => $item->tanggal_masuk_barang,
                    'lokasi_rak_barang' => $item->lokasi_rak_barang,
                    'foto_barang' => $item->foto_barang ? url('storage/' . $item->foto_barang) : null,
                    'stock_status' => $this->getStockStatus($item->jumlah_barang),
                    'estimated_value' => $item->harga_jual ? (float)($item->harga_jual * $item->jumlah_barang) : 0
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
                'kategori_filter' => $kategori,
                'stock_filter' => $stockFilter
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mencari barang masuk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get stock status based on quantity
     */
    private function getStockStatus($quantity): string
    {
        if ($quantity == 0) {
            return 'empty';
        } elseif ($quantity <= 5) {
            return 'critical';
        } elseif ($quantity <= 10) {
            return 'low';
        } else {
            return 'available';
        }
    }
}