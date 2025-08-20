<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'item_id',
        'quantity_purchased',
        'purchase_price',
        'line_total'
    ];

    protected $casts = [
        'quantity_purchased' => 'integer',
        'purchase_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    /**
     * Get the purchase this item belongs to
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Get the item details
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Calculate and set the line total
     */
    public function calculateLineTotal()
    {
        $this->line_total = $this->quantity_purchased * $this->purchase_price;
        return $this;
    }

    /**
     * Boot method to automatically calculate line total
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($purchaseItem) {
            $purchaseItem->calculateLineTotal();
        });

        static::saved(function ($purchaseItem) {
            // Update the purchase total when an item is saved
            $purchaseItem->purchase->updateTotalAmount();
        });

        static::deleted(function ($purchaseItem) {
            // Update the purchase total when an item is deleted
            $purchaseItem->purchase->updateTotalAmount();
        });
    }

    /**
     * Get the unit price vs selling price comparison
     */
    public function getProfitMarginAttribute()
    {
        $sellingPrice = $this->item->primary_price ?? 0;
        if ($this->purchase_price > 0) {
            return (($sellingPrice - $this->purchase_price) / $this->purchase_price) * 100;
        }
        return 0;
    }
}