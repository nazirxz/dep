<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\OutgoingItem;
use Carbon\Carbon;
use Faker\Factory as Faker;

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
            'lokasi_rak_barang' => 'R6-3-4',
            'nama_produsen' => 'Indofood Fritolay',
            'metode_bayar' => 'Cash',
            'pembayaran_transaksi' => 'transactions/sample_payment_chitato_out.jpg', // Diperbarui menjadi jalur gambar
            'nota_transaksi' => 'transactions/sample_nota_chitato_out.pdf', // Diperbarui menjadi jalur gambar
            'foto_barang' => 'images/chitato.jpg',
        ]);

        OutgoingItem::create([
            'nama_barang' => 'Aqua Botol 600ml',
            'kategori_barang' => 'Minuman',
            'tanggal_keluar_barang' => Carbon::now()->subDays(1),
            'jumlah_barang' => 100,
            'tujuan_distribusi' => 'Supermarket B',
            'lokasi_rak_barang' => 'R1-2-1',
            'nama_produsen' => 'Danone Aqua',
            'metode_bayar' => 'Transfer Bank',
            'pembayaran_transaksi' => null,
            'nota_transaksi' => 'transactions/sample_nota_aqua_out.png',
            'foto_barang' => null,
        ]);

        OutgoingItem::create([
            'nama_barang' => 'Sabun Mandi Lifebuoy',
            'kategori_barang' => 'Perlengkapan Mandi',
            'tanggal_keluar_barang' => Carbon::now()->subDays(2),
            'jumlah_barang' => 30,
            'tujuan_distribusi' => 'Minimarket C',
            'lokasi_rak_barang' => 'R3-5-2',
            'nama_produsen' => 'Unilever',
            'metode_bayar' => 'Cash',
            'pembayaran_transaksi' => 'transactions/sample_payment_lifebuoy_out.pdf',
            'nota_transaksi' => null,
            'foto_barang' => null,
        ]);

        // Anda bisa menambahkan lebih banyak data di sini dengan format lokasi rak yang baru
        for ($i = 0; $i < 5; $i++) {
            OutgoingItem::create([
                'nama_barang' => $faker->word . ' ' . $faker->randomElement(['Baru', 'Lama', 'Premium']),
                'kategori_barang' => $faker->randomElement(['Elektronik', 'Pakaian', 'Alat Tulis', 'Kecantikan']),
                'tanggal_keluar_barang' => $faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
                'jumlah_barang' => $faker->numberBetween(10, 100),
                'tujuan_distribusi' => $faker->company,
                'lokasi_rak_barang' => 'R' . $faker->numberBetween(1, 8) . '-' . $faker->numberBetween(1, 4) . '-' . $faker->numberBetween(1, 6),
                'nama_produsen' => $faker->name,
                'metode_bayar' => $faker->randomElement(['Cash', 'Transfer Bank', 'Kartu Kredit']),
                'pembayaran_transaksi' => $faker->boolean(50) ? 'transactions/dummy_payment_' . $faker->unique()->randomNumber(3) . '.jpg' : null,
                'nota_transaksi' => $faker->boolean(50) ? 'transactions/dummy_nota_' . $faker->unique()->randomNumber(3) . '.pdf' : null,
                'foto_barang' => $faker->boolean(50) ? 'images/chitato.jpg' : null,
            ]);
        }
    }
}
