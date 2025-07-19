<?php

namespace App\Http\Controllers;

use App\Models\VerificationItem;
use App\Models\IncomingItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
        $item = VerificationItem::findOrFail($id);
    
        // Validasi input verifikasi
        $validator = Validator::make($request->all(), [
            'kategori_barang' => 'required|string|max:100',
            'lokasi_rak_barang' => 'nullable|string|max:50',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $validator->errors()], 422);
        }
    
        // Simpan ke tabel incoming_items
        $incoming = new \App\Models\IncomingItem([
            'nama_barang' => $item->nama_barang,
            'kategori_barang' => $request->kategori_barang,
            'tanggal_masuk_barang' => $item->tanggal_masuk_barang,
            'jumlah_barang' => $item->jumlah_barang,
            'satuan_barang' => $item->satuan_barang,
            'lokasi_rak_barang' => $request->lokasi_rak_barang,
            'nama_produsen' => $item->nama_produsen,
        ]);
        $incoming->save();
    
        // Hapus data dari tabel verifikasi_barang
        $item->delete();
    
        return response()->json(['success' => true, 'message' => 'Barang berhasil diverifikasi dan dipindahkan ke stok masuk']);
    }

}
