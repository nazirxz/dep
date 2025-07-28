<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ReturnedItem;
use App\Models\IncomingItem;
use App\Models\User;

class ReturnedItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed beberapa contoh pergantian barang dari pengecer/supplier
        $incomingItems = IncomingItem::limit(3)->get();
        $staffUser = User::where('role', 'staff')->first();

        if ($incomingItems->count() > 0) {
            foreach ($incomingItems as $index => $item) {
                ReturnedItem::create([
                    'order_id' => null, // Pergantian barang, bukan dari order
                    'order_item_id' => null, // Pergantian barang, bukan dari order
                    'user_id' => $staffUser ? $staffUser->id : null, // Staff yang menangani
                    'incoming_item_id' => $item->id,
                    'nama_barang' => $item->nama_barang,
                    'kategori_barang' => $item->kategori_barang,
                    'jumlah_barang' => rand(1, min(5, $item->jumlah_barang)),
                    'nama_produsen' => $item->producer ? $item->producer->nama_produsen_supplier : 'Tidak Diketahui',
                    'alasan_pengembalian' => [
                        'Barang rusak saat pengiriman dari supplier',
                        'Kualitas tidak sesuai standar yang diminta',
                        'Barang tidak sesuai dengan spesifikasi yang dipesan'
                    ][$index % 3],
                    'foto_bukti' => null, // Bisa ditambahkan foto bukti jika ada
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now()->subDays(rand(1, 30)),
                ]);
            }

            $this->command->info('Created ' . $incomingItems->count() . ' sample returned items for pergantian barang.');
        } else {
            $this->command->warn('No incoming items found. Please seed incoming items first.');
        }
    }
}
