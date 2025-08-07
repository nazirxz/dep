<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\IncomingItem;
use App\Models\OutgoingItem;
use App\Models\VerificationItem;
use App\Models\ReturnedItem;
use App\Models\Producer;
use App\Models\Category;
use App\Models\WarehouseLocation;
use Carbon\Carbon;
use League\Csv\Reader;

class ItemManagementController extends Controller
{
    /**
     * Menampilkan halaman indeks barang (untuk menu Staff Admin Barang).
     */
    public function index()
    {
        $user = Auth::user();
        $incomingItems = IncomingItem::with(['producer', 'category'])
            ->orderBy('tanggal_masuk_barang', 'desc')
            ->get();
        $outgoingItems = OutgoingItem::with(['producer', 'category'])
            ->orderBy('tanggal_keluar_barang', 'desc')
            ->get();
        $producers = Producer::orderBy('nama_produsen_supplier')->get();
        $categories = Category::orderBy('nama_kategori')->get();

        return view('staff_admin.items', [
            'incomingItems' => $incomingItems,
            'outgoingItems' => $outgoingItems,
            'producers' => $producers,
            'categories' => $categories,
        ]);
    }

    /**
     * Menampilkan halaman pengelolaan barang (untuk menu Staff Admin Pengelolaan Barang).
     */
    public function itemManagement()
    {
        $user = Auth::user();
        $incomingItems = IncomingItem::with(['producer', 'category'])
            ->orderBy('tanggal_masuk_barang', 'desc')
            ->get();
        $outgoingItems = OutgoingItem::with(['producer', 'category'])
            ->orderBy('tanggal_keluar_barang', 'desc')
            ->get();
        $producers = Producer::orderBy('nama_produsen_supplier')->get();
        $categories = Category::orderBy('nama_kategori')->get();

        return view('staff_admin.item_management', [
            'incomingItems' => $incomingItems,
            'outgoingItems' => $outgoingItems,
            'producers' => $producers,
            'categories' => $categories,
        ]);
    }

