<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class InventoryAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'system_quantity',
        'actual_quantity',
        'adjustment_quantity',
        'adjustment_type',
        'reason',
        'notes',
        'adjustment_date',
        'financial_impact'
    ];

    protected $casts = [
        'adjustment_date' => 'date',
        'financial_impact' => 'decimal:2'
    ];

    const REASONS = [
        'damaged' => 'Damaged Items',
        'lost' => 'Lost Items',
        'theft' => 'Theft',
        'found' => 'Found Items',
        'recount' => 'Physical Recount',
        'other' => 'Other'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // Calculate adjustment details before saving
    protected static function booted()
    {
        static::creating(function ($adjustment) {
            $adjustment->adjustment_quantity = $adjustment->actual_quantity - $adjustment->system_quantity;
            $adjustment->adjustment_type = $adjustment->adjustment_quantity >= 0 ? 'increase' : 'decrease';
            
            // Calculate financial impact based on item price
            $item = Item::find($adjustment->item_id);
            if ($item) {
                $adjustment->financial_impact = abs($adjustment->adjustment_quantity) * ($item->price ?? 0);
            }
        });

        static::updating(function ($adjustment) {
            $adjustment->adjustment_quantity = $adjustment->actual_quantity - $adjustment->system_quantity;
            $adjustment->adjustment_type = $adjustment->adjustment_quantity >= 0 ? 'increase' : 'decrease';
            
            // Calculate financial impact based on item price
            $item = Item::find($adjustment->item_id);
            if ($item) {
                $adjustment->financial_impact = abs($adjustment->adjustment_quantity) * ($item->price ?? 0);
            }
        });
    }

    public static function getTotalFinancialImpact()
    {
        return self::sum('financial_impact') ?: 0;
    }

    public static function getTotalFinancialImpactThisMonth()
    {
        return self::whereMonth('adjustment_date', Carbon::now()->month)
            ->whereYear('adjustment_date', Carbon::now()->year)
            ->sum('financial_impact') ?: 0;
    }

    public static function getAdjustmentsByReason()
    {
        return self::selectRaw('reason, COUNT(*) as count, SUM(ABS(adjustment_quantity)) as total_quantity, SUM(financial_impact) as total_impact')
            ->groupBy('reason')
            ->get();
    }
}