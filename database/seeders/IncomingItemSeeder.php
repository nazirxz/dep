<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\IncomingItem; // Import model IncomingItem
use Carbon\Carbon; // Untuk bekerja dengan tanggal
use Faker\Factory as Faker; // Import Faker untuk data dummy

class IncomingItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        IncomingItem::truncate(); // Hapus semua data yang ada di tabel incoming_items sebelum seeding baru

        $faker = Faker::create('id_ID'); // Menggunakan Faker dengan lokal Indonesia

        // Data dummy untuk barang masuk
        $items = [
            [
                'nama_barang' => 'Laptop ASUS ROG Strix',
                'kategori_barang' => 'Elektronik',
                'tanggal_masuk_barang' => Carbon::parse('2025-06-15'),
                'jumlah_barang' => 10,
                'lokasi_rak_barang' => 'R1-1-1',
                'nama_produsen' => 'Toko Komputer Jaya', // Data baru
                'metode_bayar' => 'Transfer Bank', // Data baru
                'pembayaran_transaksi' => 15000000.00, // Data baru
                'nota_transaksi' => 'INV-20250615-001', // Data baru
            ],
            [
                'nama_barang' => 'Meja Gaming Ergonomis',
                'kategori_barang' => 'Furniture',
                'tanggal_masuk_barang' => Carbon::parse('2025-06-18'),
                'jumlah_barang' => 3,
                'lokasi_rak_barang' => 'R2-2-3',
                'nama_produsen' => 'Mebel Bahagia',
                'metode_bayar' => 'Cash',
                'pembayaran_transaksi' => 3500000.00,
                'nota_transaksi' => 'INV-20250618-002',
            ],
            [
                'nama_barang' => 'Keyboard Mekanikal RGB',
                'kategori_barang' => 'Elektronik',
                'tanggal_masuk_barang' => Carbon::parse('2025-06-20'),
                'jumlah_barang' => 25,
                'lokasi_rak_barang' => 'R1-1-2',
                'nama_produsen' => 'Gears Up Store',
                'metode_bayar' => 'Kartu Kredit',
                'pembayaran_transaksi' => 2500000.00,
                'nota_transaksi' => 'INV-20250620-003',
            ],
            [
                'nama_barang' => 'Mouse Gaming Wireless',
                'kategori_barang' => 'Elektronik',
                'tanggal_masuk_barang' => Carbon::parse('2025-06-22'),
                'jumlah_barang' => 0, // Stok habis
                'lokasi_rak_barang' => null,
                'nama_produsen' => 'Gears Up Store',
                'metode_bayar' => 'Transfer Bank',
                'pembayaran_transaksi' => 0.00, // Tidak ada pembayaran jika stok habis atau belum ada
                'nota_transaksi' => 'INV-20250622-004',
            ],
            [
                'nama_barang' => 'Kursi Kantor Premium',
                'kategori_barang' => 'Furniture',
                'tanggal_masuk_barang' => Carbon::parse('2025-06-25'),
                'jumlah_barang' => 7,
                'lokasi_rak_barang' => 'R3-4-6',
                'nama_produsen' => 'Mebel Mewah',
                'metode_bayar' => 'Cash',
                'pembayaran_transaksi' => 7000000.00,
                'nota_transaksi' => 'INV-20250625-005',
            ],
            [
                'nama_barang' => 'Monitor Ultrawide',
                'kategori_barang' => 'Elektronik',
                'tanggal_masuk_barang' => Carbon::parse('2025-06-28'),
                'jumlah_barang' => 15,
                'lokasi_rak_barang' => 'R1-2-1',
                'nama_produsen' => 'Toko Elektronik Hebat',
                'metode_bayar' => 'Transfer Bank',
                'pembayaran_transaksi' => 10000000.00,
                'nota_transaksi' => 'INV-20250628-006',
            ],
            [
                'nama_barang' => 'Headset Gaming',
                'kategori_barang' => 'Elektronik',
                'tanggal_masuk_barang' => Carbon::parse('2025-07-01'),
                'jumlah_barang' => 5,
                'lokasi_rak_barang' => 'R1-3-2',
                'nama_produsen' => 'Gears Up Store',
                'metode_bayar' => 'Kartu Kredit',
                'pembayaran_transaksi' => 1250000.00,
                'nota_transaksi' => 'INV-20250701-007',
            ],
            [
                'nama_barang' => 'Speaker Bluetooth',
                'kategori_barang' => 'Elektronik',
                'tanggal_masuk_barang' => Carbon::parse('2025-07-03'),
                'jumlah_barang' => 20,
                'lokasi_rak_barang' => 'R2-1-4',
                'nama_produsen' => 'Toko Elektronik Hebat',
                'metode_bayar' => 'Transfer Bank',
                'pembayaran_transaksi' => 2000000.00,
                'nota_transaksi' => 'INV-20250703-008',
            ],
            [
                'nama_barang' => 'Lemari Arsip Baja',
                'kategori_barang' => 'Furniture',
                'tanggal_masuk_barang' => Carbon::parse('2025-07-05'),
                'jumlah_barang' => 2,
                'lokasi_rak_barang' => 'R4-1-1',
                'nama_produsen' => 'Kantor Jaya',
                'metode_bayar' => 'Cash',
                'pembayaran_transaksi' => 1800000.00,
                'nota_transaksi' => 'INV-20250705-009',
            ],
            [
                'nama_barang' => 'Proyektor Mini',
                'kategori_barang' => 'Elektronik',
                'tanggal_masuk_barang' => Carbon::parse('2025-07-08'),
                'jumlah_barang' => 1,
                'lokasi_rak_barang' => 'R5-2-5',
                'nama_produsen' => 'Toko Elektronik Hebat',
                'metode_bayar' => 'Transfer Bank',
                'pembayaran_transaksi' => 2500000.00,
                'nota_transaksi' => 'INV-20250708-010',
            ],
            [
                'nama_barang' => 'Kertas HVS A4',
                'kategori_barang' => 'ATK',
                'tanggal_masuk_barang' => Carbon::parse('2025-07-10'),
                'jumlah_barang' => 50,
                'lokasi_rak_barang' => 'R6-3-3',
                'nama_produsen' => 'Toko ATK Lengkap',
                'metode_bayar' => 'Cash',
                'pembayaran_transaksi' => 500000.00,
                'nota_transaksi' => 'INV-20250710-011',
            ],
            [
                'nama_barang' => 'Pulpen Gel Hitam',
                'kategori_barang' => 'ATK',
                'tanggal_masuk_barang' => Carbon::parse('2025-07-11'),
                'jumlah_barang' => 100,
                'lokasi_rak_barang' => 'R6-3-4',
                'nama_produsen' => 'Toko ATK Lengkap',
                'metode_bayar' => 'Transfer Bank',
                'pembayaran_transaksi' => 250000.00,
                'nota_transaksi' => 'INV-20250711-012',
            ],
        ];

        foreach ($items as $item) {
            IncomingItem::create($item);
        }
    }
}