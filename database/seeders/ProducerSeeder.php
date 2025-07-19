<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Producer;

class ProducerSeeder extends Seeder
{
    public function run()
    {
        $producers = [
            [
                'nama_produsen_supplier' => 'PT. Elektronik Jaya',
                'kontak_whatsapp' => '6281234567890',
                'alamat' => 'Jl. Elektronik No. 123, Jakarta',
                'no_telp' => '021-1234567',
                'email' => 'info@elektronikjaya.com',
                'catatan' => 'Supplier elektronik utama',
            ],
            [
                'nama_produsen_supplier' => 'CV. Furniture Indah',
                'kontak_whatsapp' => '628765432109',
                'alamat' => 'Jl. Mebel No. 45, Bandung',
                'no_telp' => '022-9876543',
                'email' => 'sales@furnitureindah.com',
                'catatan' => 'Produsen furniture berkualitas',
            ],
            [
                'nama_produsen_supplier' => 'Distributor IT Cepat',
                'kontak_whatsapp' => '6285000111222',
                'alamat' => 'Jl. Komputer No. 67, Surabaya',
                'no_telp' => '031-5556677',
                'email' => 'order@itcepat.com',
                'catatan' => 'Distributor perangkat IT',
            ],
            [
                'nama_produsen_supplier' => 'Grosir Alat Tulis',
                'kontak_whatsapp' => '6281122334455',
                'alamat' => 'Jl. Pendidikan No. 89, Yogyakarta',
                'no_telp' => '0274-112233',
                'email' => 'sales@grosiratk.com',
                'catatan' => 'Supplier alat tulis kantor',
            ],
            [
                'nama_produsen_supplier' => 'Pemasok Bahan Bangunan',
                'kontak_whatsapp' => '6281345678901',
                'alamat' => 'Jl. Material No. 34, Semarang',
                'no_telp' => '024-8899001',
                'email' => 'order@bahanbangunan.com',
                'catatan' => 'Supplier material bangunan',
            ],
            [
                'nama_produsen_supplier' => 'Produsen Pakaian Fashion',
                'kontak_whatsapp' => '6282233445566',
                'alamat' => 'Jl. Mode No. 56, Malang',
                'no_telp' => '0341-334455',
                'email' => 'info@fashionindo.com',
                'catatan' => 'Produsen pakaian dan fashion',
            ],
        ];

        foreach ($producers as $producer) {
            Producer::create($producer);
        }
    }
}
