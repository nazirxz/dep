<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\OutgoingItem; // Import model OutgoingItem
use Carbon\Carbon; // Untuk bekerja dengan tanggal

class OutgoingItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data dummy untuk barang keluar
        $items = [
            [
                'nama_barang' => 'Laptop ASUS ROG Strix',
                'kategori_barang' => 'Elektronik',
                'tanggal_keluar_barang' => Carbon::parse('2025-06-20'),
                'jumlah_barang' => 2,
                'tujuan_distribusi' => 'Toko Komputer Jaya',
                'lokasi_rak_barang' => 'A1-01', // Tambahkan lokasi rak
            ],
            [
                'nama_barang' => 'Meja Gaming Ergonomis',
                'kategori_barang' => 'Furniture',
                'tanggal_keluar_barang' => Carbon::parse('2025-06-21'),
                'jumlah_barang' => 1,
                'tujuan_distribusi' => 'Pelanggan Online Budi',
                'lokasi_rak_barang' => 'B2-05', // Tambahkan lokasi rak
            ],
            [
                'nama_barang' => 'Keyboard Mekanikal RGB',
                'kategori_barang' => 'Elektronik',
                'tanggal_keluar_barang' => Carbon::parse('2025-06-23'),
                'jumlah_barang' => 5,
                'tujuan_distribusi' => 'Distributor Gadget Keren',
                'lokasi_rak_barang' => 'A1-02', // Tambahkan lokasi rak
            ],
            [
                'nama_barang' => 'Kursi Kantor Premium',
                'kategori_barang' => 'Furniture',
                'tanggal_keluar_barang' => Carbon::parse('2025-06-28'),
                'jumlah_barang' => 2,
                'tujuan_distribusi' => 'Kantor Pusat ABC',
                'lokasi_rak_barang' => 'B2-06', // Tambahkan lokasi rak
            ],
        ];

        foreach ($items as $item) {
            OutgoingItem::create($item);
        }

        // Anda juga bisa menggunakan factory untuk membuat data lebih banyak
        // OutgoingItem::factory(50)->create();
    }
}
