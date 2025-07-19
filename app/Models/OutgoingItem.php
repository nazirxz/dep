<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OutgoingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'incoming_item_id',
        'nama_barang',
        'kategori_barang',
        'category_id',
        'tanggal_keluar_barang',
        'jumlah_barang',
        'lokasi_rak_barang',
        'tujuan_distribusi',
        'producer_id',
        'metode_bayar',
        'pembayaran_transaksi',
        'nota_transaksi',
        'foto_barang',
    ];

    protected $casts = [
        'tanggal_keluar_barang' => 'date',
    ];

    /**
     * Get the producer that owns this item.
     */
    public function producer()
    {
        return $this->belongsTo(Producer::class);
    }

    /**
     * Get the category that owns this item.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function incomingItem()
    {
        return $this->belongsTo(IncomingItem::class);
    }
}
