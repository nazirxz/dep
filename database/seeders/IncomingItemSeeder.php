<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\IncomingItem;
use Carbon\Carbon;

class IncomingItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data yang sudah ada untuk menghindari duplikasi saat seeding ulang
        IncomingItem::truncate();

        IncomingItem::create([
            'nama_barang' => 'Chitato Sapi Panggang',
            'kategori_barang' => 'Makanan Ringan',
            'tanggal_masuk_barang' => Carbon::now()->subDays(10),
            'jumlah_barang' => 120,
            'lokasi_rak_barang' => 'R6-3-4', // Diperbarui sesuai permintaan
            'nama_produsen' => 'Indofood Fritolay',
            'metode_bayar' => 'Transfer Bank',
            'pembayaran_transaksi' => 120000.00,
            'nota_transaksi' => 'INV/2025/001',
            'foto_barang' => 'images/chitato.jpg', // Menambahkan foto barang
        ]);

        IncomingItem::create([
            'nama_barang' => 'Aqua Botol 600ml',
            'kategori_barang' => 'Minuman',
            'tanggal_masuk_barang' => Carbon::now()->subDays(5),
            'jumlah_barang' => 200,
            'lokasi_rak_barang' => 'R1-2-1', // Contoh format baru
            'nama_produsen' => 'Danone Aqua',
            'metode_bayar' => 'Cash',
            'pembayaran_transaksi' => 250000.00,
            'nota_transaksi' => 'INV/2025/002',
            'foto_barang' => null, // Contoh barang tanpa foto
        ]);

        IncomingItem::create([
            'nama_barang' => 'Sabun Mandi Lifebuoy',
            'kategori_barang' => 'Perlengkapan Mandi',
            'tanggal_masuk_barang' => Carbon::now()->subDays(7),
            'jumlah_barang' => 80,
            'lokasi_rak_barang' => 'R3-5-2', // Contoh format baru
            'nama_produsen' => 'Unilever',
            'metode_bayar' => 'Transfer Bank',
            'pembayaran_transaksi' => 80000.00,
            'nota_transaksi' => 'INV/2025/003',
            'foto_barang' => null, // Contoh barang tanpa foto
        ]);

        // Anda bisa menambahkan lebih banyak data di sini
    }
}
