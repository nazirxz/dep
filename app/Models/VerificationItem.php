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
        'category_id',
        'tanggal_masuk_barang',
        'jumlah_barang',
        'satuan_barang',
        'lokasi_rak_barang',
        'producer_id',
        'metode_bayar',
        'pembayaran_transaksi',
        'nota_transaksi',
        'foto_barang',
        'kondisi_fisik',
        'catatan_verifikasi',
        'is_verified',
        'verified_by',
        'verified_at',
        'incoming_item_id'
    ];

    protected $casts = [
        'tanggal_masuk_barang' => 'date',
        'verified_at' => 'datetime',
        'is_verified' => 'boolean',
        'pembayaran_transaksi' => 'string',
        'nota_transaksi' => 'string',
        'foto_barang' => 'string',
    ];

    /**
     * Get the producer that owns this verification item.
     */
    public function producer()
    {
        return $this->belongsTo(Producer::class);
    }

    /**
     * Get the category that owns this verification item.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the incoming item associated with this verification.
     */
    public function incomingItem()
    {
        return $this->belongsTo(IncomingItem::class);
    }

    /**
     * Get the user who verified this item.
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
