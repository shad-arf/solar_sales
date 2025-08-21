<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_id',
        'description',
        'debit_amount',
        'credit_amount',
        'transaction_date',
        'reference_number',
        'transaction_type'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'debit_amount' => 'decimal:2',
        'credit_amount' => 'decimal:2'
    ];

    const TYPES = [
        'revenue' => 'Revenue',
        'expense' => 'Expense',
        'owner_investment' => 'Owner Investment',
        'owner_drawing' => 'Owner Drawing',
        'purchase' => 'Purchase',
        'sale' => 'Sale',
        'other' => 'Other'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('transaction_date', Carbon::now()->month)
                    ->whereYear('transaction_date', Carbon::now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('transaction_date', Carbon::now()->year);
    }
}