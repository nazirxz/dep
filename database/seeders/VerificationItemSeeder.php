<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\VerificationItem;
use App\Models\Producer;
use Carbon\Carbon;
use Faker\Factory as Faker;

class VerificationItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Get some producer IDs to use
        $producerIds = Producer::pluck('id')->toArray();

        // Item yang perlu diverifikasi (kondisi baik)
        VerificationItem::create([
            'nama_barang' => 'Indomie Goreng Jumbo',
            'kategori_barang' => 'Makanan',
            'tanggal_masuk_barang' => Carbon::now()->subDays(2),
            'jumlah_barang' => 150,
            'satuan_barang' => 'Dus',
            'lokasi_rak_barang' => null,
            'producer_id' => $faker->randomElement($producerIds),
            'metode_bayar' => 'Transfer Bank',
            'pembayaran_transaksi' => 'transactions/verif_indomie_payment.jpg',
            'nota_transaksi' => 'transactions/verif_indomie_nota.pdf',
            'foto_barang' => 'images/verif_indomie.jpg',
            'kondisi_fisik' => 'Baik',
            'catatan_verifikasi' => 'Barang baru, perlu penempatan',
            'is_verified' => false,
            'verified_by' => null,
            'verified_at' => null,
            'incoming_item_id' => null
        ]);

        // Item yang perlu diverifikasi (kondisi rusak)
        VerificationItem::create([
            'nama_barang' => 'Susu Bear Brand',
            'kategori_barang' => 'Minuman',
            'tanggal_masuk_barang' => Carbon::now()->subDays(1),
            'jumlah_barang' => 50,
            'satuan_barang' => 'Pcs',
            'lokasi_rak_barang' => null,
            'producer_id' => $faker->randomElement($producerIds),
            'metode_bayar' => 'Cash',
            'pembayaran_transaksi' => null,
            'nota_transaksi' => 'transactions/verif_susu_nota.jpg',
            'foto_barang' => 'images/verif_susu.jpg',
            'kondisi_fisik' => 'Rusak Ringan',
            'catatan_verifikasi' => 'Beberapa kaleng penyok',
            'is_verified' => false,
            'verified_by' => null,
            'verified_at' => null,
            'incoming_item_id' => null
        ]);

        // Item yang perlu diverifikasi (kondisi tidak sesuai)
        VerificationItem::create([
            'nama_barang' => 'Sabun Cuci Piring Sunlight',
            'kategori_barang' => 'Pembersih',
            'tanggal_masuk_barang' => Carbon::now()->subDays(3),
            'jumlah_barang' => 100,
            'satuan_barang' => 'Botol',
            'lokasi_rak_barang' => null,
            'producer_id' => $faker->randomElement($producerIds),
            'metode_bayar' => 'Transfer Bank',
            'pembayaran_transaksi' => 'transactions/verif_sunlight_payment.pdf',
            'nota_transaksi' => null,
            'foto_barang' => null,
            'kondisi_fisik' => 'Tidak Sesuai',
            'catatan_verifikasi' => 'Varian yang dikirim salah',
            'is_verified' => false,
            'verified_by' => null,
            'verified_at' => null,
            'incoming_item_id' => null
        ]);

        // Tambahkan beberapa item verifikasi acak
        for ($i = 0; $i < 5; $i++) {
            VerificationItem::create([
                'nama_barang' => $faker->word . ' ' . $faker->randomElement(['A', 'B', 'C']),
                'kategori_barang' => $faker->randomElement(['Elektronik', 'Pakaian', 'Alat Tulis', 'Kecantikan']),
                'tanggal_masuk_barang' => $faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
                'jumlah_barang' => $faker->numberBetween(10, 200),
                'satuan_barang' => $faker->randomElement(['Unit', 'Pcs', 'Dus', 'Kg', 'Liter']),
                'lokasi_rak_barang' => null,
                'producer_id' => $faker->randomElement($producerIds),
                'metode_bayar' => $faker->randomElement(['Cash', 'Transfer Bank']),
                'pembayaran_transaksi' => $faker->boolean(50) ? 'transactions/dummy_verif_payment_' . $faker->unique()->randomNumber(3) . '.jpg' : null,
                'nota_transaksi' => $faker->boolean(50) ? 'transactions/dummy_verif_nota_' . $faker->unique()->randomNumber(3) . '.pdf' : null,
                'foto_barang' => $faker->boolean(50) ? 'images/dummy_verif_item_' . $faker->unique()->randomNumber(3) . '.jpg' : null,
                'kondisi_fisik' => $faker->randomElement(['Baik', 'Rusak Ringan', 'Tidak Sesuai']),
                'catatan_verifikasi' => $faker->sentence(5),
                'is_verified' => false,
                'verified_by' => null,
                'verified_at' => null,
                'incoming_item_id' => null
            ]);
        }
    }
}
