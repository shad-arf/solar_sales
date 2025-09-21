<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['name', 'code', 'price', 'quantity', 'base_price', 'operator_price', 'description'];

    protected $casts = [
        'price' => 'decimal:2',
        'base_price' => 'decimal:2',
        'operator_price' => 'decimal:2',
        'quantity' => 'integer'
    ];

    public function sales()
    {
        return $this->hasManyThrough(Sale::class, Order::class, 'item_id', 'id', 'id', 'sale_id');
    }

    public function orderItems()
    {
        return $this->hasMany(Order::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Get all prices for this item
     */
    public function prices(): HasMany
    {
        return $this->hasMany(ItemPrice::class);
    }

    /**
     * Get all prices for this item (alias for compatibility)
     */
    public function itemPrices(): HasMany
    {
        return $this->hasMany(ItemPrice::class);
    }

    /**
     * Get active prices for this item
     */
    public function activePrices(): HasMany
    {
        return $this->hasMany(ItemPrice::class)->active()->ordered();
    }

    /**
     * Get the default price for this item
     */
    public function defaultPrice()
    {
        return $this->hasOne(ItemPrice::class)->where('is_default', true)->where('is_active', true);
    }

    /**
     * Get formatted stock status
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->quantity == 0) {
            return 'Out of Stock';
        } elseif ($this->quantity < 10) {
            return 'Low Stock';
        } elseif ($this->quantity > 100) {
            return 'Overstocked';
        }
        return 'In Stock';
    }

    /**
     * Get stock status badge class
     */
    public function getStockBadgeClassAttribute(): string
    {
        if ($this->quantity == 0) {
            return 'bg-danger';
        } elseif ($this->quantity < 10) {
            return 'bg-warning text-dark';
        } elseif ($this->quantity > 100) {
            return 'bg-info';
        }
        return 'bg-success';
    }

    /**
     * Get the primary price (either from item_prices or fallback to item.price)
     */
    public function getPrimaryPriceAttribute(): float
    {
        $defaultPrice = $this->defaultPrice;
        if ($defaultPrice) {
            return $defaultPrice->price;
        }
        return $this->price ?? 0;
    }

    /**
     * Get total value based on primary price
     */
    public function getTotalValueAttribute(): float
    {
        return $this->primary_price * $this->quantity;
    }
}
