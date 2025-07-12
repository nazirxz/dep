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
        // Hapus semua data yang ada di tabel incoming_items sebelum seeding baru
        // Ini memastikan data yang bersih setiap kali seeder dijalankan
        IncomingItem::truncate();

        // Data dummy untuk barang masuk
        $items = [
            [
                'nama_barang' => 'Laptop ASUS ROG Strix',
                'kategori_barang' => 'Elektronik',
                'tanggal_masuk_barang' => Carbon::parse('2025-06-15'),
                'jumlah_barang' => 10,
                'lokasi_rak_barang' => 'R1-1-1', // Lokasi rak yang valid
            ],
            [
                'nama_barang' => 'Meja Gaming Ergonomis',
                'kategori_barang' => 'Furniture',
                'tanggal_masuk_barang' => Carbon::parse('2025-06-18'),
                'jumlah_barang' => 3,
                'lokasi_rak_barang' => 'R2-2-3', // Lokasi rak yang valid
            ],
            [
                'nama_barang' => 'Keyboard Mekanikal RGB',
                'kategori_barang' => 'Elektronik',
                'tanggal_masuk_barang' => Carbon::parse('2025-06-20'),
                'jumlah_barang' => 25,
                'lokasi_rak_barang' => 'R1-1-2', // Lokasi rak yang valid
            ],
            [
                'nama_barang' => 'Mouse Gaming Wireless',
                'kategori_barang' => 'Elektronik',
                'tanggal_masuk_barang' => Carbon::parse('2025-06-22'),
                'jumlah_barang' => 0, // Stok habis
                'lokasi_rak_barang' => null, // Lokasi dikosongkan jika stok habis
            ],
            [
                'nama_barang' => 'Kursi Kantor Premium',
                'kategori_barang' => 'Furniture',
                'tanggal_masuk_barang' => Carbon::parse('2025-06-25'),
                'jumlah_barang' => 7,
                'lokasi_rak_barang' => 'R3-4-6', // Lokasi rak yang valid
            ],
            [
                'nama_barang' => 'Monitor Ultrawide',
                'kategori_barang' => 'Elektronik',
                'tanggal_masuk_barang' => Carbon::parse('2025-06-28'),
                'jumlah_barang' => 15,
                'lokasi_rak_barang' => 'R1-2-1',
            ],
            [
                'nama_barang' => 'Headset Gaming',
                'kategori_barang' => 'Elektronik',
                'tanggal_masuk_barang' => Carbon::parse('2025-07-01'),
                'jumlah_barang' => 5, // Stok rendah
                'lokasi_rak_barang' => 'R1-3-2',
            ],
            [
                'nama_barang' => 'Speaker Bluetooth',
                'kategori_barang' => 'Elektronik',
                'tanggal_masuk_barang' => Carbon::parse('2025-07-03'),
                'jumlah_barang' => 20,
                'lokasi_rak_barang' => 'R2-1-4',
            ],
            [
                'nama_barang' => 'Lemari Arsip Baja',
                'kategori_barang' => 'Furniture',
                'tanggal_masuk_barang' => Carbon::parse('2025-07-05'),
                'jumlah_barang' => 2, // Stok rendah
                'lokasi_rak_barang' => 'R4-1-1',
            ],
            [
                'nama_barang' => 'Proyektor Mini',
                'kategori_barang' => 'Elektronik',
                'tanggal_masuk_barang' => Carbon::parse('2025-07-08'),
                'jumlah_barang' => 1, // Stok rendah
                'lokasi_rak_barang' => 'R5-2-5',
            ],
            [
                'nama_barang' => 'Kertas HVS A4',
                'kategori_barang' => 'ATK',
                'tanggal_masuk_barang' => Carbon::parse('2025-07-10'),
                'jumlah_barang' => 50,
                'lokasi_rak_barang' => 'R6-3-3',
            ],
            [
                'nama_barang' => 'Pulpen Gel Hitam',
                'kategori_barang' => 'ATK',
                'tanggal_masuk_barang' => Carbon::parse('2025-07-11'),
                'jumlah_barang' => 100,
                'lokasi_rak_barang' => 'R6-3-4',
            ],
        ];

        foreach ($items as $item) {
            // Tentukan status barang berdasarkan jumlah
            $item['status_barang'] = $this->determineStatus($item['jumlah_barang']);
            IncomingItem::create($item);
        }
    }

    /**
     * Determine item status based on quantity.
     * This is a helper method, duplicated from ItemManagementController for seeding purposes.
     */
    private function determineStatus($quantity)
    {
        if ($quantity == 0) {
            return 'Habis';
        } elseif ($quantity < 10) {
            return 'Stok Rendah';
        } else {
            return 'Tersedia';
        }
    }
}
