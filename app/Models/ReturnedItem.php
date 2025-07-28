<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'order_item_id',
        'user_id',
        'incoming_item_id',
        'nama_barang',
        'kategori_barang',
        'jumlah_barang',
        'nama_produsen',
        'alasan_pengembalian',
        'foto_bukti',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'order_id' => 'integer',
        'order_item_id' => 'integer',
        'user_id' => 'integer',
        'incoming_item_id' => 'integer',
        'jumlah_barang' => 'integer',
    ];

    /**
     * Relasi ke Order (nullable untuk pergantian barang dari pengecer)
     */
    public function order()
    {
        return $this->belongsTo(Order::class)->withDefault();
    }

    /**
     * Relasi ke OrderItem (nullable untuk pergantian barang dari pengecer)
     */
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class)->withDefault();
    }

    /**
     * Relasi ke User (nullable untuk pergantian barang dari pengecer)
     */
    public function user()
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    /**
     * Relasi ke IncomingItem (untuk pergantian barang)
     */
    public function incomingItem()
    {
        return $this->belongsTo(IncomingItem::class)->withDefault();
    }

    /**
     * Relasi ke Producer melalui IncomingItem
     */
    public function producer()
    {
        return $this->hasOneThrough(
            Producer::class,
            IncomingItem::class,
            'id', // Foreign key on incoming_items table
            'id', // Foreign key on producers table
            'incoming_item_id', // Local key on returned_items table
            'producer_id' // Local key on incoming_items table
        );
    }

    /**
     * Scope untuk filter berdasarkan tipe pengembalian
     */
    public function scopeFromOrder($query)
    {
        return $query->whereNotNull('order_id');
    }

    /**
     * Scope untuk pergantian barang (dari pengecer/supplier)
     */
    public function scopePergantianBarang($query)
    {
        return $query->whereNull('order_id');
    }

    /**
     * Accessor untuk menentukan tipe pengembalian
     */
    public function getTipeAttribute()
    {
        return $this->order_id ? 'order' : 'pergantian';
    }
}
