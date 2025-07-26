<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Auto-incrementing ID
            $table->string('full_name'); // Nama Lengkap
            $table->string('email')->unique(); // Email (harus unik)
            $table->string('username')->unique()->nullable(); // Username (harus unik, bisa kosong)
            $table->string('password'); // Password
            $table->timestamp('email_verified_at')->nullable(); // Untuk verifikasi email (opsional)
            $table->string('role')->default('user'); // Role (default 'user')
            $table->string('phone_number')->nullable(); // No HP (bisa kosong)
            $table->rememberToken(); // Untuk fitur "remember me"
            $table->timestamps(); // created_at (Created Date) dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};