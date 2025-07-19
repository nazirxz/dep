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
     * Menampilkan halaman indeks barang (untuk menu Staff Admin Barang).
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
     * Menampilkan halaman pengelolaan barang (untuk menu Staff Admin Pengelolaan Barang).
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
     * Menyimpan barang masuk baru.
     */
    public function storeIncomingItem(Request $request)
    {
        \Log::info('storeIncomingItem: Request received', ['request_data' => $request->all()]);

        $validator = Validator::make($request->all(), [
            'nama_barang' => 'required|string|max:255',
            'kategori_barang' => 'required|string|max:100',
            'jumlah_barang' => 'required|integer|min:1',
            'tanggal_masuk_barang' => 'required|date',
            'lokasi_rak_barang' => 'nullable|string|regex:/^R[1-8]-[1-4]-[1-6]$/',
            'nama_produsen' => 'nullable|string|max:255',
            'metode_bayar' => 'nullable|string|max:50',
            'pembayaran_transaksi' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048', // Changed to image
            'nota_transaksi' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048', // Changed to image
            'foto_barang' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ], [
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'kategori_barang.required' => 'Kategori barang wajib diisi.',
            'jumlah_barang.required' => 'Jumlah barang wajib diisi.',
            'jumlah_barang.min' => 'Jumlah barang minimal 1.',
            'tanggal_masuk_barang.required' => 'Tanggal masuk wajib diisi.',
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
            \Log::warning('storeIncomingItem: Validation failed', ['errors' => $validator->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 422);
        }

        $fotoPath = null;
        if ($request->hasFile('foto_barang')) {
            $fotoPath = $request->file('foto_barang')->store('images', 'public');
            \Log::info('storeIncomingItem: Image uploaded', ['path' => $fotoPath]);
        }

        $pembayaranTransaksiPath = null;
        if ($request->hasFile('pembayaran_transaksi')) {
            $pembayaranTransaksiPath = $request->file('pembayaran_transaksi')->store('transactions', 'public');
            \Log::info('storeIncomingItem: Pembayaran Transaksi uploaded', ['path' => $pembayaranTransaksiPath]);
        }

        $notaTransaksiPath = null;
        if ($request->hasFile('nota_transaksi')) {
            $notaTransaksiPath = $request->file('nota_transaksi')->store('transactions', 'public');
            \Log::info('storeIncomingItem: Nota Transaksi uploaded', ['path' => $notaTransaksiPath]);
        }

        // Logika untuk menangani lokasi rak barang
        if ($request->lokasi_rak_barang) {
            // Cek apakah ada barang lain (dengan nama berbeda) di lokasi rak ini
            $existingDifferentItemOnRack = IncomingItem::where('lokasi_rak_barang', $request->lokasi_rak_barang)
                                                        ->where('nama_barang', '!=', $request->nama_barang)
                                                        ->where('jumlah_barang', '>', 0) // Hanya jika ada barang aktif di sana
                                                        ->first();
            if ($existingDifferentItemOnRack) {
                \Log::warning('storeIncomingItem: Rack occupied by different item', ['location' => $request->lokasi_rak_barang, 'existing_item' => $existingDifferentItemOnRack->nama_barang]);
                return response()->json([
                    'success' => false,
                    'message' => 'Lokasi rak sudah ditempati oleh barang lain (' . $existingDifferentItemOnRack->nama_barang . ').'
                ], 422);
            }
            
            // Cek apakah ada barang yang sama di lokasi rak ini
            $existingSameItemOnRack = IncomingItem::where('lokasi_rak_barang', $request->lokasi_rak_barang)
                                                    ->where('nama_barang', $request->nama_barang)
                                                    ->first();
            if ($existingSameItemOnRack) {
                // Jika barang yang sama sudah ada, perbarui jumlahnya
                \Log::info('storeIncomingItem: Updating quantity for existing item on rack', ['item_id' => $existingSameItemOnRack->id, 'old_qty' => $existingSameItemOnRack->jumlah_barang, 'new_qty_added' => $request->jumlah_barang]);
                $existingSameItemOnRack->jumlah_barang += $request->jumlah_barang;
                // Update foto juga jika ada yang baru diunggah
                if ($fotoPath) {
                    if ($existingSameItemOnRack->foto_barang) {
                        Storage::disk('public')->delete($existingSameItemOnRack->foto_barang);
                    }
                    $existingSameItemOnRack->foto_barang = $fotoPath;
                }
                // Update pembayaran_transaksi dan nota_transaksi juga jika ada yang baru diunggah
                if ($pembayaranTransaksiPath) {
                    if ($existingSameItemOnRack->pembayaran_transaksi) {
                        Storage::disk('public')->delete($existingSameItemOnRack->pembayaran_transaksi);
                    }
                    $existingSameItemOnRack->pembayaran_transaksi = $pembayaranTransaksiPath;
                }
                if ($notaTransaksiPath) {
                    if ($existingSameItemOnRack->nota_transaksi) {
                        Storage::disk('public')->delete($existingSameItemOnRack->nota_transaksi);
                    }
                    $existingSameItemOnRack->nota_transaksi = $notaTransaksiPath;
                }

                $existingSameItemOnRack->save();
                \Log::info('storeIncomingItem: Item quantity updated successfully', ['item_id' => $existingSameItemOnRack->id, 'final_qty' => $existingSameItemOnRack->jumlah_barang]);

                return response()->json([
                    'success' => true,
                    'message' => 'Jumlah barang di lokasi rak yang sama berhasil diperbarui.',
                    'data' => $existingSameItemOnRack
                ]);
            }
        }

        try {
            $incomingItem = IncomingItem::create([
                'nama_barang' => $request->nama_barang,
                'kategori_barang' => $request->kategori_barang,
                'jumlah_barang' => $request->jumlah_barang,
                'tanggal_masuk_barang' => $request->tanggal_masuk_barang,
                'lokasi_rak_barang' => $request->lokasi_rak_barang,
                'nama_produsen' => $request->nama_produsen,
                'metode_bayar' => $request->metode_bayar,
                'pembayaran_transaksi' => $pembayaranTransaksiPath, // Save the image path
                'nota_transaksi' => $notaTransaksiPath, // Save the image path
                'foto_barang' => $fotoPath, // Save the image path
            ]);
            \Log::info('storeIncomingItem: New item created successfully', ['item_id' => $incomingItem->id, 'data' => $incomingItem->toArray()]);

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil ditambahkan.',
                'data' => $incomingItem
            ]);
        } catch (\Exception $e) {
            // If an error occurs after file upload, delete the uploaded files
            if ($fotoPath) {
                Storage::disk('public')->delete($fotoPath);
            }
            if ($pembayaranTransaksiPath) {
                Storage::disk('public')->delete($pembayaranTransaksiPath);
            }
            if ($notaTransaksiPath) {
                Storage::disk('public')->delete($notaTransaksiPath);
            }
            // Log error for debugging
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
            'kategori_barang' => 'required|string|max:100',
            'jumlah_barang' => 'required|integer|min:0',
            'tanggal_masuk_barang' => 'required|date',
            'lokasi_rak_barang' => 'nullable|string|regex:/^R[1-8]-[1-4]-[1-6]$/',
            'nama_produsen' => 'nullable|string|max:255',
            'metode_bayar' => 'nullable|string|max:50',
            'pembayaran_transaksi' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048', // Changed to image
            'nota_transaksi' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,pdf|max:2048', // Changed to image
            'foto_barang' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
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
            $oldLocation = $incomingItem->lokasi_rak_barang;
            $newLocation = $request->lokasi_rak_barang;

            $oldFotoPath = $incomingItem->foto_barang;
            $fotoPathToSave = $oldFotoPath;

            $oldPembayaranTransaksiPath = $incomingItem->pembayaran_transaksi;
            $pembayaranTransaksiPathToSave = $oldPembayaranTransaksiPath;

            $oldNotaTransaksiPath = $incomingItem->nota_transaksi;
            $notaTransaksiPathToSave = $oldNotaTransaksiPath;

            \Log::info('updateIncomingItem: Found item', ['item_id' => $incomingItem->id, 'current_data' => $incomingItem->toArray()]);

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


            // Jika lokasi rak diubah dan lokasi baru tidak null
            if ($newLocation && $newLocation !== $oldLocation) {
                // Cek apakah lokasi baru sudah ditempati oleh barang lain (bukan item yang sedang diupdate)
                $existingItemAtNewLocation = IncomingItem::where('lokasi_rak_barang', $newLocation)
                                                           ->where('id', '!=', $id)
                                                           ->where('jumlah_barang', '>', 0) // Hanya jika ada barang aktif di sana
                                                           ->first();
                if ($existingItemAtNewLocation) {
                    \Log::warning('updateIncomingItem: New rack location occupied by different item', ['item_id' => $id, 'new_location' => $newLocation, 'occupying_item' => $existingItemAtNewLocation->nama_barang]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Lokasi rak ' . $newLocation . ' sudah ditempati oleh barang lain (' . $existingItemAtNewLocation->nama_barang . ').'
                    ], 422);
                }
            }
            
            // Jika jumlah barang menjadi 0, set lokasi rak menjadi null
            if ($request->jumlah_barang == 0) {
                \Log::info('updateIncomingItem: Item quantity set to zero, clearing rack location', ['item_id' => $id, 'old_location' => $newLocation]);
                $newLocation = null;
            }


            $incomingItem->update([
                'nama_barang' => $request->nama_barang,
                'kategori_barang' => $request->kategori_barang,
                'jumlah_barang' => $request->jumlah_barang,
                'tanggal_masuk_barang' => $request->tanggal_masuk_barang,
                'lokasi_rak_barang' => $newLocation, // Gunakan newLocation yang sudah disesuaikan
                'nama_produsen' => $request->nama_produsen,
                'metode_bayar' => $request->metode_bayar,
                'pembayaran_transaksi' => $pembayaranTransaksiPathToSave, // Save the updated image path
                'nota_transaksi' => $notaTransaksiPathToSave, // Save the updated image path
                'foto_barang' => $fotoPathToSave, // Save the updated image path
            ]);
            \Log::info('updateIncomingItem: Item updated successfully', ['item_id' => $incomingItem->id, 'updated_data' => $incomingItem->toArray()]);


            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil diperbarui.',
                'data' => $incomingItem
            ]);
        } catch (\Exception $e) {
            // If an error occurs after new file upload, delete the newly uploaded files to prevent orphans
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
            $incomingItem = IncomingItem::findOrFail($id);
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


        // Mendapatkan jumlah total rak yang terisi untuk statistik
        $occupiedRacksCount = $incomingItems->unique('lokasi_rak_barang')->count();
        \Log::info('showWarehouseMonitor: Occupied racks count', ['count' => $occupiedRacksCount]);


        return view('staff_admin.warehouse_monitor', [
            'aggregatedItems' => $aggregatedItems, // Meneruskan data teragregasi
            'occupiedRacksCount' => $occupiedRacksCount, // Meneruskan jumlah untuk statistik
        ]);
    }


    /**
     * Mendapatkan barang keluar berdasarkan ID.
     */
    public function getOutgoingItem($id)
    {
        \Log::info('getOutgoingItem: Request received', ['item_id' => $id]);
        try {
            $outgoingItem = OutgoingItem::findOrFail($id);
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
}
