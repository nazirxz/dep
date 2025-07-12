<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\IncomingItem;
use App\Models\OutgoingItem;
use Carbon\Carbon;
use League\Csv\Reader;

class ItemManagementController extends Controller
{
    /**
     * Show the items index page (for Staff Admin Barang menu).
     */
    public function index()
    {
        $user = Auth::user();
        $incomingItems = IncomingItem::orderBy('tanggal_masuk_barang', 'desc')->get();
        $outgoingItems = OutgoingItem::orderBy('tanggal_keluar_barang', 'desc')->get();

        return view('staff_admin.items', [
            'incomingItems' => $incomingItems,
            'outgoingItems' => $outgoingItems,
        ]);
    }

    /**
     * Show the item management page (for Staff Admin Pengelolaan Barang menu).
     */
    public function itemManagement()
    {
        $user = Auth::user();
        $incomingItems = IncomingItem::orderBy('tanggal_masuk_barang', 'desc')->get();
        $outgoingItems = OutgoingItem::orderBy('tanggal_keluar_barang', 'desc')->get();

        return view('staff_admin.item_management', [
            'incomingItems' => $incomingItems,
            'outgoingItems' => $outgoingItems,
        ]);
    }

    /**
     * Store a new incoming item.
     */
    public function storeIncomingItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_barang' => 'required|string|max:255',
            'kategori_barang' => 'required|string|max:100',
            'jumlah_barang' => 'required|integer|min:1',
            'tanggal_masuk_barang' => 'required|date',
            'lokasi_rak_barang' => 'nullable|string|regex:/^R[1-8]-[1-4]-[1-6]$/',
        ], [
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'kategori_barang.required' => 'Kategori barang wajib diisi.',
            'jumlah_barang.required' => 'Jumlah barang wajib diisi.',
            'jumlah_barang.min' => 'Jumlah barang minimal 1.',
            'tanggal_masuk_barang.required' => 'Tanggal masuk wajib diisi.',
            'lokasi_rak_barang.regex' => 'Format lokasi rak tidak valid. Gunakan format R[1-8]-[1-4]-[1-6].',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if location is already occupied
        if ($request->lokasi_rak_barang) {
            $existingItem = IncomingItem::where('lokasi_rak_barang', $request->lokasi_rak_barang)->first();
            if ($existingItem) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lokasi rak sudah ditempati barang lain.'
                ], 422);
            }
        }

        try {
            $incomingItem = IncomingItem::create([
                'nama_barang' => $request->nama_barang,
                'kategori_barang' => $request->kategori_barang,
                'jumlah_barang' => $request->jumlah_barang,
                'tanggal_masuk_barang' => $request->tanggal_masuk_barang,
                'lokasi_rak_barang' => $request->lokasi_rak_barang,
                'status_barang' => $this->determineStatus($request->jumlah_barang),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil ditambahkan.',
                'data' => $incomingItem
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data.'
            ], 500);
        }
    }

    /**
     * Store a new outgoing item.
     */
    public function storeOutgoingItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_barang' => 'required|string|max:255',
            'kategori_barang' => 'required|string|max:100',
            'jumlah_barang' => 'required|integer|min:1',
            'tanggal_keluar_barang' => 'required|date',
            'tujuan_distribusi' => 'required|string|max:255',
            'lokasi_rak_barang' => 'nullable|string|regex:/^R[1-8]-[1-4]-[1-6]$/',
        ], [
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'kategori_barang.required' => 'Kategori barang wajib diisi.',
            'jumlah_barang.required' => 'Jumlah barang wajib diisi.',
            'jumlah_barang.min' => 'Jumlah barang minimal 1.',
            'tanggal_keluar_barang.required' => 'Tanggal keluar wajib diisi.',
            'tujuan_distribusi.required' => 'Tujuan distribusi wajib diisi.',
            'lokasi_rak_barang.regex' => 'Format lokasi rak tidak valid. Gunakan format R[1-8]-[1-4]-[1-6].',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check stock availability
        $incomingItem = IncomingItem::where('nama_barang', $request->nama_barang)
                                   ->where('kategori_barang', $request->kategori_barang)
                                   ->first();

        if (!$incomingItem) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan dalam stok.'
            ], 422);
        }

        if ($incomingItem->jumlah_barang < $request->jumlah_barang) {
            return response()->json([
                'success' => false,
                'message' => "Stok tidak mencukupi. Tersedia: {$incomingItem->jumlah_barang}, diminta: {$request->jumlah_barang}"
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Create outgoing item record
            $outgoingItem = OutgoingItem::create([
                'nama_barang' => $request->nama_barang,
                'kategori_barang' => $request->kategori_barang,
                'jumlah_barang' => $request->jumlah_barang,
                'tanggal_keluar_barang' => $request->tanggal_keluar_barang,
                'tujuan_distribusi' => $request->tujuan_distribusi,
                'lokasi_rak_barang' => $request->lokasi_rak_barang,
            ]);

            // Update incoming item stock
            $incomingItem->jumlah_barang -= $request->jumlah_barang;
            $incomingItem->status_barang = $this->determineStatus($incomingItem->jumlah_barang);
            $incomingItem->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Barang keluar berhasil diproses.',
                'data' => $outgoingItem
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses data.'
            ], 500);
        }
    }

    /**
     * Update an existing incoming item.
     */
    public function updateIncomingItem(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_barang' => 'required|string|max:255',
            'kategori_barang' => 'required|string|max:100',
            'jumlah_barang' => 'required|integer|min:0',
            'tanggal_masuk_barang' => 'required|date',
            'lokasi_rak_barang' => 'nullable|string|regex:/^R[1-8]-[1-4]-[1-6]$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $incomingItem = IncomingItem::findOrFail($id);

            // Check if location is available (if changed)
            if ($request->lokasi_rak_barang && $request->lokasi_rak_barang !== $incomingItem->lokasi_rak_barang) {
                $existingItem = IncomingItem::where('lokasi_rak_barang', $request->lokasi_rak_barang)
                                           ->where('id', '!=', $id)
                                           ->first();
                if ($existingItem) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Lokasi rak sudah ditempati barang lain.'
                    ], 422);
                }
            }

            $incomingItem->update([
                'nama_barang' => $request->nama_barang,
                'kategori_barang' => $request->kategori_barang,
                'jumlah_barang' => $request->jumlah_barang,
                'tanggal_masuk_barang' => $request->tanggal_masuk_barang,
                'lokasi_rak_barang' => $request->lokasi_rak_barang,
                'status_barang' => $this->determineStatus($request->jumlah_barang),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil diperbarui.',
                'data' => $incomingItem
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data.'
            ], 500);
        }
    }

    /**
     * Update an existing outgoing item.
     */
    public function updateOutgoingItem(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nama_barang' => 'required|string|max:255',
            'kategori_barang' => 'required|string|max:100',
            'jumlah_barang' => 'required|integer|min:1',
            'tanggal_keluar_barang' => 'required|date',
            'tujuan_distribusi' => 'required|string|max:255',
            'lokasi_rak_barang' => 'nullable|string|regex:/^R[1-8]-[1-4]-[1-6]$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $outgoingItem = OutgoingItem::findOrFail($id);

            $outgoingItem->update([
                'nama_barang' => $request->nama_barang,
                'kategori_barang' => $request->kategori_barang,
                'jumlah_barang' => $request->jumlah_barang,
                'tanggal_keluar_barang' => $request->tanggal_keluar_barang,
                'tujuan_distribusi' => $request->tujuan_distribusi,
                'lokasi_rak_barang' => $request->lokasi_rak_barang,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Barang keluar berhasil diperbarui.',
                'data' => $outgoingItem
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data.'
            ], 500);
        }
    }

    /**
     * Delete an incoming item.
     */
    public function deleteIncomingItem($id)
    {
        try {
            $incomingItem = IncomingItem::findOrFail($id);
            $incomingItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data.'
            ], 500);
        }
    }

    /**
     * Delete an outgoing item.
     */
    public function deleteOutgoingItem($id)
    {
        try {
            $outgoingItem = OutgoingItem::findOrFail($id);
            $outgoingItem->delete();

            return response()->json([
                'success' => true,
                'message' => 'Data barang keluar berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data.'
            ], 500);
        }
    }

    /**
     * Get item by ID.
     */
    public function getIncomingItem($id)
    {
        try {
            $incomingItem = IncomingItem::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $incomingItem
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan.'
            ], 404);
        }
    }
    public function showWarehouseMonitor()
    {
        $incomingItems = IncomingItem::orderBy('tanggal_masuk_barang', 'desc')->get();
        $outgoingItems = OutgoingItem::orderBy('tanggal_keluar_barang', 'desc')->get();

        return view('staff_admin.warehouse_monitor', [
            'incomingItems' => $incomingItems,
            'outgoingItems' => $outgoingItems,
        ]);
    }


    /**
     * Get outgoing item by ID.
     */
    public function getOutgoingItem($id)
    {
        try {
            $outgoingItem = OutgoingItem::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $outgoingItem
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data barang keluar tidak ditemukan.'
            ], 404);
        }
    }

    /**
     * Search items.
     */
    public function searchItems(Request $request)
    {
        $query = $request->get('q');
        $type = $request->get('type', 'incoming'); // incoming or outgoing

        try {
            if ($type === 'incoming') {
                $items = IncomingItem::where('nama_barang', 'like', '%' . $query . '%')
                                   ->orWhere('kategori_barang', 'like', '%' . $query . '%')
                                   ->orWhere('lokasi_rak_barang', 'like', '%' . $query . '%')
                                   ->orderBy('tanggal_masuk_barang', 'desc')
                                   ->get();
            } else {
                $items = OutgoingItem::where('nama_barang', 'like', '%' . $query . '%')
                                   ->orWhere('kategori_barang', 'like', '%' . $query . '%')
                                   ->orWhere('tujuan_distribusi', 'like', '%' . $query . '%')
                                   ->orderBy('tanggal_keluar_barang', 'desc')
                                   ->get();
            }

            return response()->json([
                'success' => true,
                'data' => $items
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari data.'
            ], 500);
        }
    }

    /**
     * Get items by category.
     */
    public function getItemsByCategory($category)
    {
        try {
            $incomingItems = IncomingItem::where('kategori_barang', $category)
                                       ->orderBy('tanggal_masuk_barang', 'desc')
                                       ->get();

            return response()->json([
                'success' => true,
                'data' => $incomingItems
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data.'
            ], 500);
        }
    }

    /**
     * Get dashboard statistics.
     */
    public function getDashboardStats()
    {
        try {
            $stats = [
                'total_incoming_items' => IncomingItem::count(),
                'total_outgoing_items' => OutgoingItem::count(),
                'total_stock' => IncomingItem::sum('jumlah_barang'),
                'low_stock_items' => IncomingItem::where('jumlah_barang', '<', 10)->count(),
                'empty_stock_items' => IncomingItem::where('jumlah_barang', '=', 0)->count(),
                'categories' => IncomingItem::distinct('kategori_barang')->count('kategori_barang'),
                'recent_incoming' => IncomingItem::orderBy('tanggal_masuk_barang', 'desc')->take(5)->get(),
                'recent_outgoing' => OutgoingItem::orderBy('tanggal_keluar_barang', 'desc')->take(5)->get(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil statistik.'
            ], 500);
        }
    }

    /**
     * Auto-assign locations for items without locations.
     */
    public function autoAssignLocations()
    {
        try {
            $itemsWithoutLocation = IncomingItem::whereNull('lokasi_rak_barang')
                                              ->orWhere('lokasi_rak_barang', '')
                                              ->get();

            if ($itemsWithoutLocation->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tidak ada barang yang memerlukan penempatan lokasi otomatis.',
                    'assigned_count' => 0
                ]);
            }

            $assignedCount = 0;
            $occupiedLocations = IncomingItem::whereNotNull('lokasi_rak_barang')
                                           ->where('lokasi_rak_barang', '!=', '')
                                           ->pluck('lokasi_rak_barang')
                                           ->toArray();

            foreach ($itemsWithoutLocation as $item) {
                $location = $this->findAvailableLocation($occupiedLocations);
                if ($location) {
                    $item->lokasi_rak_barang = $location;
                    $item->save();
                    $occupiedLocations[] = $location;
                    $assignedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Berhasil menetapkan lokasi untuk {$assignedCount} barang.",
                'assigned_count' => $assignedCount,
                'remaining_without_location' => $itemsWithoutLocation->count() - $assignedCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menetapkan lokasi otomatis.'
            ], 500);
        }
    }

    /**
     * Import items from CSV.
     */
    public function importFromCSV(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
            'has_header' => 'boolean',
            'type' => 'required|in:incoming,outgoing',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('csv_file');
            $hasHeader = $request->boolean('has_header');
            $type = $request->get('type');

            $csv = Reader::createFromPath($file->getPathname(), 'r');
            $csv->setHeaderOffset($hasHeader ? 0 : null);

            $records = $csv->getRecords();
            $importedCount = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($records as $index => $record) {
                try {
                    if ($type === 'incoming') {
                        $this->importIncomingItem($record, $index);
                    } else {
                        $this->importOutgoingItem($record, $index);
                    }
                    $importedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Baris " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil mengimpor {$importedCount} item.",
                'imported_count' => $importedCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export items to CSV.
     */
    public function exportToCSV(Request $request)
    {
        $type = $request->get('type', 'incoming');
        $format = $request->get('format', 'csv');

        try {
            $filename = $type . '_items_' . now()->format('Y-m-d_H-i-s') . '.' . $format;
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            if ($type === 'incoming') {
                $items = IncomingItem::all();
                $callback = function() use ($items) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, ['ID', 'Nama Barang', 'Kategori', 'Jumlah', 'Tanggal Masuk', 'Lokasi Rak', 'Status']);
                    foreach ($items as $item) {
                        fputcsv($file, [
                            $item->id,
                            $item->nama_barang,
                            $item->kategori_barang,
                            $item->jumlah_barang,
                            $item->tanggal_masuk_barang,
                            $item->lokasi_rak_barang,
                            $item->status_barang
                        ]);
                    }
                    fclose($file);
                };
            } else {
                $items = OutgoingItem::all();
                $callback = function() use ($items) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, ['ID', 'Nama Barang', 'Kategori', 'Jumlah', 'Tanggal Keluar', 'Tujuan Distribusi', 'Lokasi Rak']);
                    foreach ($items as $item) {
                        fputcsv($file, [
                            $item->id,
                            $item->nama_barang,
                            $item->kategori_barang,
                            $item->jumlah_barang,
                            $item->tanggal_keluar_barang,
                            $item->tujuan_distribusi,
                            $item->lokasi_rak_barang
                        ]);
                    }
                    fclose($file);
                };
            }

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengekspor data.'
            ], 500);
        }
    }

    /**
     * Generate barcode for item.
     */
    public function generateBarcode(Request $request, $id)
    {
        try {
            $item = IncomingItem::findOrFail($id);
            
            $barcodeData = [
                'id' => $item->id,
                'name' => $item->nama_barang,
                'code' => 'ITM' . str_pad($item->id, 6, '0', STR_PAD_LEFT),
                'category' => $item->kategori_barang,
                'location' => $item->lokasi_rak_barang,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Barcode berhasil dibuat.',
                'data' => $barcodeData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat barcode.'
            ], 500);
        }
    }

    /**
     * Generate QR Code for item.
     */
    public function generateQRCode(Request $request, $id)
    {
        try {
            $item = IncomingItem::findOrFail($id);
            
            $qrData = [
                'id' => $item->id,
                'name' => $item->nama_barang,
                'category' => $item->kategori_barang,
                'quantity' => $item->jumlah_barang,
                'location' => $item->lokasi_rak_barang,
                'date_added' => $item->tanggal_masuk_barang->format('Y-m-d'),
                'status' => $item->status_barang,
                'url' => url('/staff/items?item=' . $item->id)
            ];

            return response()->json([
                'success' => true,
                'message' => 'QR Code berhasil dibuat.',
                'data' => $qrData,
                'qr_string' => json_encode($qrData)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat QR Code.'
            ], 500);
        }
    }

    /**
     * Duplicate an existing item.
     */
    public function duplicateItem(Request $request, $id)
    {
        try {
            $originalItem = IncomingItem::findOrFail($id);
            
            $duplicatedItem = IncomingItem::create([
                'nama_barang' => $originalItem->nama_barang . ' (Copy)',
                'kategori_barang' => $originalItem->kategori_barang,
                'jumlah_barang' => $originalItem->jumlah_barang,
                'tanggal_masuk_barang' => now(),
                'lokasi_rak_barang' => null,
                'status_barang' => $this->determineStatus($originalItem->jumlah_barang),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil diduplikasi.',
                'data' => $duplicatedItem
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menduplikasi barang.'
            ], 500);
        }
    }

    /**
     * Get inventory report.
     */
    public function getInventoryReport(Request $request)
    {
        try {
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            $category = $request->get('category');

            $query = IncomingItem::query();

            if ($startDate && $endDate) {
                $query->whereBetween('tanggal_masuk_barang', [$startDate, $endDate]);
            }

            if ($category) {
                $query->where('kategori_barang', $category);
            }

            $items = $query->get();
            $totalValue = $items->sum('jumlah_barang');
            $categories = $items->groupBy('kategori_barang');

            $report = [
                'total_items' => $items->count(),
                'total_quantity' => $totalValue,
                'categories' => $categories->map(function ($items, $category) {
                    return [
                        'name' => $category,
                        'count' => $items->count(),
                        'total_quantity' => $items->sum('jumlah_barang'),
                        'items' => $items
                    ];
                }),
                'low_stock_items' => $items->where('jumlah_barang', '<', 10),
                'out_of_stock_items' => $items->where('jumlah_barang', '=', 0),
                'items_by_location' => $items->whereNotNull('lokasi_rak_barang')->groupBy('lokasi_rak_barang')
            ];

            return response()->json([
                'success' => true,
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat laporan.'
            ], 500);
        }
    }

    /**
     * Get movement history for an item.
     */
    public function getItemMovementHistory($id)
    {
        try {
            $incomingItem = IncomingItem::findOrFail($id);
            
            // Get outgoing items with same name and category
            $outgoingItems = OutgoingItem::where('nama_barang', $incomingItem->nama_barang)
                                       ->where('kategori_barang', $incomingItem->kategori_barang)
                                       ->orderBy('tanggal_keluar_barang', 'desc')
                                       ->get();

            $movements = [];
            
            // Add incoming record
            $movements[] = [
                'type' => 'incoming',
                'date' => $incomingItem->tanggal_masuk_barang,
                'quantity' => $incomingItem->jumlah_barang,
                'location' => $incomingItem->lokasi_rak_barang,
                'description' => 'Barang masuk'
            ];

            // Add outgoing records
            foreach ($outgoingItems as $outgoing) {
                $movements[] = [
                    'type' => 'outgoing',
                    'date' => $outgoing->tanggal_keluar_barang,
                    'quantity' => $outgoing->jumlah_barang,
                    'destination' => $outgoing->tujuan_distribusi,
                    'description' => 'Barang keluar ke ' . $outgoing->tujuan_distribusi
                ];
            }

            // Sort by date descending
            usort($movements, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'item' => $incomingItem,
                    'movements' => $movements
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil riwayat pergerakan.'
            ], 500);
        }
    }

    /**
     * Bulk update items.
     */
    public function bulkUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.id' => 'required|exists:incoming_items,id',
            'action' => 'required|in:update_category,update_location,delete',
            'category' => 'required_if:action,update_category|string|max:100',
            'location' => 'required_if:action,update_location|string|regex:/^R[1-8]-[1-4]-[1-6]$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $items = $request->get('items');
            $action = $request->get('action');
            $updatedCount = 0;

            DB::beginTransaction();

            foreach ($items as $itemData) {
                $item = IncomingItem::find($itemData['id']);
                if (!$item) continue;

                switch ($action) {
                    case 'update_category':
                        $item->kategori_barang = $request->get('category');
                        $item->save();
                        $updatedCount++;
                        break;

                    case 'update_location':
                        $location = $request->get('location');
                        // Check if location is already occupied
                        $existingItem = IncomingItem::where('lokasi_rak_barang', $location)
                                                  ->where('id', '!=', $item->id)
                                                  ->first();
                        if (!$existingItem) {
                            $item->lokasi_rak_barang = $location;
                            $item->save();
                            $updatedCount++;
                        }
                        break;

                    case 'delete':
                        $item->delete();
                        $updatedCount++;
                        break;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil memproses {$updatedCount} item.",
                'updated_count' => $updatedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses bulk update.'
            ], 500);
        }
    }

    /**
     * Get available locations.
     */
    public function getAvailableLocations()
    {
        try {
            $occupiedLocations = IncomingItem::whereNotNull('lokasi_rak_barang')
                                           ->where('lokasi_rak_barang', '!=', '')
                                           ->pluck('lokasi_rak_barang')
                                           ->toArray();

            $allLocations = [];
            for ($r = 1; $r <= 8; $r++) {
                for ($s = 1; $s <= 4; $s++) {
                    for ($p = 1; $p <= 6; $p++) {
                        $location = "R{$r}-{$s}-{$p}";
                        $allLocations[] = [
                            'location' => $location,
                            'available' => !in_array($location, $occupiedLocations)
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $allLocations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data lokasi.'
            ], 500);
        }
    }

    // ============ PRIVATE HELPER METHODS ============

    /**
     * Determine item status based on quantity.
     */
    private function determineStatus($quantity)
    {
        if ($quantity == 0) {
            return 'Habis';
        } elseif ($quantity < 10) {
            return 'Stok Rendah';
        } else {
            return 'Tersedia';
        }
    }

    /**
     * Find an available location for item placement.
     */
    private function findAvailableLocation($occupiedLocations)
    {
        for ($r = 1; $r <= 8; $r++) {
            for ($s = 1; $s <= 4; $s++) {
                for ($p = 1; $p <= 6; $p++) {
                    $location = "R{$r}-{$s}-{$p}";
                    if (!in_array($location, $occupiedLocations)) {
                        return $location;
                    }
                }
            }
        }
        return null;
    }

    /**
     * Import incoming item from CSV record.
     */
    private function importIncomingItem($record, $index)
    {
        $data = array_values($record);
        
        if (count($data) < 4) {
            throw new \Exception("Data tidak lengkap");
        }

        $validator = Validator::make([
            'nama_barang' => $data[0] ?? '',
            'kategori_barang' => $data[1] ?? '',
            'jumlah_barang' => $data[2] ?? 0,
            'tanggal_masuk_barang' => $data[3] ?? '',
            'lokasi_rak_barang' => $data[4] ?? null,
        ], [
            'nama_barang' => 'required|string|max:255',
            'kategori_barang' => 'required|string|max:100',
            'jumlah_barang' => 'required|integer|min:1',
            'tanggal_masuk_barang' => 'required|date',
            'lokasi_rak_barang' => 'nullable|string|regex:/^R[1-8]-[1-4]-[1-6]$/',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        IncomingItem::create([
            'nama_barang' => $data[0],
            'kategori_barang' => $data[1],
            'jumlah_barang' => $data[2],
            'tanggal_masuk_barang' => $data[3],
            'lokasi_rak_barang' => $data[4] ?? null,
            'status_barang' => $this->determineStatus($data[2]),
        ]);
    }

    /**
     * Import outgoing item from CSV record.
     */
    private function importOutgoingItem($record, $index)
    {
        $data = array_values($record);
        
        if (count($data) < 5) {
            throw new \Exception("Data tidak lengkap");
        }

        $validator = Validator::make([
            'nama_barang' => $data[0] ?? '',
            'kategori_barang' => $data[1] ?? '',
            'jumlah_barang' => $data[2] ?? 0,
            'tanggal_keluar_barang' => $data[3] ?? '',
            'tujuan_distribusi' => $data[4] ?? '',
            'lokasi_rak_barang' => $data[5] ?? null,
        ], [
            'nama_barang' => 'required|string|max:255',
            'kategori_barang' => 'required|string|max:100',
            'jumlah_barang' => 'required|integer|min:1',
            'tanggal_keluar_barang' => 'required|date',
            'tujuan_distribusi' => 'required|string|max:255',
            'lokasi_rak_barang' => 'nullable|string|regex:/^R[1-8]-[1-4]-[1-6]$/',
        ]);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        OutgoingItem::create([
            'nama_barang' => $data[0],
            'kategori_barang' => $data[1],
            'jumlah_barang' => $data[2],
            'tanggal_keluar_barang' => $data[3],
            'tujuan_distribusi' => $data[4],
            'lokasi_rak_barang' => $data[5] ?? null,
        ]);
    }
}