<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VerificationItem;
use App\Models\Producer;
use App\Models\Category;
use Carbon\Carbon;

class JsonDataSeeder extends Seeder
{
    public function run()
    {
        $jsonPath = base_path('data_barang.json');
        $jsonData = json_decode(file_get_contents($jsonPath), true);

        if (empty($jsonData)) {
            throw new \Exception('No data found in data_barang.json');
        }

        // Get all producers and categories
        $producers = Producer::all()->keyBy('nama_produsen_supplier');
        $categories = Category::all()->keyBy('nama_kategori');

        // First, collect all unique categories from JSON
        $uniqueCategories = collect($jsonData)
            ->pluck('kategori_barang')
            ->unique()
            ->values()
            ->all();

        // Create any missing categories
        foreach ($uniqueCategories as $categoryName) {
            $category = Category::firstOrCreate(
                ['nama_kategori' => $categoryName],
                ['deskripsi' => 'Kategori ' . $categoryName]
            );
            $categories->put($categoryName, $category);
        }

        foreach ($jsonData as $item) {
            // Get category (it should exist now)
            $category = $categories->get($item['kategori_barang']);
            if (!$category) {
                \Log::warning('Category not found: ' . $item['kategori_barang']);
                continue;
            }

            // Find producer
            $producer = $producers->get($item['nama_produsen_supplier']);
            if (!$producer) {
                $producer = Producer::create([
                    'nama_produsen_supplier' => $item['nama_produsen_supplier'],
                    'alamat' => 'Alamat akan diupdate',
                    'no_telp' => 'Nomor telepon akan diupdate',
                    'email' => strtolower(str_replace(' ', '', $item['nama_produsen_supplier'])) . '@example.com',
                    'catatan' => 'Data dari import JSON'
                ]);
                $producers->put($item['nama_produsen_supplier'], $producer);
            }

            // Create verification item
            try {
                VerificationItem::create([
                    'nama_barang' => $item['nama_barang'],
                    'kategori_barang' => $item['kategori_barang'],
                    'category_id' => $category->id,
                    'tanggal_masuk_barang' => Carbon::now(),
                    'jumlah_barang' => $item['jumlah_barang'],
                    'lokasi_rak_barang' => $item['lokasi_rak_barang'],
                    'producer_id' => $producer->id,
                    'metode_bayar' => $item['metode_bayar'],
                    'pembayaran_transaksi' => $item['pembayaran_transaski'] === 'null' ? null : $item['pembayaran_transaski'],
                    'nota_transaksi' => $item['nota_transaksi'] === 'null' ? null : $item['nota_transaksi'],
                    'foto_barang' => $item['foto_barang'],
                    'is_verified' => false,
                    'kondisi_fisik' => 'Baik',
                    'catatan_verifikasi' => 'Data dari import JSON'
                ]);
            } catch (\Exception $e) {
                \Log::error('Error creating verification item: ' . $e->getMessage(), [
                    'item' => $item,
                    'category_id' => $category->id,
                    'producer_id' => $producer->id
                ]);
            }
        }
    }
} 