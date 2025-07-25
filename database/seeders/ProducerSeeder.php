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
                'kontak_whatsapp' => '628115556677',
                'alamat' => 'Jl. Industri No. 123, Jakarta',
                'no_telp' => '628115556677',
                'email' => 'info@indofood.com',
                'catatan' => 'Produsen makanan dan minuman',
            ],
            [
                'nama_produsen_supplier' => 'Orang Tua Group',
                'kontak_whatsapp' => '628117778899',
                'alamat' => 'Jl. Manufaktur No. 45, Tangerang',
                'no_telp' => '628117778899',
                'email' => 'contact@ot.co.id',
                'catatan' => 'Produsen makanan ringan',
            ],
            [
                'nama_produsen_supplier' => 'PT Petra',
                'kontak_whatsapp' => '628123334455',
                'alamat' => 'Jl. Cokelat No. 67, Bandung',
                'no_telp' => '628123334455',
                'email' => 'info@petra.com',
                'catatan' => 'Produsen cokelat',
            ],
            [
                'nama_produsen_supplier' => 'Glico',
                'kontak_whatsapp' => '628316667788',
                'alamat' => 'Jl. Snack No. 89, Surabaya',
                'no_telp' => '628316667788',
                'email' => 'sales@glico.co.id',
                'catatan' => 'Produsen snack',
            ],
            [
                'nama_produsen_supplier' => 'Danone',
                'kontak_whatsapp' => '628118889900',
                'alamat' => 'Jl. Minuman No. 12, Jakarta',
                'no_telp' => '628118889900',
                'email' => 'info@danone.co.id',
                'catatan' => 'Produsen minuman',
            ],
            [
                'nama_produsen_supplier' => 'Sosro',
                'kontak_whatsapp' => '628114445566',
                'alamat' => 'Jl. Teh No. 34, Bekasi',
                'no_telp' => '628114445566',
                'email' => 'contact@sosro.com',
                'catatan' => 'Produsen minuman teh',
            ],
            [
                'nama_produsen_supplier' => 'Amerta Indah Otsuka',
                'kontak_whatsapp' => '628112223344',
                'alamat' => 'Jl. Kesehatan No. 56, Jakarta',
                'no_telp' => '628112223344',
                'email' => 'info@aio.co.id',
                'catatan' => 'Produsen minuman kesehatan',
            ],
            [
                'nama_produsen_supplier' => 'Coca Cola',
                'kontak_whatsapp' => '628119990001',
                'alamat' => 'Jl. Soda No. 78, Jakarta',
                'no_telp' => '628119990001',
                'email' => 'contact@cocacola.co.id',
                'catatan' => 'Produsen minuman bersoda',
            ],
            [
                'nama_produsen_supplier' => 'Mayora',
                'kontak_whatsapp' => '628111112233',
                'alamat' => 'Jl. Snack No. 90, Tangerang',
                'no_telp' => '628111112233',
                'email' => 'info@mayora.com',
                'catatan' => 'Produsen makanan dan minuman',
            ],
            [
                'nama_produsen_supplier' => 'Nestle',
                'kontak_whatsapp' => '628114445566',
                'alamat' => 'Jl. Susu No. 23, Jakarta',
                'no_telp' => '628114445566',
                'email' => 'contact@nestle.co.id',
                'catatan' => 'Produsen makanan dan minuman',
            ],
            [
                'nama_produsen_supplier' => 'You C1000',
                'kontak_whatsapp' => '628117778899',
                'alamat' => 'Jl. Vitamin No. 45, Jakarta',
                'no_telp' => '628117778899',
                'email' => 'info@youc1000.com',
                'catatan' => 'Produsen minuman vitamin',
            ],
            [
                'nama_produsen_supplier' => 'Ultra Jaya',
                'kontak_whatsapp' => '628225556677',
                'alamat' => 'Jl. Susu No. 67, Bandung',
                'no_telp' => '628225556677',
                'email' => 'contact@ultrajaya.com',
                'catatan' => 'Produsen susu dan minuman',
            ],
        ];

        foreach ($producers as $producer) {
            Producer::create($producer);
        }
    }
}
