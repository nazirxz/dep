<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserTableSeeder::class, // Panggil seeder user Anda di sini
            // Jika Anda memiliki seeder lain (misalnya ProductSeeder, CategorySeeder),
            // Anda bisa menambahkannya di sini juga:
            // ProductSeeder::class,
            // CategorySeeder::class,
        ]);
    }
}