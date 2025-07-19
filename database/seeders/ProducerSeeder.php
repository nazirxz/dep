<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Producer;

class ProducerSeeder extends Seeder
{
    public function run()
    {
        $producers = [
            [
                'nama_produsen_supplier' => 'Indofood',
                'alamat' => 'Jl. Industri No. 123, Jakarta',
                'no_telp' => '021-5556677',
                'email' => 'info@indofood.com',
                'catatan' => 'Produsen makanan dan minuman',
            ],
            [
                'nama_produsen_supplier' => 'Orang Tua Group',
                'alamat' => 'Jl. Manufaktur No. 45, Tangerang',
                'no_telp' => '021-7778899',
                'email' => 'contact@ot.co.id',
                'catatan' => 'Produsen makanan ringan',
            ],
            [
                'nama_produsen_supplier' => 'PT Petra',
                'alamat' => 'Jl. Cokelat No. 67, Bandung',
                'no_telp' => '022-3334455',
                'email' => 'info@petra.com',
                'catatan' => 'Produsen cokelat',
            ],
            [
                'nama_produsen_supplier' => 'Glico',
                'alamat' => 'Jl. Snack No. 89, Surabaya',
                'no_telp' => '031-6667788',
                'email' => 'sales@glico.co.id',
                'catatan' => 'Produsen snack',
            ],
            [
                'nama_produsen_supplier' => 'Danone',
                'alamat' => 'Jl. Minuman No. 12, Jakarta',
                'no_telp' => '021-8889900',
                'email' => 'info@danone.co.id',
                'catatan' => 'Produsen minuman',
            ],
            [
                'nama_produsen_supplier' => 'Sosro',
                'alamat' => 'Jl. Teh No. 34, Bekasi',
                'no_telp' => '021-4445566',
                'email' => 'contact@sosro.com',
                'catatan' => 'Produsen minuman teh',
            ],
            [
                'nama_produsen_supplier' => 'Amerta Indah Otsuka',
                'alamat' => 'Jl. Kesehatan No. 56, Jakarta',
                'no_telp' => '021-2223344',
                'email' => 'info@aio.co.id',
                'catatan' => 'Produsen minuman kesehatan',
            ],
            [
                'nama_produsen_supplier' => 'Coca Cola',
                'alamat' => 'Jl. Soda No. 78, Jakarta',
                'no_telp' => '021-9990001',
                'email' => 'contact@cocacola.co.id',
                'catatan' => 'Produsen minuman bersoda',
            ],
            [
                'nama_produsen_supplier' => 'Mayora',
                'alamat' => 'Jl. Snack No. 90, Tangerang',
                'no_telp' => '021-1112233',
                'email' => 'info@mayora.com',
                'catatan' => 'Produsen makanan dan minuman',
            ],
            [
                'nama_produsen_supplier' => 'Nestle',
                'alamat' => 'Jl. Susu No. 23, Jakarta',
                'no_telp' => '021-4445566',
                'email' => 'contact@nestle.co.id',
                'catatan' => 'Produsen makanan dan minuman',
            ],
            [
                'nama_produsen_supplier' => 'You C1000',
                'alamat' => 'Jl. Vitamin No. 45, Jakarta',
                'no_telp' => '021-7778899',
                'email' => 'info@youc1000.com',
                'catatan' => 'Produsen minuman vitamin',
            ],
            [
                'nama_produsen_supplier' => 'Ultra Jaya',
                'alamat' => 'Jl. Susu No. 67, Bandung',
                'no_telp' => '022-5556677',
                'email' => 'contact@ultrajaya.com',
                'catatan' => 'Produsen susu dan minuman',
            ],
        ];

        foreach ($producers as $producer) {
            Producer::create($producer);
        }
    }
}
