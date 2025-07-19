<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\OutgoingItem;
use App\Models\Producer;
use App\Models\Category;
use Carbon\Carbon;

class OutgoingItemSeeder extends Seeder
{
    public function run()
    {
        // Get some producer IDs to use
        $producers = Producer::all();
        if ($producers->isEmpty()) {
            throw new \Exception('Please run ProducerSeeder first');
        }

        // Get some category IDs to use
        $categories = Category::all();
        if ($categories->isEmpty()) {
            throw new \Exception('Please run CategorySeeder first');
        }

        // Find specific categories
        $makananRinganCategory = Category::where('nama_kategori', 'Makanan Ringan')->firstOrFail();
        $minumanCategory = Category::where('nama_kategori', 'Minuman')->firstOrFail();
        $perlengkapanMandiCategory = Category::where('nama_kategori', 'Perlengkapan Mandi')->firstOrFail();

        // Find specific producers
        $indofoodProducer = Producer::where('nama_produsen_supplier', 'like', '%Elektronik%')->first() ?? $producers->first();
        $danoneProducer = Producer::where('nama_produsen_supplier', 'like', '%Furniture%')->first() ?? $producers->first();
        $unileverProducer = Producer::where('nama_produsen_supplier', 'like', '%IT%')->first() ?? $producers->first();

        OutgoingItem::create([
            'nama_barang' => 'Chitato Sapi Panggang',
            'kategori_barang' => $makananRinganCategory->nama_kategori,
            'category_id' => $makananRinganCategory->id,
            'tanggal_keluar_barang' => Carbon::now()->subDays(3),
            'jumlah_barang' => 50,
            'tujuan_distribusi' => 'Toko A',
            'lokasi_rak_barang' => 'R6-3-4',
            'producer_id' => $indofoodProducer->id,
            'metode_bayar' => 'Cash',
            'pembayaran_transaksi' => 'transactions/sample_payment_chitato_out.jpg',
            'nota_transaksi' => 'transactions/sample_nota_chitato_out.pdf',
            'foto_barang' => 'images/chitato.jpg',
        ]);

        OutgoingItem::create([
            'nama_barang' => 'Aqua Botol 600ml',
            'kategori_barang' => $minumanCategory->nama_kategori,
            'category_id' => $minumanCategory->id,
            'tanggal_keluar_barang' => Carbon::now()->subDays(2),
            'jumlah_barang' => 100,
            'tujuan_distribusi' => 'Toko B',
            'lokasi_rak_barang' => 'R1-2-1',
            'producer_id' => $danoneProducer->id,
            'metode_bayar' => 'Transfer Bank',
            'pembayaran_transaksi' => null,
            'nota_transaksi' => 'transactions/sample_nota_aqua_out.png',
            'foto_barang' => null,
        ]);

        OutgoingItem::create([
            'nama_barang' => 'Sabun Mandi Lifebuoy',
            'kategori_barang' => $perlengkapanMandiCategory->nama_kategori,
            'category_id' => $perlengkapanMandiCategory->id,
            'tanggal_keluar_barang' => Carbon::now()->subDays(1),
            'jumlah_barang' => 30,
            'tujuan_distribusi' => 'Toko C',
            'lokasi_rak_barang' => 'R3-5-2',
            'producer_id' => $unileverProducer->id,
            'metode_bayar' => 'Cash',
            'pembayaran_transaksi' => 'transactions/sample_payment_lifebuoy_out.pdf',
            'nota_transaksi' => null,
            'foto_barang' => null,
        ]);
    }
}
