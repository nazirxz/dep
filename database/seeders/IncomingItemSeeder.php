<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\IncomingItem; // Import model IncomingItem
use Carbon\Carbon; // Untuk bekerja dengan tanggal

class IncomingItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data dummy untuk barang masuk
        $items = [
            [
                'nama_barang' => 'Laptop ASUS ROG Strix',
                'kategori_barang' => 'Elektronik',
                'tanggal_masuk_barang' => Carbon::parse('2025-06-15'),
                'jumlah_barang' => 10,
                'lokasi_rak_barang' => 'A1-01', // Tambahkan lokasi rak
                // status_barang akan otomatis diatur oleh model
            ],
            [
                'nama_barang' => 'Meja Gaming Ergonomis',
                'kategori_barang' => 'Furniture',
                'tanggal_masuk_barang' => Carbon::parse('2025-06-18'),
                'jumlah_barang' => 3,
                'lokasi_rak_barang' => 'B2-05', // Tambahkan lokasi rak
            ],
            [
                'nama_barang' => 'Keyboard Mekanikal RGB',
                'kategori_barang' => 'Elektronik',
                'tanggal_masuk_barang' => Carbon::parse('2025-06-20'),
                'jumlah_barang' => 25,
                'lokasi_rak_barang' => 'A1-02', // Tambahkan lokasi rak
            ],
            [
                'nama_barang' => 'Mouse Gaming Wireless',
                'kategori_barang' => 'Elektronik',
                'tanggal_masuk_barang' => Carbon::parse('2025-06-22'),
                'jumlah_barang' => 0,
                'lokasi_rak_barang' => 'A1-03', // Tambahkan lokasi rak
            ],
            [
                'nama_barang' => 'Kursi Kantor Premium',
                'kategori_barang' => 'Furniture',
                'tanggal_masuk_barang' => Carbon::parse('2025-06-25'),
                'jumlah_barang' => 7,
                'lokasi_rak_barang' => 'B2-06', // Tambahkan lokasi rak
            ],
        ];

        foreach ($items as $item) {
            IncomingItem::create($item);
        }

        // Anda juga bisa menggunakan factory untuk membuat data lebih banyak
        // IncomingItem::factory(50)->create();
    }
}
