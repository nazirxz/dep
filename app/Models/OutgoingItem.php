<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OutgoingItem extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'outgoing_items'; // Nama tabel di database

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_barang',
        'kategori_barang',
        'tanggal_keluar_barang',
        'jumlah_barang',
        'tujuan_distribusi',
        'lokasi_rak_barang', // Tambahkan kolom baru
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_keluar_barang' => 'date', // Mengubah tanggal menjadi objek Carbon
    ];
}
