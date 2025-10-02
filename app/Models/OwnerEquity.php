<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OwnerEquity extends Model
{
    use HasFactory;

    protected $table = 'owner_equity';

    protected $fillable = [
        'owner_id',
        'type',
        'amount',
        'description',
        'transaction_date',
        'reference_number'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date'
    ];

    const TYPES = [
        'investment' => 'Owner Investment',
        'drawing' => 'Owner Drawing'
    ];

    public function scopeInvestments($query)
    {
        return $query->where('type', 'investment');
    }

    public function scopeDrawings($query)
    {
        return $query->where('type', 'drawing');
    }

    public static function getTotalInvestments()
    {
        return self::investments()->sum('amount') ?: 0;
    }

    public static function getTotalDrawings()
    {
        return self::drawings()->sum('amount') ?: 0;
    }

    public static function getNetEquity()
    {
        return self::getTotalInvestments() - self::getTotalDrawings();
    }

    public function owner()
    {
        return $this->belongsTo(Owner::class);
    }

    public function getOwnerNameAttribute()
    {
        if ($this->owner) {
            return $this->owner->name;
        }
        
        // Fallback for legacy records without owner
        return 'Unknown Owner';
    }
}