<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ItemSale extends Model
{
    use HasFactory;

    protected $table = 'sales';

    protected $fillable = [
        'customer_id',
        'code',
        'total',
        'discount',
        'paid_amount',
        'sale_date'
    ];

    protected $casts = [
        'sale_date' => 'date',
        'total' => 'decimal:2',
        'discount' => 'decimal:2',
        'paid_amount' => 'decimal:2'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }


    // Static methods for calculations
    public static function getTotalSalesRevenue()
    {
        return self::sum('total') ?: 0;
    }

    public static function getTotalSalesRevenueThisMonth()
    {
        return self::whereMonth('sale_date', Carbon::now()->month)
            ->whereYear('sale_date', Carbon::now()->year)
            ->sum('total') ?: 0;
    }

    public static function getMonthlyRevenueData()
    {
        $months = [];
        $revenues = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M Y');

            $monthlyRevenue = self::whereMonth('sale_date', $date->month)
                ->whereYear('sale_date', $date->year)
                ->sum('total') ?: 0;

            $revenues[] = $monthlyRevenue;
        }

        return [
            'months' => $months,
            'revenues' => $revenues
        ];
    }
}