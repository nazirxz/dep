<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'nama_kategori' => 'Makanan',
                'deskripsi' => 'Produk makanan dan makanan ringan',
            ],
            [
                'nama_kategori' => 'Minuman',
                'deskripsi' => 'Produk minuman dan minuman ringan',
            ],
            [
                'nama_kategori' => 'Elektronik',
                'deskripsi' => 'Perangkat elektronik dan aksesoris',
            ],
            [
                'nama_kategori' => 'Pakaian',
                'deskripsi' => 'Pakaian dan aksesoris fashion',
            ],
            [
                'nama_kategori' => 'Alat Tulis',
                'deskripsi' => 'Perlengkapan kantor dan alat tulis',
            ],
            [
                'nama_kategori' => 'Pembersih',
                'deskripsi' => 'Produk pembersih dan perawatan',
            ],
            [
                'nama_kategori' => 'Kesehatan',
                'deskripsi' => 'Produk kesehatan dan obat-obatan',
            ],
            [
                'nama_kategori' => 'Peralatan',
                'deskripsi' => 'Peralatan dan perkakas',
            ],
            [
                'nama_kategori' => 'Perlengkapan Mandi',
                'deskripsi' => 'Produk perlengkapan mandi dan kebersihan pribadi',
            ],
            [
                'nama_kategori' => 'Makanan Ringan',
                'deskripsi' => 'Produk makanan ringan dan snack',
            ],
            [
                'nama_kategori' => 'Lainnya',
                'deskripsi' => 'Kategori lainnya',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
} 