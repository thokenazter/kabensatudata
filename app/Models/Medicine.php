<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    protected $fillable = [
        'name',
        'generic_name',
        'stock_quantity',
        'unit',
        'minimum_stock',
        'strength',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the medicine usages for this medicine.
     */
    public function medicineUsages(): HasMany
    {
        return $this->hasMany(MedicineUsage::class);
    }

    /**
     * Check if medicine is low stock
     */
    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->minimum_stock;
    }

    /**
     * Check if medicine is out of stock
     */
    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    /**
     * Reduce stock quantity
     */
    public function reduceStock(int $quantity): bool
    {
        if ($this->stock_quantity >= $quantity) {
            $this->stock_quantity -= $quantity;
            return $this->save();
        }
        return false;
    }

    /**
     * Add stock quantity
     */
    public function addStock(int $quantity): bool
    {
        $this->stock_quantity += $quantity;
        return $this->save();
    }

    /**
     * Get formatted medicine name with strength
     */
    public function getFullNameAttribute(): string
    {
        $name = $this->name;
        if ($this->strength) {
            $name .= ' ' . $this->strength;
        }
        return $name;
    }

    /**
     * Get stock status
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return 'Habis';
        } elseif ($this->isLowStock()) {
            return 'Stok Menipis';
        }
        return 'Tersedia';
    }

    /**
     * Scope for active medicines
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for available medicines (active and in stock)
     */
    public function scopeAvailable($query)
    {
        return $query->active()->where('stock_quantity', '>', 0);
    }

    /**
     * Scope for low stock medicines
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('stock_quantity <= minimum_stock');
    }
}