<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VerificationItem;
use App\Models\IncomingItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class VerificationItemController extends Controller
{
    // Ambil semua barang yang perlu diverifikasi
    public function index()
    {
        $items = VerificationItem::whereNull('verified_at')->orderByDesc('created_at')->get();
        return response()->json(['success' => true, 'data' => $items]);
    }

    // Simpan barang baru ke tabel verifikasi_barang
    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'nama_barang' => 'required|string|max:255',
        'jumlah_barang' => 'required|integer|min:1',
        'satuan_barang' => 'required|string|max:20',
        'kondisi_fisik' => 'required|string|max:50',
        'nama_produsen' => 'nullable|string|max:255',
    ]);
    if ($validator->fails()) {
        return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
    }
    $kategori = $request->kategori_barang ?? 'Lainnya'; // Default jika null

    // Simpan ke incoming_items
    $incoming = \App\Models\IncomingItem::create([
        'nama_barang' => $request->nama_barang,
        'kategori_barang' => $kategori,
        'tanggal_masuk_barang' => now()->toDateString(),
        'jumlah_barang' => $request->jumlah_barang,
        'satuan_barang' => $request->satuan_barang,
        'nama_produsen' => $request->nama_produsen,
        // tambahkan field lain jika perlu
    ]);

    return response()->json(['success' => true, 'message' => 'Barang berhasil masuk ke stok masuk', 'data' => $incoming]);
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

            // If condition is not "Baik", delete the item and return
            if ($request->kondisi_fisik !== 'Baik') {
                // Store the item details for the response
                $itemDetails = [
                    'nama_barang' => $verificationItem->nama_barang,
                    'kondisi_fisik' => $request->kondisi_fisik,
                    'catatan_verifikasi' => $request->catatan_verifikasi
                ];

                // Delete any uploaded files
                if ($verificationItem->foto_barang) {
                    Storage::disk('public')->delete($verificationItem->foto_barang);
                }
                if ($verificationItem->pembayaran_transaksi) {
                    Storage::disk('public')->delete($verificationItem->pembayaran_transaksi);
                }
                if ($verificationItem->nota_transaksi) {
                    Storage::disk('public')->delete($verificationItem->nota_transaksi);
                }

                // Delete the verification item
                $verificationItem->delete();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Barang telah dihapus karena kondisi ' . strtolower($request->kondisi_fisik),
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
                'lokasi_rak_barang' => $verificationItem->lokasi_rak_barang,
                'producer_id' => $verificationItem->producer_id,
                'metode_bayar' => $verificationItem->metode_bayar,
                'pembayaran_transaksi' => $verificationItem->pembayaran_transaksi,
                'nota_transaksi' => $verificationItem->nota_transaksi,
                'foto_barang' => $verificationItem->foto_barang,
                'kondisi_fisik' => 'Baik',
                'catatan' => $request->catatan_verifikasi
            ]);

            // Update verification item status
            $verificationItem->update([
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
