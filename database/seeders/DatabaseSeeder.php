<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Truncate all tables
        DB::table('verifikasi_barang')->truncate();
        DB::table('incoming_items')->truncate();
        DB::table('outgoing_items')->truncate();
        DB::table('producers')->truncate();
        DB::table('categories')->truncate();
        DB::table('users')->truncate();

        // Run seeders in correct order
        $this->call([
            UserTableSeeder::class,
            CategorySeeder::class,
            ProducerSeeder::class,
            IncomingItemSeeder::class,
            OutgoingItemSeeder::class,
            VerificationItemSeeder::class
        ]);

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
