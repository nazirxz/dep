<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\OutgoingItem;
use Carbon\Carbon; // Pastikan Carbon diimpor
use Faker\Factory as Faker; // Import Faker

class OutgoingItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data yang sudah ada untuk menghindari duplikasi saat seeding ulang
        OutgoingItem::truncate();

        // Menggunakan Faker untuk data dummy
        $faker = Faker::create('id_ID');

        OutgoingItem::create([
            'nama_barang' => 'Chitato Sapi Panggang',
            'kategori_barang' => 'Makanan Ringan',
            'tanggal_keluar_barang' => Carbon::now()->subDays(3),
            'jumlah_barang' => 50,
            'tujuan_distribusi' => 'Toko A',
            'lokasi_rak_barang' => 'R6-3-4', // Diperbarui sesuai permintaan
            'nama_produsen' => 'Indofood Fritolay',
            'metode_bayar' => 'Cash',
            'pembayaran_transaksi' => 60000.00,
            'nota_transaksi' => 'OUT/2025/001',
            'foto_barang' => 'images/chitato.jpg', // Menambahkan foto barang
        ]);

        OutgoingItem::create([
            'nama_barang' => 'Aqua Botol 60,0 ml',
            'kategori_barang' => 'Minuman',
            'tanggal_keluar_barang' => Carbon::now()->subDays(1),
            'jumlah_barang' => 100,
            'tujuan_distribusi' => 'Supermarket B',
            'lokasi_rak_barang' => 'R1-2-1', // Contoh format baru
            'nama_produsen' => 'Danone Aqua',
            'metode_bayar' => 'Transfer Bank',
            'pembayaran_transaksi' => 150000.00,
            'nota_transaksi' => 'OUT/2025/002',
            'foto_barang' => null, // Contoh barang tanpa foto
        ]);

        OutgoingItem::create([
            'nama_barang' => 'Sabun Mandi Lifebuoy',
            'kategori_barang' => 'Perlengkapan Mandi',
            'tanggal_keluar_barang' => Carbon::now()->subDays(2),
            'jumlah_barang' => 30,
            'tujuan_distribusi' => 'Minimarket C',
            'lokasi_rak_barang' => 'R3-5-2', // Contoh format baru
            'nama_produsen' => 'Unilever',
            'metode_bayar' => 'Cash',
            'pembayaran_transaksi' => 35000.00,
            'nota_transaksi' => 'OUT/2025/003',
            'foto_barang' => null, // Contoh barang tanpa foto
        ]);

        // Anda bisa menambahkan lebih banyak data di sini dengan format lokasi rak yang baru
        for ($i = 0; $i < 5; $i++) { // Menambahkan 5 data dummy lagi
            OutgoingItem::create([
                'nama_barang' => $faker->word . ' ' . $faker->randomElement(['Baru', 'Lama', 'Premium']),
                'kategori_barang' => $faker->randomElement(['Elektronik', 'Pakaian', 'Alat Tulis', 'Kecantikan']),
                'tanggal_keluar_barang' => $faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
                'jumlah_barang' => $faker->numberBetween(10, 100),
                'tujuan_distribusi' => $faker->company,
                'lokasi_rak_barang' => 'R' . $faker->numberBetween(1, 10) . '-' . $faker->numberBetween(1, 5) . '-' . $faker->numberBetween(1, 5), // Format lokasi rak baru
                'nama_produsen' => $faker->name,
                'metode_bayar' => $faker->randomElement(['Cash', 'Transfer Bank', 'Kartu Kredit']),
                'pembayaran_transaksi' => $faker->randomFloat(2, 50000, 5000000),
                'nota_transaksi' => 'OUT/' . date('Y') . '/' . $faker->unique()->randomNumber(5),
                'foto_barang' => $faker->boolean(50) ? 'images/chitato.jpg' : null, // 50% kemungkinan ada foto chitato
            ]);
        }
    }
}
