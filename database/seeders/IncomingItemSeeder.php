<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\IncomingItem;
use App\Models\Producer;
use App\Models\Category;
use Carbon\Carbon;

class IncomingItemSeeder extends Seeder
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

        IncomingItem::create([
            'nama_barang' => 'Chitato Sapi Panggang',
            'kategori_barang' => $makananRinganCategory->nama_kategori,
            'category_id' => $makananRinganCategory->id,
            'tanggal_masuk_barang' => Carbon::now()->subDays(10),
            'jumlah_barang' => 120,
            'lokasi_rak_barang' => 'R6-3-4',
            'producer_id' => $indofoodProducer->id,
            'metode_bayar' => 'Transfer Bank',
            'pembayaran_transaksi' => 'transactions/sample_payment_chitato.jpg',
            'nota_transaksi' => 'transactions/sample_nota_chitato.pdf',
            'foto_barang' => 'images/chitato.jpg',
        ]);

        IncomingItem::create([
            'nama_barang' => 'Aqua Botol 600ml',
            'kategori_barang' => $minumanCategory->nama_kategori,
            'category_id' => $minumanCategory->id,
            'tanggal_masuk_barang' => Carbon::now()->subDays(5),
            'jumlah_barang' => 200,
            'lokasi_rak_barang' => 'R1-2-1',
            'producer_id' => $danoneProducer->id,
            'metode_bayar' => 'Cash',
            'pembayaran_transaksi' => null,
            'nota_transaksi' => 'transactions/sample_nota_aqua.png',
            'foto_barang' => null,
        ]);

        IncomingItem::create([
            'nama_barang' => 'Sabun Mandi Lifebuoy',
            'kategori_barang' => $perlengkapanMandiCategory->nama_kategori,
            'category_id' => $perlengkapanMandiCategory->id,
            'tanggal_masuk_barang' => Carbon::now()->subDays(7),
            'jumlah_barang' => 80,
            'lokasi_rak_barang' => 'R3-5-2',
            'producer_id' => $unileverProducer->id,
            'metode_bayar' => 'Transfer Bank',
            'pembayaran_transaksi' => 'transactions/sample_payment_lifebuoy.pdf',
            'nota_transaksi' => null,
            'foto_barang' => null,
        ]);
    }
}
