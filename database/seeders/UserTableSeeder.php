<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User; // Pastikan untuk meng-import model User
use Illuminate\Support\Facades\Hash; // Untuk meng-hash password

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Contoh membuat satu user Admin
        User::create([
            'full_name' => 'Administrator Utama',
            'email' => 'admin@example.com',
            'username' => 'admin_udks',
            'password' => Hash::make('password123'), // Ganti dengan password yang kuat
            'role' => 'admin',
            'phone_number' => '081234567890',
        ]);

        // Contoh membuat beberapa user biasa menggunakan factory
        User::factory(5)->create([
            'role' => 'user',
        ]);

        // Contoh membuat user lain dengan role berbeda
        User::create([
            'full_name' => 'Manajer Proyek',
            'email' => 'manager@example.com',
            'username' => 'manager_udks',
            'password' => Hash::make('manager123'),
            'role' => 'manager',
            'phone_number' => '087654321098',
        ]);
    }
}