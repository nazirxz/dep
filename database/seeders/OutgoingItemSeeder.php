<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\OutgoingItem; // Import model OutgoingItem
use App\Models\IncomingItem; // Import model IncomingItem
use Faker\Factory as Faker; // Import Faker

class OutgoingItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID'); // Menggunakan Faker dengan lokal Indonesia

        // Ambil beberapa barang masuk yang ada untuk dijadikan barang keluar
        // Pastikan ada IncomingItem yang tersedia dan memiliki jumlah > 0
        $incomingItems = IncomingItem::where('jumlah_barang', '>', 0)->get();

        if ($incomingItems->isEmpty()) {
            $this->command->info('Tidak ada IncomingItem yang cukup untuk membuat OutgoingItem. Jalankan IncomingItemSeeder terlebih dahulu.');
            return;
        }

        // Contoh data barang keluar
        $numOutgoingItems = 20; // Jumlah barang keluar yang ingin dibuat

        for ($i = 0; $i < $numOutgoingItems; $i++) {
            // Pilih IncomingItem secara acak dari yang tersedia
            $incomingItem = $incomingItems->random();

            // Pastikan jumlah yang akan dikeluarkan tidak melebihi jumlah yang tersedia
            $quantityToMove = $faker->numberBetween(1, min(10, $incomingItem->jumlah_barang));

            // Jika jumlah yang akan dikeluarkan adalah 0 atau IncomingItem sudah habis, lewati iterasi ini
            if ($quantityToMove === 0 || $incomingItem->jumlah_barang < $quantityToMove) {
                continue;
            }

            // Kurangi jumlah barang di IncomingItem
            $incomingItem->jumlah_barang -= $quantityToMove;
            // Perbarui status barang masuk berdasarkan sisa jumlah
            if ($incomingItem->jumlah_barang <= 0) {
                $incomingItem->status_barang = 'Habis';
            } elseif ($incomingItem->jumlah_barang <= 10) { // Contoh threshold untuk 'Sedikit'
                $incomingItem->status_barang = 'Sedikit';
            } else {
                $incomingItem->status_barang = 'Banyak';
            }
            $incomingItem->save();

            // Buat entri OutgoingItem
            OutgoingItem::create([
                'nama_barang' => $incomingItem->nama_barang,
                'kategori_barang' => $incomingItem->kategori_barang,
                'tanggal_keluar_barang' => $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
                'jumlah_barang' => $quantityToMove,
                'tujuan_distribusi' => $faker->randomElement(['Toko A', 'Toko B', 'Pelanggan Online', 'Distributor XYZ', 'Cabang Pusat']),
                'lokasi_rak_barang' => $incomingItem->lokasi_rak_barang, // Lokasi rak asal
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        $this->command->info('Outgoing items seeded successfully!');
    }
}

