<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VerificationItem;
use App\Models\IncomingItem;
use App\Models\ReturnedItem;
use App\Models\WarehouseLocation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class VerificationItemController extends Controller
{
    // Ambil semua barang yang perlu diverifikasi (status pending)
    public function index()
    {
        $items = VerificationItem::where('status', 'pending')
            ->with(['producer', 'category'])
            ->orderByDesc('created_at')
            ->get();
        return response()->json(['success' => true, 'data' => $items]);
    }

    // Simpan barang baru ke tabel verifikasi_barang
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_barang' => 'required|string|max:255',
            'kategori_barang' => 'required|string|max:100',
            'category_id' => 'required|exists:categories,id',
            'producer_id' => 'required|exists:producers,id',
            'jumlah_barang' => 'required|integer|min:1',
            'harga_jual' => 'nullable|numeric|min:0',
            'tanggal_masuk_barang' => 'required|date',
            'lokasi_rak_barang' => 'nullable|string|regex:/^R[1-8]-[1-4]-[1-6]$/',
            'metode_bayar' => 'nullable|string|max:50',
        ], [
            'nama_barang.required' => 'Nama barang wajib diisi.',
            'kategori_barang.required' => 'Kategori barang wajib diisi.',
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
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }

        try {
            // Simpan langsung ke tabel verifikasi_barang dengan status pending
            $verificationItem = VerificationItem::create([
                'nama_barang' => $request->nama_barang,
                'kategori_barang' => $request->kategori_barang,
                'category_id' => $request->category_id,
                'producer_id' => $request->producer_id,
                'jumlah_barang' => $request->jumlah_barang,
                'harga_jual' => $request->harga_jual,
                'tanggal_masuk_barang' => $request->tanggal_masuk_barang,
                'lokasi_rak_barang' => $request->lokasi_rak_barang,
                'metode_bayar' => $request->metode_bayar,
                'status' => 'pending', // Status pending menunggu verifikasi
                'is_verified' => false,
                'verified_at' => null,
                'verified_by' => null,
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Barang berhasil ditambahkan ke daftar verifikasi. Menunggu verifikasi dari admin.',
                'data' => $verificationItem
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    // Verifikasi barang: pindahkan ke incoming_items

    public function verify(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $verificationItem = VerificationItem::findOrFail($id);

            // Validate request
            $request->validate([
                'kondisi_fisik' => 'required|in:Baik,Rusak Ringan,Tidak Sesuai,Kadaluarsa',
                'catatan_verifikasi' => 'nullable|string|max:1000',
            ], [
                'kondisi_fisik.required' => 'Kondisi fisik barang wajib diisi.',
                'kondisi_fisik.in' => 'Kondisi fisik harus berupa: Baik, Rusak Ringan, Tidak Sesuai, atau Kadaluarsa.',
                'catatan_verifikasi.max' => 'Catatan verifikasi maksimal 1000 karakter.',
            ]);

            // If condition is not "Baik", mark as rejected and create return record
            if ($request->kondisi_fisik !== 'Baik') {
                // Create a new returned item record
                ReturnedItem::create([
                    'nama_barang' => $verificationItem->nama_barang,
                    'kategori_barang' => $verificationItem->kategori_barang,
                    'jumlah_barang' => $verificationItem->jumlah_barang,
                    'nama_produsen' => $verificationItem->producer->nama_produsen_supplier ?? 'Tidak Diketahui',
                    'alasan_pengembalian' => $request->kondisi_fisik . ' - ' . $request->catatan_verifikasi,
                ]);

                // Update verification item status to rejected instead of deleting
                $verificationItem->update([
                    'status' => 'rejected',
                    'is_verified' => false,
                    'verified_by' => Auth::id(),
                    'verified_at' => Carbon::now(),
                    'kondisi_fisik' => $request->kondisi_fisik,
                    'catatan_verifikasi' => $request->catatan_verifikasi,
                ]);

                // Store the item details for the response
                $itemDetails = [
                    'nama_barang' => $verificationItem->nama_barang,
                    'kondisi_fisik' => $request->kondisi_fisik,
                    'catatan_verifikasi' => $request->catatan_verifikasi
                ];

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Item tidak lolos verifikasi dan telah ditandai untuk pengembalian.',
                    'data' => $itemDetails,
                    'status' => 'deleted'
                ]);
            }

            // If condition is "Baik", proceed with verification and move to incoming items
            $incomingItem = IncomingItem::create([
                'nama_barang' => $verificationItem->nama_barang,
                'kategori_barang' => $verificationItem->kategori_barang,
                'category_id' => $verificationItem->category_id,
                'tanggal_masuk_barang' => $verificationItem->tanggal_masuk_barang,
                'jumlah_barang' => $verificationItem->jumlah_barang,
                'harga_jual' => $verificationItem->harga_jual, // Tambahkan harga_jual
                'lokasi_rak_barang' => $verificationItem->lokasi_rak_barang,
                'producer_id' => $verificationItem->producer_id,
                'metode_bayar' => $verificationItem->metode_bayar,
                'pembayaran_transaksi' => $verificationItem->pembayaran_transaksi,
                'nota_transaksi' => $verificationItem->nota_transaksi,
                'foto_barang' => $verificationItem->foto_barang,
                'kondisi_fisik' => 'Baik',
                'catatan' => $request->catatan_verifikasi
            ]);

            // Update warehouse location capacity after item is verified and moved to inventory
            if ($incomingItem->lokasi_rak_barang) {
                $location = WarehouseLocation::where('location_name', $incomingItem->lokasi_rak_barang)->first();
                if (!$location) {
                    // Create new location if doesn't exist
                    WarehouseLocation::create([
                        'location_name' => $incomingItem->lokasi_rak_barang,
                        'max_capacity' => 300,
                        'current_capacity' => $incomingItem->jumlah_barang
                    ]);
                    \Log::info('VerificationItemController: Created new warehouse location', [
                        'location' => $incomingItem->lokasi_rak_barang,
                        'initial_capacity' => $incomingItem->jumlah_barang
                    ]);
                } else {
                    // Update existing location capacity
                    $location->current_capacity += $incomingItem->jumlah_barang;
                    $location->save();
                    \Log::info('VerificationItemController: Updated warehouse location capacity', [
                        'location' => $incomingItem->lokasi_rak_barang,
                        'added' => $incomingItem->jumlah_barang,
                        'new_capacity' => $location->current_capacity
                    ]);
                }
            }

            // Update verification item status
            $verificationItem->update([
                'status' => 'verified', // Update status ke verified
                'is_verified' => true,
                'verified_by' => Auth::id(),
                'verified_at' => Carbon::now(),
                'kondisi_fisik' => 'Baik',
                'catatan_verifikasi' => $request->catatan_verifikasi,
                'incoming_item_id' => $incomingItem->id
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil diverifikasi dan dipindahkan ke daftar barang masuk',
                'data' => $incomingItem,
                'status' => 'verified'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error in verifyItem: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memverifikasi barang: ' . $e->getMessage()
            ], 500);
        }
    }
}
