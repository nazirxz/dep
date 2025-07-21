<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'estimated_days_min',
        'estimated_days_max',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'estimated_days_min' => 'integer',
        'estimated_days_max' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope for active shipping methods
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return $this->price > 0 ? 'Rp ' . number_format($this->price, 0, ',', '.') : 'Gratis';
    }

    /**
     * Get estimated delivery text
     */
    public function getEstimatedDeliveryAttribute(): string
    {
        if ($this->estimated_days_min === $this->estimated_days_max) {
            return $this->estimated_days_min . ' hari';
        }
        
        return $this->estimated_days_min . '-' . $this->estimated_days_max . ' hari';
    }
}
