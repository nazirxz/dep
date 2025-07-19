<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_kategori',
        'deskripsi'
    ];

    public function incomingItems()
    {
        return $this->hasMany(IncomingItem::class);
    }

    public function outgoingItems()
    {
        return $this->hasMany(OutgoingItem::class);
    }

    public function verificationItems()
    {
        return $this->hasMany(VerificationItem::class);
    }
} 