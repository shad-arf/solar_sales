<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Expense extends Model
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
        'inventory' => 'Inventory Purchase',
        'rent' => 'Rent',
        'utilities' => 'Utilities',
        'fuel' => 'Fuel & Vehicle',
        'marketing' => 'Marketing & Advertising',
        'office_supplies' => 'Office Supplies',
        'equipment' => 'Equipment',
        'salaries' => 'Salaries & Wages',
        'insurance' => 'Insurance',
        'other' => 'Other Expenses'
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