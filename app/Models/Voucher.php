<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'max_discount',
        'min_purchase',
        'usage_limit',
        'used_count',
        'usage_limit_per_user',
        'is_active',
        'valid_from',
        'valid_until',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'usage_limit_per_user' => 'integer',
        'is_active' => 'boolean',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Check if voucher is valid
     */
    public function isValid($subtotal = 0): array
    {
        $now = Carbon::now();

        // Check if active
        if (!$this->is_active) {
            return ['valid' => false, 'message' => 'Voucher tidak aktif'];
        }

        // Check date validity
        if ($now->lt($this->valid_from)) {
            return ['valid' => false, 'message' => 'Voucher belum dapat digunakan'];
        }

        if ($now->gt($this->valid_until)) {
            return ['valid' => false, 'message' => 'Voucher sudah expired'];
        }

        // Check usage limit
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return ['valid' => false, 'message' => 'Voucher sudah mencapai batas penggunaan'];
        }

        // Check minimum purchase
        if ($subtotal < $this->min_purchase) {
            return ['valid' => false, 'message' => "Minimum pembelian Rp " . number_format($this->min_purchase, 0, ',', '.')];
        }

        return ['valid' => true, 'message' => 'Voucher valid'];
    }

    /**
     * Calculate discount amount
     */
    public function calculateDiscount($subtotal, $shippingCost = 0): float
    {
        switch ($this->discount_type) {
            case 'percentage':
                $discount = ($subtotal * $this->discount_value / 100);
                return $this->max_discount ? min($discount, $this->max_discount) : $discount;

            case 'fixed_amount':
                return min($this->discount_value, $subtotal);

            case 'free_shipping':
                return $shippingCost;

            default:
                return 0;
        }
    }

    /**
     * Increment usage count
     */
    public function incrementUsage()
    {
        $this->increment('used_count');
    }

    /**
     * Scope for active vouchers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('valid_from', '<=', Carbon::now())
                    ->where('valid_until', '>=', Carbon::now());
    }

    /**
     * Scope for available vouchers (not reached usage limit)
     */
    public function scopeAvailable($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('usage_limit')
              ->orWhereRaw('used_count < usage_limit');
        });
    }
}
