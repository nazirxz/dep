<?php

namespace App\Http\Controllers;

use App\Models\VerificationItem;
use App\Models\IncomingItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
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
            \Log::info('verify: Starting verification process', ['item_id' => $id, 'request_data' => $request->all()]);
            
            $item = VerificationItem::findOrFail($id);
            
            if ($item->is_verified) {
                return response()->json([
                    'success' => false,
                    'message' => 'Barang ini sudah diverifikasi sebelumnya.'
                ], 422);
            }
            
            // Validasi input verifikasi
            $validator = Validator::make($request->all(), [
                'producer_id' => 'required|exists:producers,id',
                'satuan_barang' => 'required|string|max:50',
                'kondisi_fisik' => 'required|string|in:Baik,Rusak Ringan,Tidak Sesuai',
                'catatan_verifikasi' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                \Log::warning('verify: Validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Create new incoming item
            $incoming = IncomingItem::create([
                'nama_barang' => $item->nama_barang,
                'kategori_barang' => $item->kategori_barang,
                'tanggal_masuk_barang' => $item->tanggal_masuk_barang,
                'jumlah_barang' => $item->jumlah_barang,
                'satuan_barang' => $request->satuan_barang,
                'lokasi_rak_barang' => null, // Will be assigned later
                'producer_id' => $request->producer_id,
                'metode_bayar' => $item->metode_bayar,
                'pembayaran_transaksi' => $item->pembayaran_transaksi,
                'nota_transaksi' => $item->nota_transaksi,
                'foto_barang' => $item->foto_barang,
            ]);

            // Update verification item
            $item->update([
                'is_verified' => true,
                'verified_by' => Auth::id(),
                'verified_at' => Carbon::now(),
                'incoming_item_id' => $incoming->id,
                'satuan_barang' => $request->satuan_barang,
                'kondisi_fisik' => $request->kondisi_fisik,
                'catatan_verifikasi' => $request->catatan_verifikasi,
            ]);

            \Log::info('verify: Verification completed successfully', [
                'verification_id' => $item->id,
                'incoming_id' => $incoming->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Barang berhasil diverifikasi.',
                'data' => [
                    'verification' => $item,
                    'incoming' => $incoming
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in verify: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memverifikasi barang: ' . $e->getMessage()
            ], 500);
        }
    }
}
