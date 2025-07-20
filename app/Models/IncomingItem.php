<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_barang',
        'kategori_barang',
        'category_id',
        'tanggal_masuk_barang',
        'jumlah_barang',
        'lokasi_rak_barang',
        'producer_id',
        'metode_bayar',
        'pembayaran_transaksi',
        'nota_transaksi',
        'foto_barang',
        'kondisi_fisik',
        'catatan',
        'harga_jual',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'tanggal_masuk_barang' => 'date',
        'harga_jual' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function producer()
    {
        return $this->belongsTo(Producer::class, 'producer_id');
    }
}
