<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class VerificationItem extends Model
{
    use HasFactory;

    protected $table = 'verifikasi_barang';

    protected $fillable = [
        'nama_barang',
        'kategori_barang',
        'tanggal_masuk_barang',
        'jumlah_barang',
        'satuan_barang',
        'lokasi_rak_barang',
        'nama_produsen',
        'metode_bayar',
        'pembayaran_transaksi',
        'nota_transaksi',
        'foto_barang',
        'kondisi_fisik',
        'catatan_verifikasi',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'tanggal_masuk_barang' => 'date',
        'verified_at' => 'datetime',
        'pembayaran_transaksi' => 'string',
        'nota_transaksi' => 'string',
        'foto_barang' => 'string',
    ];
}
