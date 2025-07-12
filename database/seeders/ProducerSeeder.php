<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Producer; // Import model Producer

class ProducerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $producers = [
            [
                'nama_produsen_supplier' => 'PT. Elektronik Jaya',
                'kontak_whatsapp' => '6281234567890',
            ],
            [
                'nama_produsen_supplier' => 'CV. Furniture Indah',
                'kontak_whatsapp' => '628765432109',
            ],
            [
                'nama_produsen_supplier' => 'Distributor IT Cepat',
                'kontak_whatsapp' => '6285000111222',
            ],
            [
                'nama_produsen_supplier' => 'Grosir Alat Tulis',
                'kontak_whatsapp' => '6281122334455',
            ],
            [
                'nama_produsen_supplier' => 'Pemasok Bahan Bangunan',
                'kontak_whatsapp' => '6281345678901',
            ],
            [
                'nama_produsen_supplier' => 'Produsen Pakaian Fashion',
                'kontak_whatsapp' => '6282233445566',
            ],
        ];

        foreach ($producers as $producer) {
            Producer::create($producer);
        }
    }
}
