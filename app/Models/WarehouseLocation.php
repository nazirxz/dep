<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_name',
        'max_capacity',
        'current_capacity',
    ];

    protected $casts = [
        'max_capacity' => 'integer',
        'current_capacity' => 'integer',
    ];

    /**
     * Check if location can accommodate additional items
     */
    public function canAccommodate($quantity)
    {
        return ($this->current_capacity + $quantity) <= $this->max_capacity;
    }

    /**
     * Get available capacity
     */
    public function getAvailableCapacity()
    {
        return $this->max_capacity - $this->current_capacity;
    }

    /**
     * Get capacity percentage
     */
    public function getCapacityPercentage()
    {
        if ($this->max_capacity == 0) {
            return 0;
        }
        return round(($this->current_capacity / $this->max_capacity) * 100, 2);
    }

    /**
     * Update current capacity based on incoming items
     */
    public function updateCurrentCapacity()
    {
        $this->current_capacity = IncomingItem::where('lokasi_rak_barang', $this->location_name)
            ->where('jumlah_barang', '>', 0)
            ->sum('jumlah_barang');
        $this->save();
    }

    /**
     * Relationship with incoming items
     */
    public function incomingItems()
    {
        return $this->hasMany(IncomingItem::class, 'lokasi_rak_barang', 'location_name');
    }
}