<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EmailVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'otp',
        'expires_at',
        'is_verified',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_verified' => 'boolean',
    ];

    public function isExpired()
    {
        return Carbon::now()->isAfter($this->expires_at);
    }

    public function isValid($otp)
    {
        return $this->otp === $otp && !$this->isExpired() && !$this->is_verified;
    }

    public static function generateOTP()
    {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public static function createForEmail($email)
    {
        // Delete existing OTPs for this email
        self::where('email', $email)->delete();

        // Create new OTP
        return self::create([
            'email' => $email,
            'otp' => self::generateOTP(),
            'expires_at' => Carbon::now()->addMinutes(10), // 10 minutes expiry
        ]);
    }
}