    /**
     * Menyimpan barang masuk baru.
     */
    public function storeIncomingItem(Request $request)
    {
        \Log::info('storeIncomingItem: Request received', ['request_data' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'nama_barang' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'producer_id' => 'required|exists:producers,id',
            'jumlah_barang' => 'required|integer|min:1',
            'harga_jual' => 'nullable|numeric|min:0', // Validasi untuk harga jual
            'tanggal_masuk_barang' => 'required|date',
            'lokasi_rak_barang' => 'nullable|string|regex:/^R[1-8]-[1-4]-[1-6]$/',
            'metode_bayar' => 'nullable|string|max:50',
            'pembayaran_transaksi' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048',
            'nota_transaksi' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048',
            'foto_barang' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'foto_option' => 'required|in:tidak_perlu,existing,upload',
            'foto_barang_existing' => 'nullable|string',
        ], [
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'category_id.required' => 'Kategori barang wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'producer_id.required' => 'Produsen barang wajib dipilih.',
            'producer_id.exists' => 'Produsen yang dipilih tidak valid.',
            'jumlah_barang.required' => 'Jumlah barang wajib diisi.',
            'jumlah_barang.min' => 'Jumlah barang minimal 1.',
            'harga_jual.numeric' => 'Harga jual harus berupa angka.',
            'harga_jual.min' => 'Harga jual tidak boleh negatif.',
            'tanggal_masuk_barang.required' => 'Tanggal masuk barang wajib diisi.',
            'lokasi_rak_barang.regex' => 'Format lokasi rak tidak valid. Gunakan format R[1-8]-[1-4]-[1-6].',
            'pembayaran_transaksi.mimes' => 'File bukti pembayaran harus berformat: jpeg, png, jpg, gif, svg, atau pdf.',
            'pembayaran_transaksi.max' => 'Ukuran file bukti pembayaran maksimal 2MB.',
            'nota_transaksi.mimes' => 'File nota transaksi harus berformat: jpeg, png, jpg, gif, svg, atau pdf.',
            'nota_transaksi.max' => 'Ukuran file nota transaksi maksimal 2MB.',
            'foto_barang.image' => 'File foto barang harus berupa gambar.',
            'foto_barang.mimes' => 'File foto barang harus berformat: jpeg, png, jpg, gif, atau svg.',
            'foto_barang.max' => 'Ukuran file foto barang maksimal 2MB.',
            'foto_option.required' => 'Opsi foto barang wajib dipilih.',
            'foto_option.in' => 'Opsi foto barang tidak valid.',
        ]);

        if ($validator->fails()) {
            \Log::warning('storeIncomingItem: Validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        // Capacity validation for warehouse location
        if ($request->lokasi_rak_barang) {
            $location = WarehouseLocation::where('location_name', $request->lokasi_rak_barang)->first();
            if (!$location) {
                // Create new location with default capacity
                $location = WarehouseLocation::create([
                    'location_name' => $request->lokasi_rak_barang,
                    'max_capacity' => 300,
                    'current_capacity' => 0
                ]);
            }

            // Check if adding this quantity would exceed capacity
            if (!$location->canAccommodate($request->jumlah_barang)) {
                \Log::warning('storeIncomingItem: Capacity exceeded', [
                    'location' => $request->lokasi_rak_barang,
                    'current_capacity' => $location->current_capacity,
                    'max_capacity' => $location->max_capacity,
                    'requested_quantity' => $request->jumlah_barang,
                    'available_capacity' => $location->getAvailableCapacity()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => "Kapasitas rak tidak mencukupi. Lokasi {$request->lokasi_rak_barang} memiliki kapasitas maksimal {$location->max_capacity} unit. Saat ini terisi {$location->current_capacity} unit. Tersisa {$location->getAvailableCapacity()} unit, tetapi Anda mencoba menambah {$request->jumlah_barang} unit.",
                    'capacity_info' => [
                        'max_capacity' => $location->max_capacity,
                        'current_capacity' => $location->current_capacity,
                        'available_capacity' => $location->getAvailableCapacity(),
                        'requested_quantity' => $request->jumlah_barang
                    ]
                ], 422);
            }
        }

        try {
            // Handle file uploads
            $pembayaranTransaksiPath = null;
            $notaTransaksiPath = null;
            $fotoPath = null;

            if ($request->hasFile('pembayaran_transaksi')) {
                $pembayaranTransaksiPath = $request->file('pembayaran_transaksi')->store('transactions', 'public');
                \Log::info('storeIncomingItem: Pembayaran Transaksi uploaded', ['path' => $pembayaranTransaksiPath]);
            }

            if ($request->hasFile('nota_transaksi')) {
                $notaTransaksiPath = $request->file('nota_transaksi')->store('transactions', 'public');
                \Log::info('storeIncomingItem: Nota Transaksi uploaded', ['path' => $notaTransaksiPath]);
            }

            // Handle photo based on selected option
            if ($request->foto_option === 'upload' && $request->hasFile('foto_barang')) {
                $fotoPath = $request->file('foto_barang')->store('items', 'public');
                \Log::info('storeIncomingItem: Foto Barang uploaded', ['path' => $fotoPath]);
            } elseif ($request->foto_option === 'existing' && $request->foto_barang_existing) {
                $fotoPath = $request->foto_barang_existing;
                \Log::info('storeIncomingItem: Using existing photo', ['path' => $fotoPath]);
            } else {
                // foto_option is 'tidak_perlu' or no valid photo provided
                $fotoPath = null;
                \Log::info('storeIncomingItem: No photo selected');
            }

            // Get kategori name for storage
            $kategori = Category::find($request->category_id);
            $kategoriName = $kategori ? $kategori->nama_kategori : 'Lainnya';

            // Create verification item first (barang masuk ke tabel verifikasi dulu)
            $verificationItem = VerificationItem::create([
                'nama_barang' => $request->nama_barang,
                'kategori_barang' => $kategoriName,
                'category_id' => $request->category_id,
                'producer_id' => $request->producer_id,
                'jumlah_barang' => $request->jumlah_barang,
                'harga_jual' => $request->harga_jual,
                'tanggal_masuk_barang' => $request->tanggal_masuk_barang,
                'lokasi_rak_barang' => $request->lokasi_rak_barang,
                'metode_bayar' => $request->metode_bayar,
                'pembayaran_transaksi' => $pembayaranTransaksiPath,
                'nota_transaksi' => $notaTransaksiPath,
                'foto_barang' => $fotoPath,
                'status' => 'pending', // Status pending menunggu verifikasi
                'verified_at' => null,
                'verified_by' => null,
            ]);

            \Log::info('storeIncomingItem: Item added to verification table', ['verification_id' => $verificationItem->id, 'data' => $verificationItem->toArray()]);

            return response()->json([
                'success' => true,
                'message' => 'Barang masuk berhasil ditambahkan ke daftar verifikasi. Menunggu verifikasi dari admin.',
                'data' => $verificationItem
            ]);

        } catch (\Exception $e) {
            // Clean up uploaded files if there's an error
            if (isset($fotoPath)) {
                Storage::disk('public')->delete($fotoPath);
            }
            if (isset($pembayaranTransaksiPath)) {
                Storage::disk('public')->delete($pembayaranTransaksiPath);
            }
            if (isset($notaTransaksiPath)) {
                Storage::disk('public')->delete($notaTransaksiPath);
            }

            \Log::error('Error in storeIncomingItem: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menyimpan barang keluar baru.
     */
    public function storeOutgoingItem(Request $request)
    {
        \Log::info('storeOutgoingItem: Request received', ['request_data' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'nama_barang' => 'required|string|max:255',
            'kategori_barang' => 'required|string|max:100',
            'jumlah_barang' => 'required|integer|min:1',
            'tanggal_keluar_barang' => 'required|date',
            'tujuan_distribusi' => 'required|string|max:255',
            'lokasi_rak_barang' => 'nullable|string|regex:/^R[1-8]-[1-4]-[1-6]$/', // Opsional, tapi penting untuk penarikan spesifik
            'nama_produsen' => 'nullable|string|max:255', // Kolom baru
            'metode_bayar' => 'nullable|string|max:50', // Kolom baru
            'pembayaran_transaksi' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048', // Changed to image
            'nota_transaksi' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048', // Changed to image
            'foto_barang' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Added validation for image
        ], [
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'kategori_barang.required' => 'Kategori barang wajib diisi.',
            'jumlah_barang.required' => 'Jumlah barang wajib diisi.',
            'jumlah_barang.min' => 'Jumlah barang minimal 1.',
            'tanggal_keluar_barang.required' => 'Tanggal keluar wajib diisi.',
            'tujuan_distribusi.required' => 'Tujuan distribusi wajib diisi.',
            'lokasi_rak_barang.regex' => 'Format lokasi rak tidak valid. Gunakan format R[1-8]-[1-4]-[1-6].',
            'pembayaran_transaksi.image' => 'Pembayaran transaksi harus berupa gambar atau PDF.',
            'pembayaran_transaksi.mimes' => 'Format file pembayaran transaksi yang diizinkan: jpeg, png, jpg, gif, svg, pdf.',
            'pembayaran_transaksi.max' => 'Ukuran file pembayaran transaksi maksimal 2MB.',
            'nota_transaksi.image' => 'Nota transaksi harus berupa gambar atau PDF.',
            'nota_transaksi.mimes' => 'Format file nota transaksi yang diizinkan: jpeg, png, jpg, gif, svg, pdf.',
            'nota_transaksi.max' => 'Ukuran file nota transaksi maksimal 2MB.',
            'foto_barang.image' => 'File harus berupa gambar.',
            'foto_barang.mimes' => 'Format gambar yang diizinkan: jpeg, png, jpg, gif, svg.',
            'foto_barang.max' => 'Ukuran gambar maksimal 2MB.',
        ]);

        if ($validator->fails()) {
            \Log::warning('storeOutgoingItem: Validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        $fotoPath = null;
        $pembayaranTransaksiPath = null;
        $notaTransaksiPath = null;

        if ($request->hasFile('foto_barang')) {
            $fotoPath = $request->file('foto_barang')->store('images', 'public');
            \Log::info('storeOutgoingItem: Image uploaded', ['path' => $fotoPath]);
        } else {
            // If no new photo is uploaded, try to get it from the incoming item
            $incomingItemForPhoto = IncomingItem::where('nama_barang', $request->nama_barang)
                                                ->where('kategori_barang', $request->kategori_barang)
                                                ->first();
            if ($incomingItemForPhoto && $incomingItemForPhoto->foto_barang) {
                $fotoPath = $incomingItemForPhoto->foto_barang;
            }
        }

        if ($request->hasFile('pembayaran_transaksi')) {
            $pembayaranTransaksiPath = $request->file('pembayaran_transaksi')->store('transactions', 'public');
            \Log::info('storeOutgoingItem: Pembayaran Transaksi uploaded', ['path' => $pembayaranTransaksiPath]);
        } else {
            $incomingItemForPayment = IncomingItem::where('nama_barang', $request->nama_barang)
                                                  ->where('kategori_barang', $request->kategori_barang)
                                                  ->first();
            if ($incomingItemForPayment && $incomingItemForPayment->pembayaran_transaksi) {
                $pembayaranTransaksiPath = $incomingItemForPayment->pembayaran_transaksi;
            }
        }

        if ($request->hasFile('nota_transaksi')) {
            $notaTransaksiPath = $request->file('nota_transaksi')->store('transactions', 'public');
            \Log::info('storeOutgoingItem: Nota Transaksi uploaded', ['path' => $notaTransaksiPath]);
        } else {
            $incomingItemForNota = IncomingItem::where('nama_barang', $request->nama_barang)
                                               ->where('kategori_barang', $request->kategori_barang)
                                               ->first();
            if ($incomingItemForNota && $incomingItemForNota->nota_transaksi) {
                $notaTransaksiPath = $incomingItemForNota->nota_transaksi;
            }
        }


        // Cek ketersediaan stok berdasarkan nama barang, kategori, dan lokasi rak (jika disediakan)
        $incomingItemQuery = IncomingItem::where('nama_barang', $request->nama_barang)
                                         ->where('kategori_barang', $request->kategori_barang);

        if ($request->lokasi_rak_barang) {
            $incomingItemQuery->where('lokasi_rak_barang', $request->lokasi_rak_barang);
        }

        $incomingItem = $incomingItemQuery->first();
        \Log::info('storeOutgoingItem: Checking incoming item stock', ['nama_barang' => $request->nama_barang, 'kategori_barang' => $request->kategori_barang, 'lokasi_rak_barang' => $request->lokasi_rak_barang, 'found_item' => $incomingItem ? $incomingItem->toArray() : 'not found']);


        if (!$incomingItem) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan dalam stok atau di lokasi rak yang ditentukan.'
            ], 422);
        }

        if ($incomingItem->jumlah_barang < $request->jumlah_barang) {
            \Log::warning('storeOutgoingItem: Insufficient stock', ['available' => $incomingItem->jumlah_barang, 'requested' => $request->jumlah_barang]);
            return response()->json([
                'success' => false,
                'message' => "Stok tidak mencukupi. Tersedia: {$incomingItem->jumlah_barang}, diminta: {$request->jumlah_barang}"
            ], 422);
        }

        try {
            DB::beginTransaction();
            \Log::info('storeOutgoingItem: DB Transaction started');

            // Buat catatan barang keluar
            $outgoingItem = OutgoingItem::create([
                'nama_barang' => $request->nama_barang,
                'kategori_barang' => $request->kategori_barang,
                'jumlah_barang' => $request->jumlah_barang,
                'tanggal_keluar_barang' => $request->tanggal_keluar_barang,
                'tujuan_distribusi' => $request->tujuan_distribusi,
                'lokasi_rak_barang' => $request->lokasi_rak_barang, // Simpan lokasi rak dari mana barang keluar
                'nama_produsen' => $request->nama_produsen, // Kolom baru
                'metode_bayar' => $request->metode_bayar, // Kolom baru
                'pembayaran_transaksi' => $pembayaranTransaksiPath, // Save the image path
                'nota_transaksi' => $notaTransaksiPath, // Save the image path
                'foto_barang' => $fotoPath, // Save the image path
            ]);
            \Log::info('storeOutgoingItem: Outgoing item created', ['outgoing_item_id' => $outgoingItem->id, 'data' => $outgoingItem->toArray()]);


            // Perbarui stok barang masuk
            $incomingItem->jumlah_barang -= $request->jumlah_barang;
            $incomingItem->save();
            \Log::info('storeOutgoingItem: Incoming item stock updated', ['item_id' => $incomingItem->id, 'final_qty' => $incomingItem->jumlah_barang]);


            // Jika stok menjadi 0 dan lokasi rak tidak null, kosongkan lokasi rak
            if ($incomingItem->jumlah_barang == 0 && $incomingItem->lokasi_rak_barang) {
                $incomingItem->lokasi_rak_barang = null;
                $incomingItem->save();
                \Log::info('storeOutgoingItem: Incoming item rack location cleared due to zero stock', ['item_id' => $incomingItem->id]);
            }

            DB::commit();
            \Log::info('storeOutgoingItem: DB Transaction committed successfully');

            return response()->json([
                'success' => true,
                'message' => 'Barang keluar berhasil diproses.',
                'data' => $outgoingItem
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            // If an error occurs after file upload, delete the uploaded files
            if ($fotoPath && $request->hasFile('foto_barang')) {
                Storage::disk('public')->delete($fotoPath);
            }
            if ($pembayaranTransaksiPath && $request->hasFile('pembayaran_transaksi')) {
                Storage::disk('public')->delete($pembayaranTransaksiPath);
            }
            if ($notaTransaksiPath && $request->hasFile('nota_transaksi')) {
                Storage::disk('public')->delete($notaTransaksiPath);
            }
            \Log::error('Error in storeOutgoingItem: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Memperbarui barang masuk yang sudah ada.
     */
    public function updateIncomingItem(Request $request, $id)
    {
        \Log::info('updateIncomingItem: Request received', ['item_id' => $id, 'request_data' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'nama_barang' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'producer_id' => 'required|exists:producers,id',
            'jumlah_barang' => 'required|integer|min:0',
            'harga_jual' => 'nullable|numeric|min:0', // Validasi untuk harga jual
            'tanggal_masuk_barang' => 'required|date',
            'lokasi_rak_barang' => 'nullable|string|regex:/^R[1-8]-[1-4]-[1-6]$/',
            'metode_bayar' => 'nullable|string|max:50',
            'pembayaran_transaksi' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048',
            'nota_transaksi' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048',
            'foto_barang' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            // Fields untuk menghapus file yang ada
            'pembayaran_transaksi_removed' => 'nullable|boolean',
            'nota_transaksi_removed' => 'nullable|boolean',
            'foto_barang_removed' => 'nullable|boolean',
        ], [
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'category_id.required' => 'Kategori barang wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'producer_id.required' => 'Produsen barang wajib dipilih.',
            'producer_id.exists' => 'Produsen yang dipilih tidak valid.',
            'jumlah_barang.required' => 'Jumlah barang wajib diisi.',
            'jumlah_barang.min' => 'Jumlah barang tidak boleh negatif.',
            'harga_jual.numeric' => 'Harga jual harus berupa angka.',
            'harga_jual.min' => 'Harga jual tidak boleh negatif.',
            'tanggal_masuk_barang.required' => 'Tanggal masuk barang wajib diisi.',
            'lokasi_rak_barang.regex' => 'Format lokasi rak tidak valid. Gunakan format R[1-8]-[1-4]-[1-6].',
            'pembayaran_transaksi.mimes' => 'File bukti pembayaran harus berformat: jpeg, png, jpg, gif, svg, atau pdf.',
            'pembayaran_transaksi.max' => 'Ukuran file bukti pembayaran maksimal 2MB.',
            'nota_transaksi.mimes' => 'File nota transaksi harus berformat: jpeg, png, jpg, gif, svg, atau pdf.',
            'nota_transaksi.max' => 'Ukuran file nota transaksi maksimal 2MB.',
            'foto_barang.image' => 'File foto barang harus berupa gambar.',
            'foto_barang.mimes' => 'File foto barang harus berformat: jpeg, png, jpg, gif, atau svg.',
            'foto_barang.max' => 'Ukuran file foto barang maksimal 2MB.',
        ]);

        if ($validator->fails()) {
            \Log::warning('updateIncomingItem: Validation failed', ['item_id' => $id, 'errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $incomingItem = IncomingItem::findOrFail($id);
            \Log::info('updateIncomingItem: Found item', ['item_id' => $incomingItem->id, 'current_data' => $incomingItem->toArray()]);

            // Handle file uploads and removals
            $pembayaranTransaksiPathToSave = $incomingItem->pembayaran_transaksi;
            $notaTransaksiPathToSave = $incomingItem->nota_transaksi;
            $fotoPathToSave = $incomingItem->foto_barang;

            // Handle pembayaran_transaksi
            if ($request->has('pembayaran_transaksi_removed') && $request->pembayaran_transaksi_removed) {
                if ($incomingItem->pembayaran_transaksi) {
                    Storage::disk('public')->delete($incomingItem->pembayaran_transaksi);
                }
                $pembayaranTransaksiPathToSave = null;
            } elseif ($request->hasFile('pembayaran_transaksi')) {
                if ($incomingItem->pembayaran_transaksi) {
                    Storage::disk('public')->delete($incomingItem->pembayaran_transaksi);
                }
                $pembayaranTransaksiPathToSave = $request->file('pembayaran_transaksi')->store('transactions', 'public');
                \Log::info('updateIncomingItem: New pembayaran_transaksi uploaded', ['path' => $pembayaranTransaksiPathToSave]);
            }

            // Handle nota_transaksi
            if ($request->has('nota_transaksi_removed') && $request->nota_transaksi_removed) {
                if ($incomingItem->nota_transaksi) {
                    Storage::disk('public')->delete($incomingItem->nota_transaksi);
                }
                $notaTransaksiPathToSave = null;
            } elseif ($request->hasFile('nota_transaksi')) {
                if ($incomingItem->nota_transaksi) {
                    Storage::disk('public')->delete($incomingItem->nota_transaksi);
                }
                $notaTransaksiPathToSave = $request->file('nota_transaksi')->store('transactions', 'public');
                \Log::info('updateIncomingItem: New nota_transaksi uploaded', ['path' => $notaTransaksiPathToSave]);
            }

            // Handle foto_barang
            if ($request->has('foto_barang_removed') && $request->foto_barang_removed) {
                if ($incomingItem->foto_barang) {
                    Storage::disk('public')->delete($incomingItem->foto_barang);
                }
                $fotoPathToSave = null;
            } elseif ($request->hasFile('foto_barang')) {
                if ($incomingItem->foto_barang) {
                    Storage::disk('public')->delete($incomingItem->foto_barang);
                }
                $fotoPathToSave = $request->file('foto_barang')->store('items', 'public');
                \Log::info('updateIncomingItem: New foto_barang uploaded', ['path' => $fotoPathToSave]);
            }

            // Get kategori name for storage
            $kategori = Category::find($request->category_id);
            $kategoriName = $kategori ? $kategori->nama_kategori : 'Lainnya';

            // Update incoming item with harga_jual
            $incomingItem->update([
                'nama_barang' => $request->nama_barang,
                'kategori_barang' => $kategoriName,
                'category_id' => $request->category_id,
                'producer_id' => $request->producer_id,
                'jumlah_barang' => $request->jumlah_barang,
                'harga_jual' => $request->harga_jual, // Update harga_jual
                'tanggal_masuk_barang' => $request->tanggal_masuk_barang,
                'lokasi_rak_barang' => $request->lokasi_rak_barang,
                'metode_bayar' => $request->metode_bayar,
                'pembayaran_transaksi' => $pembayaranTransaksiPathToSave,
                'nota_transaksi' => $notaTransaksiPathToSave,
                'foto_barang' => $fotoPathToSave,
            ]);

            \Log::info('updateIncomingItem: Item updated successfully', ['item_id' => $incomingItem->id, 'updated_data' => $incomingItem->toArray()]);

            return response()->json([
                'success' => true,
                'message' => 'Barang masuk berhasil diperbarui.',
                'data' => $incomingItem
            ]);

        } catch (\Exception $e) {
            // Clean up uploaded files if there's an error
            if (isset($fotoPathToSave) && $request->hasFile('foto_barang')) {
                Storage::disk('public')->delete($fotoPathToSave);
            }
            if (isset($pembayaranTransaksiPathToSave) && $request->hasFile('pembayaran_transaksi')) {
                Storage::disk('public')->delete($pembayaranTransaksiPathToSave);
            }
            if (isset($notaTransaksiPathToSave) && $request->hasFile('nota_transaksi')) {
                Storage::disk('public')->delete($notaTransaksiPathToSave);
            }

            \Log::error('Error in updateIncomingItem: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Memperbarui barang keluar yang sudah ada.
     */
    public function updateOutgoingItem(Request $request, $id)
    {
        \Log::info('updateOutgoingItem: Request received', ['item_id' => $id, 'request_data' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'nama_barang' => 'required|string|max:255',
            'kategori_barang' => 'required|string|max:100',
            'jumlah_barang' => 'required|integer|min:1',
            'tanggal_keluar_barang' => 'required|date',
            'tujuan_distribusi' => 'required|string|max:255',
            'lokasi_rak_barang' => 'nullable|string|regex:/^R[1-8]-[1-4]-[1-6]$/',
            'nama_produsen' => 'nullable|string|max:255', // Kolom baru
            'metode_bayar' => 'nullable|string|max:50', // Kolom baru
            'pembayaran_transaksi' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048', // Changed to image
            'nota_transaksi' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048', // Changed to image
            'foto_barang' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Added validation for image
        ]);

        if ($validator->fails()) {
            \Log::warning('updateOutgoingItem: Validation failed', ['item_id' => $id, 'errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $outgoingItem = OutgoingItem::findOrFail($id);
            $oldFotoPath = $outgoingItem->foto_barang;
            $fotoPathToSave = $oldFotoPath;

            $oldPembayaranTransaksiPath = $outgoingItem->pembayaran_transaksi;
            $pembayaranTransaksiPathToSave = $oldPembayaranTransaksiPath;

            $oldNotaTransaksiPath = $outgoingItem->nota_transaksi;
            $notaTransaksiPathToSave = $oldNotaTransaksiPath;

            // Handle foto_barang upload
            if ($request->hasFile('foto_barang')) {
                $fotoPathToSave = $request->file('foto_barang')->store('images', 'public');
                if ($oldFotoPath) {
                    Storage::disk('public')->delete($oldFotoPath);
                }
            } else if ($request->input('foto_barang_removed', false)) {
                if ($oldFotoPath) {
                    Storage::disk('public')->delete($oldFotoPath);
                }
                $fotoPathToSave = null;
            } else {
                $fotoPathToSave = $oldFotoPath;
            }

            // Handle pembayaran_transaksi upload
            if ($request->hasFile('pembayaran_transaksi')) {
                $pembayaranTransaksiPathToSave = $request->file('pembayaran_transaksi')->store('transactions', 'public');
                if ($oldPembayaranTransaksiPath) {
                    Storage::disk('public')->delete($oldPembayaranTransaksiPath);
                }
            } else if ($request->input('pembayaran_transaksi_removed', false)) {
                if ($oldPembayaranTransaksiPath) {
                    Storage::disk('public')->delete($oldPembayaranTransaksiPath);
                }
                $pembayaranTransaksiPathToSave = null;
            } else {
                $pembayaranTransaksiPathToSave = $oldPembayaranTransaksiPath;
            }

            // Handle nota_transaksi upload
            if ($request->hasFile('nota_transaksi')) {
                $notaTransaksiPathToSave = $request->file('nota_transaksi')->store('transactions', 'public');
                if ($oldNotaTransaksiPath) {
                    Storage::disk('public')->delete($oldNotaTransaksiPath);
                }
            } else if ($request->input('nota_transaksi_removed', false)) {
                if ($oldNotaTransaksiPath) {
                    Storage::disk('public')->delete($oldNotaTransaksiPath);
                }
                $notaTransaksiPathToSave = null;
            } else {
                $notaTransaksiPathToSave = $oldNotaTransaksiPath;
            }

            \Log::info('updateOutgoingItem: Found item', ['item_id' => $outgoingItem->id, 'current_data' => $outgoingItem->toArray()]);

            $outgoingItem->update([
                'nama_barang' => $request->nama_barang,
                'kategori_barang' => $request->kategori_barang,
                'jumlah_barang' => $request->jumlah_barang,
                'tanggal_keluar_barang' => $request->tanggal_keluar_barang,
                'tujuan_distribusi' => $request->tujuan_distribusi,
                'lokasi_rak_barang' => $request->lokasi_rak_barang,
                'nama_produsen' => $request->nama_produsen, // Kolom baru
                'metode_bayar' => $request->metode_bayar, // Kolom baru
                'pembayaran_transaksi' => $pembayaranTransaksiPathToSave, // Save the updated image path
                'nota_transaksi' => $notaTransaksiPathToSave, // Save the updated image path
                'foto_barang' => $fotoPathToSave, // Save the updated image path
            ]);
            \Log::info('updateOutgoingItem: Item updated successfully', ['item_id' => $outgoingItem->id, 'updated_data' => $outgoingItem->toArray()]);

            return response()->json([
                'success' => true,
                'message' => 'Barang keluar berhasil diperbarui.',
                'data' => $outgoingItem
            ]);
        } catch (\Exception $e) {
            if (isset($fotoPathToSave) && $request->hasFile('foto_barang')) {
                Storage::disk('public')->delete($fotoPathToSave);
            }
            if (isset($pembayaranTransaksiPathToSave) && $request->hasFile('pembayaran_transaksi')) {
                Storage::disk('public')->delete($pembayaranTransaksiPathToSave);
            }
            if (isset($notaTransaksiPathToSave) && $request->hasFile('nota_transaksi')) {
                Storage::disk('public')->delete($notaTransaksiPathToSave);
            }
            // Log error for debugging
            \Log::error('Error in updateOutgoingItem: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus barang masuk.
     */
    public function deleteIncomingItem($id)
    {
        \Log::info('deleteIncomingItem: Request received', ['item_id' => $id]);
        try {
            $incomingItem = IncomingItem::findOrFail($id);
            // Delete associated photo if exists
            if ($incomingItem->foto_barang) {
                Storage::disk('public')->delete($incomingItem->foto_barang);
                \Log::info('deleteIncomingItem: Associated image deleted', ['path' => $incomingItem->foto_barang]);
            }
            // Delete associated pembayaran_transaksi if exists
            if ($incomingItem->pembayaran_transaksi) {
                Storage::disk('public')->delete($incomingItem->pembayaran_transaksi);
                \Log::info('deleteIncomingItem: Associated pembayaran_transaksi deleted', ['path' => $incomingItem->pembayaran_transaksi]);
            }
            // Delete associated nota_transaksi if exists
            if ($incomingItem->nota_transaksi) {
                Storage::disk('public')->delete($incomingItem->nota_transaksi);
                \Log::info('deleteIncomingItem: Associated nota_transaksi deleted', ['path' => $incomingItem->nota_transaksi]);
            }

            \Log::info('deleteIncomingItem: Found item', ['item_id' => $incomingItem->id, 'data' => $incomingItem->toArray()]);
            $incomingItem->delete();
            \Log::info('deleteIncomingItem: Item deleted successfully', ['item_id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            // Log error for debugging
            \Log::error('Error in deleteIncomingItem: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menghapus barang keluar.
     */
    public function deleteOutgoingItem($id)
    {
        \Log::info('deleteOutgoingItem: Request received', ['item_id' => $id]);
        try {
            $outgoingItem = OutgoingItem::findOrFail($id);
            // Delete associated photo if exists
            if ($outgoingItem->foto_barang && Storage::disk('public')->exists($outgoingItem->foto_barang)) {
                Storage::disk('public')->delete($outgoingItem->foto_barang);
                \Log::info('deleteOutgoingItem: Associated image deleted', ['path' => $outgoingItem->foto_barang]);
            }
            // Delete associated pembayaran_transaksi if exists
            if ($outgoingItem->pembayaran_transaksi && Storage::disk('public')->exists($outgoingItem->pembayaran_transaksi)) {
                Storage::disk('public')->delete($outgoingItem->pembayaran_transaksi);
                \Log::info('deleteOutgoingItem: Associated pembayaran_transaksi deleted', ['path' => $outgoingItem->pembayaran_transaksi]);
            }
            // Delete associated nota_transaksi if exists
            if ($outgoingItem->nota_transaksi && Storage::disk('public')->exists($outgoingItem->nota_transaksi)) {
                Storage::disk('public')->delete($outgoingItem->nota_transaksi);
                \Log::info('deleteOutgoingItem: Associated nota_transaksi deleted', ['path' => $outgoingItem->nota_transaksi]);
            }

            \Log::info('deleteOutgoingItem: Found item', ['item_id' => $outgoingItem->id, 'data' => $outgoingItem->toArray()]);
            $outgoingItem->delete();
            \Log::info('deleteOutgoingItem: Item deleted successfully', ['item_id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Data barang keluar berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            // Log error for debugging
            \Log::error('Error in deleteOutgoingItem: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mendapatkan barang berdasarkan ID.
     */
    public function getIncomingItem($id)
    {
        \Log::info('getIncomingItem: Request received', ['item_id' => $id]);
        try {
            $incomingItem = IncomingItem::with(['producer', 'category'])->findOrFail($id);
            \Log::info('getIncomingItem: Item found', ['item_id' => $incomingItem->id, 'data' => $incomingItem->toArray()]);
            return response()->json([
                'success' => true,
                'data' => $incomingItem
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getIncomingItem: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Barang tidak ditemukan.'
            ], 404);
        }
    }

    /**
     * Menampilkan halaman monitor gudang.
     * Mengambil barang masuk dan mengagregasinya berdasarkan lokasi dan nama barang.
     */
    public function showWarehouseMonitor()
    {
        \Log::info('showWarehouseMonitor: Accessing warehouse monitor page');
        // Mendapatkan semua barang masuk yang memiliki lokasi rak yang ditetapkan
        // dan yang kuantitasnya lebih besar dari 0
        $incomingItems = IncomingItem::whereNotNull('lokasi_rak_barang')
                                   ->where('lokasi_rak_barang', '!=', '')
                                   ->where('jumlah_barang', '>', 0)
                                   ->get();
        \Log::info('showWarehouseMonitor: Fetched incoming items with location', ['count' => $incomingItems->count()]);

        // Mengelompokkan item berdasarkan lokasi_rak_barang dan kemudian berdasarkan nama_barang,
        // dan menjumlahkan kuantitasnya.
        // Ini membuat koleksi bersarang: lokasi -> nama_item -> data_agregasi
        $aggregatedItems = $incomingItems->groupBy('lokasi_rak_barang')->map(function ($itemsByLocation) {
            return $itemsByLocation->groupBy('nama_barang')->map(function ($itemsByName) {
                return [
                    'nama_barang' => $itemsByName->first()->nama_barang,
                    'jumlah_barang' => $itemsByName->sum('jumlah_barang'),
                ];
            })->first(); // Ambil item teragregasi pertama untuk lokasi tersebut (dengan asumsi item unik per rak)
        });
        \Log::info('showWarehouseMonitor: Aggregated items by location', ['aggregated_count' => $aggregatedItems->count()]);

        // Sync warehouse locations and get capacity information
        $this->syncWarehouseLocations();
        $warehouseLocations = WarehouseLocation::all()->keyBy('location_name');

        // Add capacity information to aggregated items
        $aggregatedItemsWithCapacity = $aggregatedItems->map(function ($item, $locationName) use ($warehouseLocations) {
            $location = $warehouseLocations->get($locationName);
            if ($location) {
                $item['max_capacity'] = $location->max_capacity;
                $item['current_capacity'] = $location->current_capacity;
                $item['available_capacity'] = $location->getAvailableCapacity();
                $item['capacity_percentage'] = $location->getCapacityPercentage();
            } else {
                // Default values if location not found in warehouse_locations table
                $item['max_capacity'] = 300;
                $item['current_capacity'] = $item['jumlah_barang'];
                $item['available_capacity'] = 300 - $item['jumlah_barang'];
                $item['capacity_percentage'] = round(($item['jumlah_barang'] / 300) * 100, 2);
            }
            return $item;
        });

        // Mendapatkan jumlah total rak yang terisi untuk statistik
        $occupiedRacksCount = $incomingItems->unique('lokasi_rak_barang')->count();
        \Log::info('showWarehouseMonitor: Occupied racks count', ['count' => $occupiedRacksCount]);

        return view('staff_admin.warehouse_monitor', [
            'aggregatedItems' => $aggregatedItemsWithCapacity, // Meneruskan data teragregasi dengan info kapasitas
            'occupiedRacksCount' => $occupiedRacksCount, // Meneruskan jumlah untuk statistik
            'maxCapacityPerLocation' => 300, // Maximum capacity per location
        ]);
    }

    /**
     * Sync warehouse locations with current data from incoming items
     */
    private function syncWarehouseLocations()
    {
        $locations = IncomingItem::whereNotNull('lokasi_rak_barang')
            ->where('lokasi_rak_barang', '!=', '')
            ->distinct()
            ->pluck('lokasi_rak_barang');

        foreach ($locations as $locationName) {
            $currentCapacity = IncomingItem::where('lokasi_rak_barang', $locationName)
                ->where('jumlah_barang', '>', 0)
                ->sum('jumlah_barang');

            WarehouseLocation::updateOrCreate(
                ['location_name' => $locationName],
                [
                    'max_capacity' => 300,
                    'current_capacity' => $currentCapacity
                ]
            );
        }
    }

    /**
     * Check warehouse capacity for a specific location
     */
    public function checkWarehouseCapacity(Request $request)
    {
        \Log::info('checkWarehouseCapacity: Request received', ['request_data' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'location_name' => 'required|string',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $locationName = $request->location_name;
            $requestedQuantity = $request->quantity;

            // Get or create location
            $location = WarehouseLocation::where('location_name', $locationName)->first();
            if (!$location) {
                // Calculate current capacity for new location
                $currentCapacity = IncomingItem::where('lokasi_rak_barang', $locationName)
                    ->where('jumlah_barang', '>', 0)
                    ->sum('jumlah_barang');

                $location = WarehouseLocation::create([
                    'location_name' => $locationName,
                    'max_capacity' => 300,
                    'current_capacity' => $currentCapacity
                ]);
            } else {
                // Update current capacity from actual data
                $location->updateCurrentCapacity();
            }

            // Check if can accommodate the requested quantity
            $canAccommodate = $location->canAccommodate($requestedQuantity);
            
            // Calculate what the capacity would be AFTER adding this quantity
            $projectedCapacity = $location->current_capacity + $requestedQuantity;
            $projectedPercentage = ($projectedCapacity / $location->max_capacity) * 100;
            
            \Log::info('checkWarehouseCapacity: Capacity calculation', [
                'location' => $locationName,
                'current_capacity' => $location->current_capacity,
                'max_capacity' => $location->max_capacity,
                'requested_quantity' => $requestedQuantity,
                'projected_capacity' => $projectedCapacity,
                'projected_percentage' => $projectedPercentage,
                'can_accommodate' => $canAccommodate
            ]);

            return response()->json([
                'success' => $canAccommodate,
                'message' => $canAccommodate 
                    ? 'Kapasitas mencukupi' 
                    : "Kapasitas tidak mencukupi. Tersisa {$location->getAvailableCapacity()} unit dari maksimal {$location->max_capacity} unit.",
                'capacity_info' => [
                    'location_name' => $location->location_name,
                    'max_capacity' => $location->max_capacity,
                    'current_capacity' => $location->current_capacity,
                    'available_capacity' => $location->getAvailableCapacity(),
                    'requested_quantity' => $requestedQuantity,
                    'can_accommodate' => $canAccommodate,
                    'capacity_percentage' => $location->getCapacityPercentage(),
                    'projected_capacity' => $projectedCapacity,
                    'projected_percentage' => round($projectedPercentage, 1),
                    'will_exceed' => $projectedCapacity > $location->max_capacity
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in checkWarehouseCapacity: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memeriksa kapasitas: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Mendapatkan barang keluar berdasarkan ID.
     */
    public function getOutgoingItem($id)
    {
        \Log::info('getOutgoingItem: Request received', ['item_id' => $id]);
        try {
            $outgoingItem = OutgoingItem::with(['producer', 'category'])->findOrFail($id);
            \Log::info('getOutgoingItem: Item found', ['item_id' => $outgoingItem->id, 'data' => $outgoingItem->toArray()]);
            return response()->json([
                'success' => true,
                'data' => $outgoingItem
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getOutgoingItem: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Data barang keluar tidak ditemukan.'
            ], 404);
        }
    }

    /**
     * Mencari barang.
     */
    public function searchItems(Request $request)
    {
        \Log::info('searchItems: Request received', ['query' => $request->get('q'), 'type' => $request->get('type')]);
        $query = $request->get('q');
        $type = $request->get('type', 'incoming'); // incoming atau outgoing

        try {
            if ($type === 'incoming') {
                $items = IncomingItem::where('nama_barang', 'like', '%' . $query . '%')
                                   ->orWhere('kategori_barang', 'like', '%' . $query . '%')
                                   ->orWhere('lokasi_rak_barang', 'like', '%' . $query . '%')
                                   ->orWhere('nama_produsen', 'like', '%' . $query . '%') // Kolom baru
                                   ->orWhere('nota_transaksi', 'like', '%' . $query . '%') // Kolom baru
                                   ->orderBy('tanggal_masuk_barang', 'desc')
                                   ->get();
            } else {
                $items = OutgoingItem::where('nama_barang', 'like', '%' . $query . '%')
                                   ->orWhere('kategori_barang', 'like', '%' . $query . '%')
                                   ->orWhere('tujuan_distribusi', 'like', '%' . $query . '%')
                                   ->orWhere('nama_produsen', 'like', '%' . $query . '%') // Kolom baru
                                   ->orWhere('nota_transaksi', 'like', '%' . $query . '%') // Kolom baru
                                   ->orderBy('tanggal_keluar_barang', 'desc')
                                   ->get();
            }
            \Log::info('searchItems: Items found', ['count' => $items->count(), 'type' => $type]);

            return response()->json([
                'success' => true,
                'data' => $items
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in searchItems: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mendapatkan barang berdasarkan kategori.
     */
    public function getItemsByCategory($category)
    {
        \Log::info('getItemsByCategory: Request received', ['category' => $category]);
        try {
            $incomingItems = IncomingItem::where('kategori_barang', $category)
                                       ->orderBy('tanggal_masuk_barang', 'desc')
                                       ->get();
            \Log::info('getItemsByCategory: Items found', ['count' => $incomingItems->count(), 'category' => $category]);

            return response()->json([
                'success' => true,
                'data' => $incomingItems
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getItemsByCategory: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mendapatkan statistik dashboard.
     */
    public function getDashboardStats()
    {
        \Log::info('getDashboardStats: Request received');
        try {
            $stats = [
                'total_incoming_items' => IncomingItem::count(),
                'total_outgoing_items' => OutgoingItem::count(),
                'total_stock' => IncomingItem::sum('jumlah_barang'),
                'categories' => IncomingItem::distinct('kategori_barang')->count('kategori_barang'),
                'recent_incoming' => IncomingItem::orderBy('tanggal_masuk_barang', 'desc')->take(5)->get(),
                'recent_outgoing' => OutgoingItem::orderBy('tanggal_keluar_barang', 'desc')->take(5)->get(),
            ];
            // Jika Anda ingin tetap menampilkan statistik stok rendah/habis, Anda bisa menghitungnya secara manual
            // tanpa bergantung pada kolom status_barang:
            $stats['low_stock_items'] = IncomingItem::where('jumlah_barang', '>', 0)->where('jumlah_barang', '<', 10)->count();
            $stats['empty_stock_items'] = IncomingItem::where('jumlah_barang', '=', 0)->count();

            \Log::info('getDashboardStats: Stats calculated', ['stats' => $stats]);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getDashboardStats: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil statistik: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mendapatkan data dashboard admin dengan filter tanggal.
     */
    public function getAdminDashboardData(Request $request)
    {
        \Log::info('getAdminDashboardData: Request received', ['date' => $request->get('date')]);
        
        try {
            $selectedDate = $request->get('date', now()->format('Y-m-d'));
            
            // Parse tanggal yang dipilih
            $date = Carbon::parse($selectedDate);
            $startOfDay = $date->startOfDay();
            $endOfDay = $date->copy()->endOfDay();
            
            \Log::info('getAdminDashboardData: Date range', [
                'start' => $startOfDay->format('Y-m-d H:i:s'),
                'end' => $endOfDay->format('Y-m-d H:i:s')
            ]);

            // Hitung statistik berdasarkan tanggal yang dipilih
            $totalIncoming = IncomingItem::whereBetween('tanggal_masuk_barang', [$startOfDay, $endOfDay])->count();
            $totalOutgoing = OutgoingItem::whereBetween('tanggal_keluar_barang', [$startOfDay, $endOfDay])->count();
            $totalStock = IncomingItem::sum('jumlah_barang'); // Total stok keseluruhan
            $lowStockItems = IncomingItem::where('jumlah_barang', '>', 0)->where('jumlah_barang', '<', 10)->count();
            
            // Data untuk grafik (7 hari terakhir dari tanggal yang dipilih)
            $chartData = [];
            for ($i = 6; $i >= 0; $i--) {
                $chartDate = $date->copy()->subDays($i);
                $dayStart = $chartDate->copy()->startOfDay();
                $dayEnd = $chartDate->copy()->endOfDay();
                
                $chartData[] = [
                    'date' => $chartDate->format('Y-m-d'),
                    'incoming' => IncomingItem::whereBetween('tanggal_masuk_barang', [$dayStart, $dayEnd])->count(),
                    'outgoing' => OutgoingItem::whereBetween('tanggal_keluar_barang', [$dayStart, $dayEnd])->count()
                ];
            }

            $stats = [
                'date' => $selectedDate,
                'total_incoming_today' => $totalIncoming,
                'total_outgoing_today' => $totalOutgoing,
                'total_stock' => $totalStock,
                'low_stock_items' => $lowStockItems,
                'chart_data' => $chartData,
                'recent_incoming' => IncomingItem::whereBetween('tanggal_masuk_barang', [$startOfDay, $endOfDay])
                    ->with(['producer', 'category'])
                    ->orderBy('tanggal_masuk_barang', 'desc')
                    ->take(5)
                    ->get(),
                'recent_outgoing' => OutgoingItem::whereBetween('tanggal_keluar_barang', [$startOfDay, $endOfDay])
                    ->with(['producer', 'category'])
                    ->orderBy('tanggal_keluar_barang', 'desc')
                    ->take(5)
                    ->get()
            ];

            \Log::info('getAdminDashboardData: Stats calculated', ['stats' => $stats]);

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in getAdminDashboardData: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data dashboard: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menetapkan lokasi secara otomatis untuk barang tanpa lokasi.
     */
    public function autoAssignLocations()
    {
        \Log::info('autoAssignLocations: Request received');
        try {
            $itemsWithoutLocation = IncomingItem::where(function($query) {
                                                $query->whereNull('lokasi_rak_barang')
                                                      ->orWhere('lokasi_rak_barang', '');
                                            })
                                            ->where('jumlah_barang', '>', 0) // Hanya barang dengan stok > 0 yang perlu lokasi
                                            ->get();
            \Log::info('autoAssignLocations: Items without location found', ['count' => $itemsWithoutLocation->count()]);


            if ($itemsWithoutLocation->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Tidak ada barang yang memerlukan penempatan lokasi otomatis.',
                    'assigned_count' => 0
                ]);
            }

            $assignedCount = 0;
            // Dapatkan lokasi yang sudah ditempati oleh barang dengan jumlah > 0
            $occupiedLocations = IncomingItem::whereNotNull('lokasi_rak_barang')
                                           ->where('lokasi_rak_barang', '!=', '')
                                           ->where('jumlah_barang', '>', 0) // Hanya pertimbangkan rak dengan item aktif
                                           ->pluck('lokasi_rak_barang')
                                           ->toArray();
            \Log::info('autoAssignLocations: Currently occupied locations', ['locations' => $occupiedLocations]);


            foreach ($itemsWithoutLocation as $item) {
                $location = $this->findAvailableLocation($occupiedLocations);
                if ($location) {
                    $item->lokasi_rak_barang = $location;
                    $item->save();
                    $occupiedLocations[] = $location; // Tambahkan lokasi yang baru ditempati ke daftar
                    $assignedCount++;
                    \Log::info('autoAssignLocations: Assigned location to item', ['item_id' => $item->id, 'location' => $location]);
                } else {
                    \Log::warning('autoAssignLocations: No available location found for item', ['item_id' => $item->id]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Berhasil menetapkan lokasi untuk {$assignedCount} barang.",
                'assigned_count' => $assignedCount,
                'remaining_without_location' => $itemsWithoutLocation->count() - $assignedCount
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in autoAssignLocations: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menetapkan lokasi otomatis: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengimpor barang dari CSV.
     */
    public function importFromCSV(Request $request)
    {
        \Log::info('importFromCSV: Request received', ['file_name' => $request->file('csv_file') ? $request->file('csv_file')->getClientOriginalName() : 'no file', 'type' => $request->get('type')]);

        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
            'has_header' => 'boolean',
            'type' => 'required|in:incoming,outgoing',
        ]);

        if ($validator->fails()) {
            \Log::warning('importFromCSV: Validation failed', ['errors' => $validator->errors()]);
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
            \Log::info('importFromCSV: DB Transaction started');


            foreach ($records as $index => $record) {
                try {
                    if ($type === 'incoming') {
                        $this->importIncomingItem($record, $index, $hasHeader);
                    } else {
                        $this->importOutgoingItem($record, $index, $hasHeader);
                    }
                    $importedCount++;
                    \Log::info('importFromCSV: Record imported successfully', ['row_index' => $index, 'type' => $type]);
                } catch (\Exception $e) {
                    $errors[] = "Baris " . ($index + ($hasHeader ? 2 : 1)) . ": " . $e->getMessage();
                    \Log::warning('importFromCSV: Error importing record', ['row_index' => $index, 'error' => $e->getMessage()]);
                }
            }

            DB::commit();
            \Log::info('importFromCSV: DB Transaction committed');

            return response()->json([
                'success' => true,
                'message' => "Berhasil mengimpor {$importedCount} item.",
                'imported_count' => $importedCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in importFromCSV: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengimpor data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mengekspor barang ke CSV.
     */
    public function exportToCSV(Request $request)
    {
        \Log::info('exportToCSV: Request received', ['type' => $request->get('type'), 'format' => $request->get('format')]);
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
                    // Header CSV baru, tambahkan 'Foto Barang', 'Pembayaran Transaksi', 'Nota Transaksi'
                    fputcsv($file, ['ID', 'Nama Barang', 'Kategori', 'Jumlah', 'Tanggal Masuk', 'Lokasi Rak', 'Nama Produsen', 'Metode Bayar', 'Pembayaran Transaksi', 'Nota Transaksi', 'Foto Barang']);
                    foreach ($items as $item) {
                        fputcsv($file, [
                            $item->id,
                            $item->nama_barang,
                            $item->kategori_barang,
                            $item->jumlah_barang,
                            $item->tanggal_masuk_barang,
                            $item->lokasi_rak_barang,
                            $item->nama_produsen,
                            $item->metode_bayar,
                            $item->pembayaran_transaksi, // Add pembayaran_transaksi path
                            $item->nota_transaksi, // Add nota_transaksi path
                            $item->foto_barang, // Add foto_barang path
                        ]);
                    }
                    fclose($file);
                };
            } else {
                $items = OutgoingItem::all();
                $callback = function() use ($items) {
                    $file = fopen('php://output', 'w');
                    // Header CSV baru, tambahkan 'Foto Barang', 'Pembayaran Transaksi', 'Nota Transaksi'
                    fputcsv($file, ['ID', 'Nama Barang', 'Kategori', 'Jumlah', 'Tanggal Keluar', 'Tujuan Distribusi', 'Lokasi Rak', 'Nama Produsen', 'Metode Bayar', 'Pembayaran Transaksi', 'Nota Transaksi', 'Foto Barang']);
                    foreach ($items as $item) {
                        fputcsv($file, [
                            $item->id,
                            $item->nama_barang,
                            $item->kategori_barang,
                            $item->jumlah_barang,
                            $item->tanggal_keluar_barang,
                            $item->tujuan_distribusi,
                            $item->lokasi_rak_barang,
                            $item->nama_produsen,
                            $item->metode_bayar,
                            $item->pembayaran_transaksi, // Add pembayaran_transaksi path
                            $item->nota_transaksi, // Add nota_transaksi path
                            $item->foto_barang, // Add foto_barang path
                        ]);
                    }
                    fclose($file);
                };
            }
            \Log::info('exportToCSV: Export stream initiated', ['filename' => $filename]);

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            \Log::error('Error in exportToCSV: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengekspor data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Membuat barcode untuk item.
     */
    public function generateBarcode(Request $request, $id)
    {
        \Log::info('generateBarcode: Request received', ['item_id' => $id]);
        try {
            $item = IncomingItem::findOrFail($id);
            
            $barcodeData = [
                'id' => $item->id,
                'name' => $item->nama_barang,
                'code' => 'ITM' . str_pad($item->id, 6, '0', STR_PAD_LEFT),
                'category' => $item->kategori_barang,
                'location' => $item->lokasi_rak_barang,
            ];
            \Log::info('generateBarcode: Barcode data generated', ['barcode_data' => $barcodeData]);

            return response()->json([
                'success' => true,
                'message' => 'Barcode berhasil dibuat.',
                'data' => $barcodeData
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in generateBarcode: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat barcode: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Membuat QR Code untuk item.
     */
    public function generateQRCode(Request $request, $id)
    {
        \Log::info('generateQRCode: Request received', ['item_id' => $id]);
        try {
            $item = IncomingItem::findOrFail($id);
            
            $qrData = [
                'id' => $item->id,
                'name' => $item->nama_barang,
                'category' => $item->kategori_barang,
                'quantity' => $item->jumlah_barang,
                'location' => $item->lokasi_rak_barang,
                'date_added' => $item->tanggal_masuk_barang->format('Y-m-d'),
                'url' => url('/staff/items?item=' . $item->id)
            ];
            \Log::info('generateQRCode: QR Code data generated', ['qr_data' => $qrData]);

            return response()->json([
                'success' => true,
                'message' => 'QR Code berhasil dibuat.',
                'data' => $qrData,
                'qr_string' => json_encode($qrData)
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in generateQRCode: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat QR Code: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menduplikasi item yang sudah ada.
     */
    public function duplicateItem(Request $request, $id)
    {
        \Log::info('duplicateItem: Request received', ['item_id' => $id]);
        try {
            $originalItem = IncomingItem::findOrFail($id);
            \Log::info('duplicateItem: Original item found', ['item_id' => $originalItem->id, 'data' => $originalItem->toArray()]);

            $duplicatedItem = IncomingItem::create([
                'nama_barang' => $originalItem->nama_barang . ' (Copy)',
                'kategori_barang' => $originalItem->kategori_barang,
                'jumlah_barang' => $originalItem->jumlah_barang,
                'tanggal_masuk_barang' => now(),
                'lokasi_rak_barang' => null, // Setel ke null agar penempatan otomatis bisa menempatkannya
                'nama_produsen' => $originalItem->nama_produsen, // Kolom baru
                'metode_bayar' => $originalItem->metode_bayar, // Kolom baru
                'pembayaran_transaksi' => $originalItem->pembayaran_transaksi, // Copy pembayaran_transaksi path
                'nota_transaksi' => $originalItem->nota_transaksi, // Copy nota_transaksi path
                'foto_barang' => $originalItem->foto_barang, // Copy foto_barang
            ]);
            \Log::info('duplicateItem: Item duplicated successfully', ['new_item_id' => $duplicatedItem->id, 'data' => $duplicatedItem->toArray()]);

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil diduplikasi.',
                'data' => $duplicatedItem
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in duplicateItem: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menduplikasi barang: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mendapatkan laporan inventaris.
     */
    public function getInventoryReport(Request $request)
    {
        \Log::info('getInventoryReport: Request received', ['filters' => $request->all()]);
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
            \Log::info('getInventoryReport: Report data fetched', ['total_items' => $items->count(), 'total_quantity' => $totalValue]);


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
                'items_by_location' => $items->whereNotNull('lokasi_rak_barang')->groupBy('lokasi_rak_barang')
            ];
            // Hitung ulang statistik stok rendah/habis jika diperlukan di laporan
            $report['low_stock_items'] = $items->where('jumlah_barang', '>', 0)->where('jumlah_barang', '<', 10)->values();
            $report['out_of_stock_items'] = $items->where('jumlah_barang', '=', 0)->values();


            return response()->json([
                'success' => true,
                'data' => $report
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getInventoryReport: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mendapatkan riwayat pergerakan untuk suatu item.
     */
    public function getItemMovementHistory($id)
    {
        \Log::info('getItemMovementHistory: Request received', ['item_id' => $id]);
        try {
            $incomingItem = IncomingItem::findOrFail($id);
            \Log::info('getItemMovementHistory: Incoming item found', ['item_id' => $incomingItem->id]);
            
            // Mendapatkan barang keluar dengan nama dan kategori yang sama
            $outgoingItems = OutgoingItem::where('nama_barang', $incomingItem->nama_barang)
                                       ->where('kategori_barang', $incomingItem->kategori_barang)
                                       ->orderBy('tanggal_keluar_barang', 'desc')
                                       ->get();
            \Log::info('getItemMovementHistory: Outgoing items found', ['count' => $outgoingItems->count()]);


            $movements = [];
            
            // Tambahkan catatan masuk
            $movements[] = [
                'type' => 'incoming',
                'date' => $incomingItem->tanggal_masuk_barang,
                'quantity' => $incomingItem->jumlah_barang,
                'location' => $incomingItem->lokasi_rak_barang,
                'nama_produsen' => $incomingItem->nama_produsen,
                'metode_bayar' => $incomingItem->metode_bayar,
                'pembayaran_transaksi' => $incomingItem->pembayaran_transaksi, // Add pembayaran_transaksi
                'nota_transaksi' => $incomingItem->nota_transaksi, // Add nota_transaksi
                'foto_barang' => $incomingItem->foto_barang,
                'description' => 'Barang masuk'
            ];

            // Tambahkan catatan keluar
            foreach ($outgoingItems as $outgoing) {
                $movements[] = [
                    'type' => 'outgoing',
                    'date' => $outgoing->tanggal_keluar_barang,
                    'quantity' => $outgoing->jumlah_barang,
                    'destination' => $outgoing->tujuan_distribusi,
                    'location' => $outgoing->lokasi_rak_barang,
                    'nama_produsen' => $outgoing->nama_produsen,
                    'metode_bayar' => $outgoing->metode_bayar,
                    'pembayaran_transaksi' => $outgoing->pembayaran_transaksi, // Add pembayaran_transaksi
                    'nota_transaksi' => $outgoing->nota_transaksi, // Add nota_transaksi
                    'foto_barang' => $outgoing->foto_barang,
                    'description' => 'Barang keluar ke ' . ($outgoing->tujuan_distribusi ?? $outgoing->nama_produsen)
                ];
            }

            // Urutkan berdasarkan tanggal menurun
            usort($movements, function($a, $b) {
                return strtotime($b['date']) - strtotime($a['date']);
            });
            \Log::info('getItemMovementHistory: Movement history generated', ['movements_count' => count($movements)]);


            return response()->json([
                'success' => true,
                'data' => [
                    'item' => $incomingItem,
                    'movements' => $movements
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getItemMovementHistory: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil riwayat pergerakan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Memperbarui item secara massal.
     */
    public function bulkUpdate(Request $request)
    {
        \Log::info('bulkUpdate: Request received', ['request_data' => $request->all()]);
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.id' => 'required|exists:incoming_items,id',
            'action' => 'required|in:update_category,update_location,delete',
            'category' => 'required_if:action,update_category|string|max:100',
            'location' => 'required_if:action,update_location|string|regex:/^R[1-8]-[1-4]-[1-6]$/',
        ]);

        if ($validator->fails()) {
            \Log::warning('bulkUpdate: Validation failed', ['errors' => $validator->errors()]);
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
            \Log::info('bulkUpdate: DB Transaction started');


            foreach ($items as $itemData) {
                $item = IncomingItem::find($itemData['id']);
                if (!$item) {
                    \Log::warning('bulkUpdate: Item not found for bulk update', ['item_id' => $itemData['id']]);
                    continue;
                }

                switch ($action) {
                    case 'update_category':
                        $item->kategori_barang = $request->get('category');
                        $item->save();
                        $updatedCount++;
                        \Log::info('bulkUpdate: Item category updated', ['item_id' => $item->id, 'new_category' => $request->get('category')]);
                        break;

                    case 'update_location':
                        $location = $request->get('location');
                        // Cek apakah lokasi sudah ditempati oleh barang lain
                        $existingItem = IncomingItem::where('lokasi_rak_barang', $location)
                                                  ->where('id', '!=', $item->id)
                                                  ->where('jumlah_barang', '>', 0) // Hanya jika ada barang aktif di sana
                                                  ->first();
                        if (!$existingItem) {
                            $item->lokasi_rak_barang = $location;
                            $item->save();
                            $updatedCount++;
                            \Log::info('bulkUpdate: Item location updated', ['item_id' => $item->id, 'new_location' => $location]);
                        } else {
                            \Log::warning('bulkUpdate: Failed to update location, rack occupied', ['item_id' => $item->id, 'new_location' => $location, 'occupying_item' => $existingItem->nama_barang]);
                            // Jika lokasi ditempati, bisa tambahkan pesan error spesifik
                            // atau lewati item ini
                            // Contoh: $errors[] = "Lokasi {$location} sudah ditempati oleh {$existingItem->nama_barang}.";
                        }
                        break;

                    case 'delete':
                        // Delete associated photos if exists
                        if ($item->foto_barang) {
                            Storage::disk('public')->delete($item->foto_barang);
                            \Log::info('bulkUpdate: Associated image deleted during bulk delete', ['path' => $item->foto_barang]);
                        }
                        if ($item->pembayaran_transaksi) {
                            Storage::disk('public')->delete($item->pembayaran_transaksi);
                            \Log::info('bulkUpdate: Associated pembayaran_transaksi deleted during bulk delete', ['path' => $item->pembayaran_transaksi]);
                        }
                        if ($item->nota_transaksi) {
                            Storage::disk('public')->delete($item->nota_transaksi);
                            \Log::info('bulkUpdate: Associated nota_transaksi deleted during bulk delete', ['path' => $item->nota_transaksi]);
                        }
                        $item->delete();
                        $updatedCount++;
                        \Log::info('bulkUpdate: Item deleted', ['item_id' => $item->id]);
                        break;
                }
            }

            DB::commit();
            \Log::info('bulkUpdate: DB Transaction committed successfully');

            return response()->json([
                'success' => true,
                'message' => "Berhasil memproses {$updatedCount} item.",
                'updated_count' => $updatedCount
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in bulkUpdate: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses bulk update: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mendapatkan lokasi yang tersedia.
     */
    public function getAvailableLocations()
    {
        \Log::info('getAvailableLocations: Request received');
        try {
            $occupiedLocations = IncomingItem::whereNotNull('lokasi_rak_barang')
                                           ->where('lokasi_rak_barang', '!=', '')
                                           ->where('jumlah_barang', '>', 0) // Hanya pertimbangkan rak dengan item aktif
                                           ->pluck('lokasi_rak_barang')
                                           ->toArray();
            \Log::info('getAvailableLocations: Occupied locations fetched', ['locations' => $occupiedLocations]);


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
            \Log::info('getAvailableLocations: All locations generated', ['total_locations' => count($allLocations)]);


            return response()->json([
                'success' => true,
                'data' => $allLocations
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getAvailableLocations: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data lokasi: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============ METODE PEMBANTU PRIBADI ============

    /**
     * Mencari lokasi yang tersedia untuk penempatan item.
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
     * Mengimpor barang masuk dari catatan CSV.
     */
    private function importIncomingItem($record, $index, $hasHeader)
    {
        \Log::info('importIncomingItem (private): Processing record', ['record' => $record, 'index' => $index]);
        $data = array_values($record);
        
        // Sesuaikan indeks data dengan kolom CSV yang baru
        // Format CSV: nama_barang,kategori_barang,jumlah_barang,tanggal_masuk_barang,lokasi_rak_barang,nama_produsen,metode_bayar,pembayaran_transaksi,nota_transaksi,foto_barang
        $csvData = [
            'nama_barang' => $data[0] ?? '',
            'kategori_barang' => $data[1] ?? '',
            'jumlah_barang' => $data[2] ?? 0,
            'tanggal_masuk_barang' => $data[3] ?? '',
            'lokasi_rak_barang' => $data[4] ?? null,
            'nama_produsen' => $data[5] ?? null,
            'metode_bayar' => $data[6] ?? null,
            'pembayaran_transaksi' => $data[7] ?? null, // Changed to path string
            'nota_transaksi' => $data[8] ?? null, // Changed to path string
            'foto_barang' => $data[9] ?? null,
        ];

        // Validasi data impor
        $validator = Validator::make($csvData, [
            'nama_barang' => 'required|string|max:255',
            'kategori_barang' => 'required|string|max:100',
            'jumlah_barang' => 'required|integer|min:1',
            'tanggal_masuk_barang' => 'required|date',
            'lokasi_rak_barang' => 'nullable|string|regex:/^R[1-8]-[1-4]-[1-6]$/',
            'nama_produsen' => 'nullable|string|max:255',
            'metode_bayar' => 'nullable|string|max:50',
            'pembayaran_transaksi' => 'nullable|string|max:255', // Validate as string path for CSV import
            'nota_transaksi' => 'nullable|string|max:255', // Validate as string path for CSV import
            'foto_barang' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new \Exception("Validasi gagal pada baris " . ($index + ($hasHeader ? 2 : 1)) . ": " . $validator->errors()->first());
        }

        // Logika untuk menangani lokasi rak barang saat impor
        if (!empty($csvData['lokasi_rak_barang'])) {
            $lokasi_rak_barang = $csvData['lokasi_rak_barang'];
            // Cek apakah ada barang lain (dengan nama berbeda) di lokasi rak ini
            $existingDifferentItemOnRack = IncomingItem::where('lokasi_rak_barang', $lokasi_rak_barang)
                                                        ->where('nama_barang', '!=', $csvData['nama_barang'])
                                                        ->where('jumlah_barang', '>', 0)
                                                        ->first();
            if ($existingDifferentItemOnRack) {
                throw new \Exception('Lokasi rak ' . $lokasi_rak_barang . ' sudah ditempati oleh barang lain (' . $existingDifferentItemOnRack->nama_barang . ').');
            }
            
            // Cek apakah ada barang yang sama di lokasi rak ini
            $existingSameItemOnRack = IncomingItem::where('lokasi_rak_barang', $lokasi_rak_barang)
                                                    ->where('nama_barang', $csvData['nama_barang'])
                                                    ->first();
            if ($existingSameItemOnRack) {
                // Jika barang yang sama sudah ada, perbarui jumlahnya
                $existingSameItemOnRack->jumlah_barang += $csvData['jumlah_barang'];
                // Update foto, pembayaran_transaksi, dan nota_transaksi juga jika ada yang baru diunggah dari CSV
                if ($csvData['foto_barang']) {
                    $existingSameItemOnRack->foto_barang = $csvData['foto_barang'];
                }
                if ($csvData['pembayaran_transaksi']) {
                    $existingSameItemOnRack->pembayaran_transaksi = $csvData['pembayaran_transaksi'];
                }
                if ($csvData['nota_transaksi']) {
                    $existingSameItemOnRack->nota_transaksi = $csvData['nota_transaksi'];
                }
                $existingSameItemOnRack->save();
                \Log::info('importIncomingItem (private): Updated existing item on rack', ['item_id' => $existingSameItemOnRack->id, 'final_qty' => $existingSameItemOnRack->jumlah_barang]);
                return; // Barang diperbarui, tidak perlu membuat yang baru
            }
        }

        IncomingItem::create([
            'nama_barang' => $csvData['nama_barang'],
            'kategori_barang' => $csvData['kategori_barang'],
            'jumlah_barang' => $csvData['jumlah_barang'],
            'tanggal_masuk_barang' => $csvData['tanggal_masuk_barang'],
            'lokasi_rak_barang' => $csvData['lokasi_rak_barang'],
            'nama_produsen' => $csvData['nama_produsen'],
            'metode_bayar' => $csvData['metode_bayar'],
            'pembayaran_transaksi' => $csvData['pembayaran_transaksi'], // Save pembayaran_transaksi from CSV
            'nota_transaksi' => $csvData['nota_transaksi'], // Save nota_transaksi from CSV
            'foto_barang' => $csvData['foto_barang'],
        ]);
        \Log::info('importIncomingItem (private): New item created', ['nama_barang' => $csvData['nama_barang'], 'jumlah_barang' => $csvData['jumlah_barang']]);
    }




    /**
     * Mengimpor barang keluar dari catatan CSV.
     */
    private function importOutgoingItem($record, $index, $hasHeader)
    {
        \Log::info('importOutgoingItem (private): Processing record', ['record' => $record, 'index' => $index]);
        $data = array_values($record);
        
        // Sesuaikan indeks data dengan kolom CSV yang baru
        // Format CSV: nama_barang,kategori_barang,jumlah_barang,tanggal_keluar_barang,tujuan_distribusi,lokasi_rak_barang,nama_produsen,metode_bayar,pembayaran_transaksi,nota_transaksi,foto_barang
        $csvData = [
            'nama_barang' => $data[0] ?? '',
            'kategori_barang' => $data[1] ?? '',
            'jumlah_barang' => $data[2] ?? 0,
            'tanggal_keluar_barang' => $data[3] ?? '',
            'tujuan_distribusi' => $data[4] ?? '',
            'lokasi_rak_barang' => $data[5] ?? null,
            'nama_produsen' => $data[6] ?? null,
            'metode_bayar' => $data[7] ?? null,
            'pembayaran_transaksi' => $data[8] ?? null, // Changed to path string
            'nota_transaksi' => $data[9] ?? null, // Changed to path string
            'foto_barang' => $data[10] ?? null,
        ];

        // Validasi data impor
        $validator = Validator::make($csvData, [
            'nama_barang' => 'required|string|max:255',
            'kategori_barang' => 'required|string|max:100',
            'jumlah_barang' => 'required|integer|min:1',
            'tanggal_keluar_barang' => 'required|date',
            'tujuan_distribusi' => 'required|string|max:255',
            'lokasi_rak_barang' => 'nullable|string|regex:/^R[1-8]-[1-4]-[1-6]$/',
            'nama_produsen' => 'nullable|string|max:255',
            'metode_bayar' => 'nullable|string|max:50',
            'pembayaran_transaksi' => 'nullable|string|max:255', // Validate as string path for CSV import
            'nota_transaksi' => 'nullable|string|max:255', // Validate as string path for CSV import
            'foto_barang' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new \Exception("Validasi gagal pada baris " . ($index + ($hasHeader ? 2 : 1)) . ": " . $validator->errors()->first());
        }

        // Cek ketersediaan stok berdasarkan nama barang, kategori, dan lokasi rak (jika disediakan)
        $incomingItemQuery = IncomingItem::where('nama_barang', $csvData['nama_barang'])
                                         ->where('kategori_barang', $csvData['kategori_barang']);

        if (!empty($csvData['lokasi_rak_barang'])) {
            $incomingItemQuery->where('lokasi_rak_barang', $csvData['lokasi_rak_barang']);
        }

        $incomingItem = $incomingItemQuery->first();
        \Log::info('importOutgoingItem (private): Checking stock for', ['nama_barang' => $csvData['nama_barang'], 'lokasi_rak_barang' => $csvData['lokasi_rak_barang'], 'incoming_item_found' => $incomingItem ? $incomingItem->toArray() : 'not found']);


        if (!$incomingItem) {
            throw new \Exception('Barang ' . $csvData['nama_barang'] . ' tidak ditemukan dalam stok atau di lokasi rak ' . ($csvData['lokasi_rak_barang'] ?? 'mana pun') . '.');
        }

        if ($incomingItem->jumlah_barang < $csvData['jumlah_barang']) {
            throw new \Exception("Stok barang {$csvData['nama_barang']} tidak mencukupi. Tersedia: {$incomingItem->jumlah_barang}, diminta: {$csvData['jumlah_barang']}");
        }

        DB::transaction(function () use ($csvData, $incomingItem) {
            OutgoingItem::create([
                'nama_barang' => $csvData['nama_barang'],
                'kategori_barang' => $csvData['kategori_barang'],
                'jumlah_barang' => $csvData['jumlah_barang'],
                'tanggal_keluar_barang' => $csvData['tanggal_keluar_barang'],
                'tujuan_distribusi' => $csvData['tujuan_distribusi'],
                'lokasi_rak_barang' => $csvData['lokasi_rak_barang'],
                'nama_produsen' => $csvData['nama_produsen'],
                'metode_bayar' => $csvData['metode_bayar'],
                'pembayaran_transaksi' => $csvData['pembayaran_transaksi'], // Save pembayaran_transaksi from CSV
                'nota_transaksi' => $csvData['nota_transaksi'], // Save nota_transaksi from CSV
                'foto_barang' => $csvData['foto_barang'],
            ]);
            \Log::info('importOutgoingItem (private): Outgoing item created during import', ['nama_barang' => $csvData['nama_barang'], 'jumlah' => $csvData['jumlah_barang']]);


            $incomingItem->jumlah_barang -= $csvData['jumlah_barang'];
            $incomingItem->save();
            \Log::info('importOutgoingItem (private): Incoming item stock reduced during import', ['item_id' => $incomingItem->id, 'final_qty' => $incomingItem->jumlah_barang]);


            // Jika stok menjadi 0 dan lokasi rak tidak null, kosongkan lokasi rak
            if ($incomingItem->jumlah_barang == 0 && $incomingItem->lokasi_rak_barang) {
                $incomingItem->lokasi_rak_barang = null;
                $incomingItem->save();
                \Log::info('importOutgoingItem (private): Incoming item rack location cleared due to zero stock during import', ['item_id' => $incomingItem->id]);
            }
        });
    }

    public function getPendingVerificationItems()
    {
        try {
            \Log::info('getPendingVerificationItems: Fetching pending verification items');
            
            $pendingItems = VerificationItem::with('producer')
                                         ->where('is_verified', false)
                                         ->get()
                                         ->map(function ($item) {
                                             return [
                                                 'id' => $item->id,
                                                 'nama_barang' => $item->nama_barang,
                                                 'kategori_barang' => $item->kategori_barang,
                                                 'jumlah_barang' => $item->jumlah_barang,
                                                 'nama_produsen' => $item->producer ? $item->producer->nama_produsen : null,
                                                 'producer_id' => $item->producer_id
                                             ];
                                         });

            \Log::info('getPendingVerificationItems: Found items', ['count' => $pendingItems->count()]);

            return response()->json([
                'success' => true,
                'data' => $pendingItems
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getPendingVerificationItems: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark order as finished packing
     * For Admin role in Pengelolaan Barang menu
     */
    public function markOrderAsFinishedPacking(Request $request, $orderId)
    {
        try {
            // Check if user is admin
            $user = Auth::user();
            if ($user->role !== 'Admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Admin access required.'
                ], 403);
            }

            // Import Order model if not already imported
            $order = \App\Models\Order::findOrFail($orderId);

            // Validate current order status - only allow from confirmed status
            if ($order->order_status !== 'confirmed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order harus dalam status confirmed untuk bisa ditandai selesai dikemas'
                ], 422);
            }

            // Update order status to processing (barang sudah selesai dikemas)
            $order->order_status = 'processing';
            
            // Add notes about packing completion
            $packingNote = '[' . now()->format('Y-m-d H:i:s') . '] Barang sudah selesai dikemas oleh Admin';
            $order->notes = ($order->notes ? $order->notes . '\n' : '') . $packingNote;

            $order->save();

            return response()->json([
                'success' => true,
                'message' => 'Order berhasil ditandai sebagai selesai dikemas',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'previous_status' => 'confirmed',
                    'current_status' => $order->order_status,
                    'updated_at' => $order->updated_at,
                    'notes' => $order->notes
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error in markOrderAsFinishedPacking: ' . $e->getMessage(), [
                'order_id' => $orderId,
                'user_id' => Auth::id(),
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai order selesai dikemas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of incoming items for pergantian barang selection
     */
    public function getIncomingItemsList(Request $request)
    {
        try {
            $search = $request->get('search', '');
            
            $query = IncomingItem::with(['category', 'producer'])
                ->select([
                    'id',
                    'nama_barang',
                    'kategori_barang', 
                    'category_id',
                    'producer_id',
                    'jumlah_barang',
                    'tanggal_masuk_barang',
                    'lokasi_rak_barang',
                    'foto_barang'
                ])
                ->where('jumlah_barang', '>', 0); // Only items with available stock
            
            // Add search functionality
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('nama_barang', 'LIKE', "%{$search}%")
                      ->orWhere('kategori_barang', 'LIKE', "%{$search}%");
                });
            }
            
            $items = $query->orderBy('nama_barang', 'asc')
                          ->limit(50) // Limit results for performance
                          ->get();
            
            $formattedItems = $items->map(function($item) {
                return [
                    'id' => $item->id,
                    'nama_barang' => $item->nama_barang,
                    'kategori_barang' => $item->kategori_barang,
                    'category_id' => $item->category_id,
                    'producer_id' => $item->producer_id,
                    'nama_produsen' => $item->producer ? $item->producer->nama_produsen : null,
                    'jumlah_barang' => $item->jumlah_barang,
                    'tanggal_masuk_barang' => $item->tanggal_masuk_barang,
                    'lokasi_rak_barang' => $item->lokasi_rak_barang,
                    'foto_barang' => $item->foto_barang,
                    'foto_url' => $item->foto_barang ? url('storage/' . $item->foto_barang) : null,
                    'display_name' => $item->nama_barang . ' - ' . $item->kategori_barang . ' (Stock: ' . $item->jumlah_barang . ')'
                ];
            });
            
            return response()->json([
                'success' => true,
                'data' => $formattedItems
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error in getIncomingItemsList: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil daftar barang.'
            ], 500);
        }
    }

    /**
     * Store pergantian barang (direct return without order)
     */
    public function storePergantianBarang(Request $request)
    {
        try {
            // Validate the request data for pergantian barang
            $validator = Validator::make($request->all(), [
                'nama_barang' => 'required|string|max:255',
                'kategori_barang' => 'required|string|max:255', 
                'jumlah_barang' => 'required|integer|min:1|max:99999',
                'nama_produsen' => 'nullable|string|max:255',
                'alasan_pengembalian' => 'required|string|max:1000',
                'incoming_item_id' => 'nullable|integer|exists:incoming_items,id',
                'foto_bukti' => 'nullable|image|mimes:jpeg,jpg,png|max:2048' // max 2MB
            ], [
                'nama_barang.required' => 'Nama barang wajib diisi.',
                'kategori_barang.required' => 'Kategori barang wajib diisi.',
                'jumlah_barang.required' => 'Jumlah barang wajib diisi.',
                'jumlah_barang.min' => 'Jumlah barang minimal 1.',
                'jumlah_barang.max' => 'Jumlah barang maksimal 99999.',
                'alasan_pengembalian.required' => 'Alasan pergantian wajib diisi.',
                'alasan_pengembalian.max' => 'Alasan pergantian maksimal 1000 karakter.',
                'incoming_item_id.exists' => 'Barang yang dipilih tidak valid.',
                'foto_bukti.image' => 'File foto bukti harus berupa gambar.',
                'foto_bukti.mimes' => 'File foto bukti harus berformat: jpeg, jpg, atau png.',
                'foto_bukti.max' => 'Ukuran file foto bukti maksimal 2MB.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal.',
                    'errors' => $validator->errors()
                ], 422);
            }

            $fotoPath = null;

            // Handle file upload if foto_bukti is provided
            if ($request->hasFile('foto_bukti')) {
                $file = $request->file('foto_bukti');
                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                
                // Store file in public/storage/return_items folder
                $fotoPath = $file->storeAs('return_items', $fileName, 'public');
                \Log::info('storePergantianBarang: Foto bukti uploaded', ['path' => $fotoPath]);
            }

            // Validate stock if incoming_item_id is provided
            if ($request->incoming_item_id) {
                $incomingItem = IncomingItem::find($request->incoming_item_id);
                if (!$incomingItem) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Barang yang dipilih tidak ditemukan.'
                    ], 404);
                }
                
                if ($request->jumlah_barang > $incomingItem->jumlah_barang) {
                    return response()->json([
                        'success' => false,
                        'message' => "Jumlah barang tidak boleh melebihi stok yang tersedia ({$incomingItem->jumlah_barang} unit)."
                    ], 400);
                }
            }

            // Log data before saving for debugging
            \Log::info('storePergantianBarang: Data sebelum disimpan', [
                'request_data' => $request->except(['foto_bukti']),
                'foto_path' => $fotoPath
            ]);

            // Prepare data for creation - use NULL for non-order returns (pergantian barang)
            $createData = [
                'order_id' => null, // NULL for pergantian barang (not from order)
                'order_item_id' => null, // NULL for pergantian barang (not from order)
                'user_id' => Auth::id(), // Current staff user, can be null
                'nama_barang' => $request->nama_barang,
                'kategori_barang' => $request->kategori_barang,
                'jumlah_barang' => (int) $request->jumlah_barang,
                'nama_produsen' => $request->nama_produsen ?? '',
                'alasan_pengembalian' => $request->alasan_pengembalian,
                'foto_bukti' => $fotoPath,
                'incoming_item_id' => $request->incoming_item_id ? (int) $request->incoming_item_id : null
            ];

            \Log::info('storePergantianBarang: Final create data', [
                'create_data' => $createData
            ]);

            try {
                // Create using Eloquent model (simpler and handles nullable fields better)
                $returnedItem = ReturnedItem::create($createData);
                
                \Log::info('storePergantianBarang: Item berhasil dibuat', [
                    'returned_item_id' => $returnedItem->id
                ]);
                
            } catch (\Exception $createException) {
                \Log::error('storePergantianBarang: Error saat create ReturnedItem', [
                    'error' => $createException->getMessage(),
                    'create_data' => $createData,
                    'trace' => $createException->getTraceAsString()
                ]);
                
                // Check if it's a nullable column issue
                if (str_contains($createException->getMessage(), 'cannot be null') || 
                    str_contains($createException->getMessage(), 'NOT NULL')) {
                    throw new \Exception(
                        'Tabel returned_items memerlukan kolom order_id, order_item_id, dan user_id menjadi nullable. ' .
                        'Silakan jalankan migration yang sudah dibuat: php artisan migrate'
                    );
                }
                
                throw $createException;
            }

            \Log::info('storePergantianBarang: Pergantian barang berhasil disimpan', [
                'returned_item_id' => $returnedItem->id,
                'data' => $returnedItem->toArray()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan pergantian barang berhasil dikirim! Admin akan memproses permintaan Anda.',
                'data' => $returnedItem
            ]);

        } catch (\Exception $e) {
            // Clean up uploaded files if there's an error
            if (isset($fotoPath) && $fotoPath) {
                Storage::disk('public')->delete($fotoPath);
            }

            \Log::error('Error in storePergantianBarang: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data pergantian barang: ' . $e->getMessage()
            ], 500);
        }
    }
}
