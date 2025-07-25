<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PengecerUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pengecerUsers = [
            [
                'full_name' => 'Budi Santoso',
                'username' => 'budi_pengecer',
                'email' => 'budi.santoso@example.com',
                'password' => Hash::make('password123'),
                'role' => 'pengecer',
                'phone_number' => '081234567890',
            ],
            [
                'full_name' => 'Siti Nurhaliza',
                'username' => 'siti_retail',
                'email' => 'siti.nurhaliza@example.com',
                'password' => Hash::make('password123'),
                'role' => 'pengecer',
                'phone_number' => '081234567891',
            ],
            [
                'full_name' => 'Ahmad Wijaya',
                'username' => 'ahmad_toko',
                'email' => 'ahmad.wijaya@example.com',
                'password' => Hash::make('password123'),
                'role' => 'pengecer',
                'phone_number' => '081234567892',
            ],
            [
                'full_name' => 'Rina Maharani',
                'username' => 'rina_shop',
                'email' => 'rina.maharani@example.com',
                'password' => Hash::make('password123'),
                'role' => 'pengecer',
                'phone_number' => '081234567893',
            ],
            [
                'full_name' => 'Dedi Kurniawan',
                'username' => 'dedi_store',
                'email' => 'dedi.kurniawan@example.com',
                'password' => Hash::make('password123'),
                'role' => 'pengecer',
                'phone_number' => '081234567894',
            ],
        ];

        foreach ($pengecerUsers as $userData) {
            // Check if user already exists by email or username
            $existingUser = User::where('email', $userData['email'])
                               ->orWhere('username', $userData['username'])
                               ->first();
            
            if (!$existingUser) {
                User::create($userData);
            }
        }
    }
}