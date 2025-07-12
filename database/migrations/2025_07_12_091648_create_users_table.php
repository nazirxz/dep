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

        // Migrasi untuk tabel password_reset_tokens dan sessions biasanya juga ada di sini
        // Anda bisa menyalinnya dari migrasi awal yang tadi dihapus jika diperlukan
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};