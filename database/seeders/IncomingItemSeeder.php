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
            'lokasi_rak_barang' => 'R6-3-4',
            'nama_produsen' => 'Indofood Fritolay',
            'metode_bayar' => 'Transfer Bank',
            'pembayaran_transaksi' => 'transactions/sample_payment_chitato.jpg', // Diperbarui menjadi jalur gambar
            'nota_transaksi' => 'transactions/sample_nota_chitato.pdf', // Diperbarui menjadi jalur gambar
            'foto_barang' => 'images/chitato.jpg',
        ]);

        IncomingItem::create([
            'nama_barang' => 'Aqua Botol 600ml',
            'kategori_barang' => 'Minuman',
            'tanggal_masuk_barang' => Carbon::now()->subDays(5),
            'jumlah_barang' => 200,
            'lokasi_rak_barang' => 'R1-2-1',
            'nama_produsen' => 'Danone Aqua',
            'metode_bayar' => 'Cash',
            'pembayaran_transaksi' => null, // Contoh tanpa bukti pembayaran
            'nota_transaksi' => 'transactions/sample_nota_aqua.png', // Contoh nota dalam bentuk gambar
            'foto_barang' => null,
        ]);

        IncomingItem::create([
            'nama_barang' => 'Sabun Mandi Lifebuoy',
            'kategori_barang' => 'Perlengkapan Mandi',
            'tanggal_masuk_barang' => Carbon::now()->subDays(7),
            'jumlah_barang' => 80,
            'lokasi_rak_barang' => 'R3-5-2',
            'nama_produsen' => 'Unilever',
            'metode_bayar' => 'Transfer Bank',
            'pembayaran_transaksi' => 'transactions/sample_payment_lifebuoy.pdf', // Contoh bukti pembayaran PDF
            'nota_transaksi' => null, // Contoh tanpa nota transaksi
            'foto_barang' => null,
        ]);

        // Anda bisa menambahkan lebih banyak data di sini
    }
}
