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
                'nama_kategori' => 'Minuman Ringan',
                'deskripsi' => 'Produk minuman dan minuman ringan',
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