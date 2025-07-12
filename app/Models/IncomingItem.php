<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class IncomingItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'incoming_items'; // Nama tabel di database

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_barang',
        'kategori_barang',
        'tanggal_masuk_barang',
        'jumlah_barang',
        'status_barang',
        'lokasi_rak_barang', // Tambahkan kolom baru
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_masuk_barang' => 'date', // Mengubah tanggal menjadi objek Carbon
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    // Hapus atau komentari metode booted() ini untuk menghindari konflik dengan controller
    // protected static function booted()
    // {
    //     static::saving(function ($item) {
    //         if ($item->jumlah_barang <= 0) {
    //             $item->status_barang = 'Habis';
    //         } elseif ($item->jumlah_barang < 5) {
    //             $item->status_barang = 'Sedikit';
    //         } else {
    //             $item->status_barang = 'Banyak';
    //         }
    //     });
    // }
}
