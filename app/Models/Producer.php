<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producer extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_produsen_supplier',
        'kontak_whatsapp',
        'alamat',
        'no_telp',
        'email',
        'catatan',
    ];

    /**
     * Get the incoming items for this producer.
     */
    public function incomingItems()
    {
        return $this->hasMany(IncomingItem::class);
    }

    /**
     * Get the outgoing items for this producer.
     */
    public function outgoingItems()
    {
        return $this->hasMany(OutgoingItem::class);
    }

    /**
     * Get the verification items for this producer.
     */
    public function verificationItems()
    {
        return $this->hasMany(VerificationItem::class);
    }
}
