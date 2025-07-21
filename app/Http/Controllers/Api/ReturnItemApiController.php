<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReturnedItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ReturnItemApiController extends Controller
{
    /**
     * Get list of all returned items (barang return)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Parameters for pagination and filtering
            $perPage = $request->get('per_page', 15);
            $kategori = $request->get('kategori');
            $search = $request->get('search');
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');

            // Build query
            $query = ReturnedItem::select([
                'id',
                'nama_barang',
                'kategori_barang',
                'jumlah_barang',
                'nama_produsen',
                'alasan_pengembalian',
                'foto_bukti',
                'created_at',
                'updated_at'
            ]);

            // Filter by kategori
            if ($kategori && $kategori !== 'all') {
                $query->where('kategori_barang', $kategori);
            }

            // Filter by date range
            if ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            }

            // Search functionality
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nama_barang', 'LIKE', "%{$search}%")
                      ->orWhere('kategori_barang', 'LIKE', "%{$search}%")
                      ->orWhere('nama_produsen', 'LIKE', "%{$search}%")
                      ->orWhere('alasan_pengembalian', 'LIKE', "%{$search}%");
                });
            }

            // Apply sorting
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $returnedItems = $query->paginate($perPage);

            // Transform data
            $formattedItems = $returnedItems->getCollection()->map(function ($item) {
                $fotoUrl = null;
                if ($item->foto_bukti) {
                    $fotoUrl = url('storage/' . $item->foto_bukti);
                }
                
                return [
                    'id' => $item->id,
                    'nama_barang' => $item->nama_barang,
                    'kategori_barang' => $item->kategori_barang,
                    'jumlah_barang' => $item->jumlah_barang,
                    'nama_produsen' => $item->nama_produsen,
                    'alasan_pengembalian' => $item->alasan_pengembalian,
                    'foto_bukti' => $item->foto_bukti,
                    'foto_url' => $fotoUrl,
                    'tanggal_pengembalian' => $item->created_at->format('Y-m-d'),
                    'waktu_pengembalian' => $item->created_at->format('Y-m-d H:i:s'),
                    'status_pengembalian' => $this->getReturnStatus($item->created_at),
                    'reason_category' => $this->categorizeReason($item->alasan_pengembalian),
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Data barang return berhasil diambil',
                'data' => $formattedItems,
                'pagination' => [
                    'current_page' => $returnedItems->currentPage(),
                    'last_page' => $returnedItems->lastPage(),
                    'per_page' => $returnedItems->perPage(),
                    'total' => $returnedItems->total(),
                    'from' => $returnedItems->firstItem(),
                    'to' => $returnedItems->lastItem()
                ],
                'filters' => [
                    'kategori' => $kategori,
                    'search' => $search,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'sort_by' => $sortBy,
                    'sort_order' => $sortOrder
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data barang return',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created returned item
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'nama_barang' => 'required|string|max:255',
                'kategori_barang' => 'required|string|max:255',
                'jumlah_barang' => 'required|integer|min:1',
                'nama_produsen' => 'nullable|string|max:255',
                'alasan_pengembalian' => 'required|string',
                'foto_bukti' => 'nullable|image|mimes:jpeg,jpg,png|max:2048' // max 2MB
            ]);

            $fotoPath = null;

            // Handle file upload if foto_bukti is provided
            if ($request->hasFile('foto_bukti')) {
                $file = $request->file('foto_bukti');
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                
                // Store file in public/storage/return_items folder
                $fotoPath = $file->storeAs('return_items', $fileName, 'public');
            }

            // Create the returned item
            $returnedItem = ReturnedItem::create([
                'nama_barang' => $validatedData['nama_barang'],
                'kategori_barang' => $validatedData['kategori_barang'],
                'jumlah_barang' => $validatedData['jumlah_barang'],
                'nama_produsen' => $validatedData['nama_produsen'],
                'alasan_pengembalian' => $validatedData['alasan_pengembalian'],
                'foto_bukti' => $fotoPath
            ]);

            // Generate full URL for foto if exists
            $fotoUrl = null;
            if ($returnedItem->foto_bukti) {
                $fotoUrl = url('storage/' . $returnedItem->foto_bukti);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Barang return berhasil ditambahkan',
                'data' => [
                    'id' => $returnedItem->id,
                    'nama_barang' => $returnedItem->nama_barang,
                    'kategori_barang' => $returnedItem->kategori_barang,
                    'jumlah_barang' => $returnedItem->jumlah_barang,
                    'nama_produsen' => $returnedItem->nama_produsen,
                    'alasan_pengembalian' => $returnedItem->alasan_pengembalian,
                    'foto_bukti' => $returnedItem->foto_bukti,
                    'foto_url' => $fotoUrl,
                    'created_at' => $returnedItem->created_at,
                    'updated_at' => $returnedItem->updated_at
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menambahkan barang return',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a returned item
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $returnedItem = ReturnedItem::find($id);

            if (!$returnedItem) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data barang return tidak ditemukan'
                ], 404);
            }

            // Validate the request data
            $validatedData = $request->validate([
                'nama_barang' => 'sometimes|required|string|max:255',
                'kategori_barang' => 'sometimes|required|string|max:255',
                'jumlah_barang' => 'sometimes|required|integer|min:1',
                'nama_produsen' => 'nullable|string|max:255',
                'alasan_pengembalian' => 'sometimes|required|string',
                'foto_bukti' => 'nullable|image|mimes:jpeg,jpg,png|max:2048'
            ]);

            $fotoPath = $returnedItem->foto_bukti; // Keep existing foto if not updated

            // Handle file upload if foto_bukti is provided
            if ($request->hasFile('foto_bukti')) {
                // Delete old foto if exists
                if ($returnedItem->foto_bukti && Storage::disk('public')->exists($returnedItem->foto_bukti)) {
                    Storage::disk('public')->delete($returnedItem->foto_bukti);
                }

                $file = $request->file('foto_bukti');
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $fotoPath = $file->storeAs('return_items', $fileName, 'public');
            }

            // Update the returned item
            $returnedItem->update([
                'nama_barang' => $validatedData['nama_barang'] ?? $returnedItem->nama_barang,
                'kategori_barang' => $validatedData['kategori_barang'] ?? $returnedItem->kategori_barang,
                'jumlah_barang' => $validatedData['jumlah_barang'] ?? $returnedItem->jumlah_barang,
                'nama_produsen' => $validatedData['nama_produsen'] ?? $returnedItem->nama_produsen,
                'alasan_pengembalian' => $validatedData['alasan_pengembalian'] ?? $returnedItem->alasan_pengembalian,
                'foto_bukti' => $fotoPath
            ]);

            // Generate full URL for foto if exists
            $fotoUrl = null;
            if ($returnedItem->foto_bukti) {
                $fotoUrl = url('storage/' . $returnedItem->foto_bukti);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Barang return berhasil diperbarui',
                'data' => [
                    'id' => $returnedItem->id,
                    'nama_barang' => $returnedItem->nama_barang,
                    'kategori_barang' => $returnedItem->kategori_barang,
                    'jumlah_barang' => $returnedItem->jumlah_barang,
                    'nama_produsen' => $returnedItem->nama_produsen,
                    'alasan_pengembalian' => $returnedItem->alasan_pengembalian,
                    'foto_bukti' => $returnedItem->foto_bukti,
                    'foto_url' => $fotoUrl,
                    'created_at' => $returnedItem->created_at,
                    'updated_at' => $returnedItem->updated_at
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak valid',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui barang return',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a returned item
     */
    public function destroy($id): JsonResponse
    {
        try {
            $returnedItem = ReturnedItem::find($id);

            if (!$returnedItem) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data barang return tidak ditemukan'
                ], 404);
            }

            // Delete foto if exists
            if ($returnedItem->foto_bukti && Storage::disk('public')->exists($returnedItem->foto_bukti)) {
                Storage::disk('public')->delete($returnedItem->foto_bukti);
            }

            $returnedItem->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Barang return berhasil dihapus'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menghapus barang return',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get returned items by category
     */
    public function getByCategory(Request $request, $kategori): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);
            $search = $request->get('search');

            $query = ReturnedItem::select([
                'id',
                'nama_barang',
                'kategori_barang',
                'jumlah_barang',
                'nama_produsen',
                'alasan_pengembalian',
                'foto_bukti',
                'created_at'
            ])
            ->where('kategori_barang', $kategori);

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('nama_barang', 'LIKE', "%{$search}%")
                      ->orWhere('nama_produsen', 'LIKE', "%{$search}%");
                });
            }

            $returnedItems = $query->orderBy('created_at', 'desc')
                                  ->paginate($perPage);

            $formattedItems = $returnedItems->getCollection()->map(function ($item) {
                $fotoUrl = null;
                if ($item->foto_bukti) {
                    $fotoUrl = url('storage/' . $item->foto_bukti);
                }
                
                return [
                    'id' => $item->id,
                    'nama_barang' => $item->nama_barang,
                    'kategori_barang' => $item->kategori_barang,
                    'jumlah_barang' => $item->jumlah_barang,
                    'nama_produsen' => $item->nama_produsen,
                    'alasan_pengembalian' => $item->alasan_pengembalian,
                    'foto_bukti' => $item->foto_bukti,
                    'foto_url' => $fotoUrl,
                    'tanggal_pengembalian' => $item->created_at->format('Y-m-d'),
                    'reason_category' => $this->categorizeReason($item->alasan_pengembalian),
                ];
            });

            return response()->json([
                'status' => 'success',
                'message' => "Data barang return kategori {$kategori} berhasil diambil",
                'data' => $formattedItems,
                'pagination' => [
                    'current_page' => $returnedItems->currentPage(),
                    'last_page' => $returnedItems->lastPage(),
                    'per_page' => $returnedItems->perPage(),
                    'total' => $returnedItems->total()
                ],
                'kategori' => $kategori
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data barang return berdasarkan kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single returned item detail
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $returnedItem = ReturnedItem::find($id);

            if (!$returnedItem) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data barang return tidak ditemukan'
                ], 404);
            }

            $fotoUrl = null;
            if ($returnedItem->foto_bukti) {
                $fotoUrl = url('storage/' . $returnedItem->foto_bukti);
            }

            $formattedItem = [
                'id' => $returnedItem->id,
                'nama_barang' => $returnedItem->nama_barang,
                'kategori_barang' => $returnedItem->kategori_barang,
                'jumlah_barang' => $returnedItem->jumlah_barang,
                'nama_produsen' => $returnedItem->nama_produsen,
                'alasan_pengembalian' => $returnedItem->alasan_pengembalian,
                'foto_bukti' => $returnedItem->foto_bukti,
                'foto_url' => $fotoUrl,
                'tanggal_pengembalian' => $returnedItem->created_at->format('Y-m-d'),
                'waktu_pengembalian' => $returnedItem->created_at->format('Y-m-d H:i:s'),
                'status_pengembalian' => $this->getReturnStatus($returnedItem->created_at),
                'reason_category' => $this->categorizeReason($returnedItem->alasan_pengembalian),
                'created_at' => $returnedItem->created_at,
                'updated_at' => $returnedItem->updated_at,
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Detail barang return berhasil diambil',
                'data' => $formattedItem
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil detail barang return',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all available categories from returned items
     */
    public function getCategories(Request $request): JsonResponse
    {
        try {
            $categories = ReturnedItem::select('kategori_barang')
                ->distinct()
                ->orderBy('kategori_barang', 'asc')
                ->pluck('kategori_barang')
                ->filter()
                ->values();

            return response()->json([
                'status' => 'success',
                'message' => 'Data kategori barang return berhasil diambil',
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
     * Get weekly return statistics
     */
    public function getWeeklyReturnStats(Request $request): JsonResponse
    {
        try {
            // Get date range (default: last 7 days from latest data)
            $latestDate = ReturnedItem::max('created_at');
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

            // Query for returned items by day
            $dailyReturns = ReturnedItem::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(jumlah_barang) as total_quantity'),
                DB::raw('COUNT(*) as total_returns')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

            // Merge with date range
            $returnQuantity = [];
            $returnCounts = [];
            
            foreach ($dateRange->keys() as $date) {
                $returnQuantity[] = $dailyReturns->has($date) ? (int)$dailyReturns[$date]->total_quantity : 0;
                $returnCounts[] = $dailyReturns->has($date) ? (int)$dailyReturns[$date]->total_returns : 0;
            }

            // Get category breakdown for the period
            $categoryBreakdown = ReturnedItem::select(
                'kategori_barang',
                DB::raw('SUM(jumlah_barang) as total_quantity'),
                DB::raw('COUNT(*) as total_returns')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('kategori_barang')
            ->orderBy('total_quantity', 'desc')
            ->get();

            // Get reason breakdown
            $reasonBreakdown = ReturnedItem::select(
                'alasan_pengembalian',
                DB::raw('SUM(jumlah_barang) as total_quantity'),
                DB::raw('COUNT(*) as total_returns')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('alasan_pengembalian')
            ->orderBy('total_returns', 'desc')
            ->get()
            ->map(function($item) {
                return [
                    'alasan_pengembalian' => $item->alasan_pengembalian,
                    'reason_category' => $this->categorizeReason($item->alasan_pengembalian),
                    'total_quantity' => $item->total_quantity,
                    'total_returns' => $item->total_returns
                ];
            });

            // Get top returned products
            $topReturnedProducts = ReturnedItem::select(
                'nama_barang',
                'kategori_barang',
                DB::raw('SUM(jumlah_barang) as total_returned'),
                DB::raw('COUNT(*) as return_frequency')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('nama_barang', 'kategori_barang')
            ->orderBy('total_returned', 'desc')
            ->take(10)
            ->get();

            // Get producer return analysis
            $producerReturnAnalysis = ReturnedItem::select(
                'nama_produsen',
                DB::raw('COUNT(*) as total_returns'),
                DB::raw('SUM(jumlah_barang) as total_items')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('nama_produsen')
            ->orderBy('total_returns', 'desc')
            ->take(10)
            ->get();

            $weeklyReturnStats = [
                'period' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'readable_period' => $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y'),
                    'total_days' => $daysDiff
                ],
                'chart_data' => [
                    'labels' => $daysOfWeek,
                    'return_quantity' => $returnQuantity,
                    'return_counts' => $returnCounts
                ],
                'summary' => [
                    'total_items_returned' => array_sum($returnQuantity),
                    'total_return_transactions' => array_sum($returnCounts),
                    'average_items_per_day' => round(array_sum($returnQuantity) / $daysDiff, 2),
                    'average_returns_per_day' => round(array_sum($returnCounts) / $daysDiff, 2)
                ],
                'category_breakdown' => $categoryBreakdown,
                'reason_breakdown' => $reasonBreakdown,
                'top_returned_products' => $topReturnedProducts,
                'producer_return_analysis' => $producerReturnAnalysis
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'Statistik pengembalian barang mingguan berhasil diambil',
                'data' => $weeklyReturnStats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil statistik pengembalian barang mingguan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search returned items
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q', '');
            $kategori = $request->get('kategori');
            $reasonCategory = $request->get('reason_category');
            $perPage = $request->get('per_page', 15);
            
            if (empty($query)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Parameter pencarian (q) tidak boleh kosong'
                ], 400);
            }

            $searchQuery = ReturnedItem::select([
                'id',
                'nama_barang',
                'kategori_barang',
                'jumlah_barang',
                'nama_produsen',
                'alasan_pengembalian',
                'foto_bukti',
                'created_at'
            ])
            ->where(function($q) use ($query) {
                $q->where('nama_barang', 'LIKE', "%{$query}%")
                  ->orWhere('kategori_barang', 'LIKE', "%{$query}%")
                  ->orWhere('nama_produsen', 'LIKE', "%{$query}%")
                  ->orWhere('alasan_pengembalian', 'LIKE', "%{$query}%");
            });

            // Filter by category if provided
            if ($kategori && $kategori !== 'all') {
                $searchQuery->where('kategori_barang', $kategori);
            }

            $results = $searchQuery->orderBy('created_at', 'desc')
                                  ->paginate($perPage);

            $formattedResults = $results->getCollection()->map(function ($item) use ($reasonCategory) {
                $itemReasonCategory = $this->categorizeReason($item->alasan_pengembalian);
                
                // Filter by reason category if provided
                if ($reasonCategory && $itemReasonCategory !== $reasonCategory) {
                    return null;
                }
                
                $fotoUrl = null;
                if ($item->foto_bukti) {
                    $fotoUrl = url('storage/' . $item->foto_bukti);
                }
                
                return [
                    'id' => $item->id,
                    'nama_barang' => $item->nama_barang,
                    'kategori_barang' => $item->kategori_barang,
                    'jumlah_barang' => $item->jumlah_barang,
                    'nama_produsen' => $item->nama_produsen,
                    'alasan_pengembalian' => $item->alasan_pengembalian,
                    'foto_bukti' => $item->foto_bukti,
                    'foto_url' => $fotoUrl,
                    'tanggal_pengembalian' => $item->created_at->format('Y-m-d'),
                    'reason_category' => $itemReasonCategory,
                ];
            })->filter();

            return response()->json([
                'status' => 'success',
                'message' => "Hasil pencarian untuk '{$query}'",
                'data' => $formattedResults->values(),
                'pagination' => [
                    'current_page' => $results->currentPage(),
                    'last_page' => $results->lastPage(),
                    'per_page' => $results->perPage(),
                    'total' => $formattedResults->count()
                ],
                'search_query' => $query,
                'kategori_filter' => $kategori,
                'reason_category_filter' => $reasonCategory
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mencari barang return',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get return status based on date
     */
    private function getReturnStatus($createdAt): string
    {
        $daysDiff = Carbon::now()->diffInDays($createdAt);
        
        if ($daysDiff <= 1) {
            return 'recent';
        } elseif ($daysDiff <= 7) {
            return 'this_week';
        } elseif ($daysDiff <= 30) {
            return 'this_month';
        } else {
            return 'old';
        }
    }

    /**
     * Categorize return reason
     */
    private function categorizeReason($reason): string
    {
        $reason = strtolower($reason);
        
        if (strpos($reason, 'rusak') !== false || strpos($reason, 'cacat') !== false) {
            return 'damaged';
        } elseif (strpos($reason, 'tidak sesuai') !== false || strpos($reason, 'salah') !== false) {
            return 'incorrect';
        } elseif (strpos($reason, 'kadaluarsa') !== false || strpos($reason, 'expired') !== false) {
            return 'expired';
        } elseif (strpos($reason, 'baik') !== false) {
            return 'quality_issue';
        } else {
            return 'other';
        }
    }
}