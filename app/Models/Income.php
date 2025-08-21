<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'description',
        'category',
        'date',
        'reference_number'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date'
    ];

    const CATEGORIES = [
        'solar_sales' => 'Solar Panel Sales',
        'installation' => 'Installation Services',
        'maintenance' => 'Maintenance Services', 
        'consultation' => 'Consultation Fees',
        'other' => 'Other Income'
    ];

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('date', Carbon::now()->month)
                    ->whereYear('date', Carbon::now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('date', Carbon::now()->year);
    }

    public static function getTotalThisMonth()
    {
        return self::thisMonth()->sum('amount');
    }

    public static function getTotalThisYear()
    {
        return self::thisYear()->sum('amount');
    }
}