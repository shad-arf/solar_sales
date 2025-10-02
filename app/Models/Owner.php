<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Owner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'ownership_percentage',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'ownership_percentage' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    public function ownerEquities()
    {
        return $this->hasMany(OwnerEquity::class);
    }

    public function investments()
    {
        return $this->hasMany(OwnerEquity::class)->where('type', 'investment');
    }

    public function drawings()
    {
        return $this->hasMany(OwnerEquity::class)->where('type', 'drawing');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getTotalInvestmentsAttribute()
    {
        return $this->investments()->sum('amount') ?: 0;
    }

    public function getTotalDrawingsAttribute()
    {
        return $this->drawings()->sum('amount') ?: 0;
    }

    public function getNetEquityAttribute()
    {
        return $this->total_investments - $this->total_drawings;
    }

    public function getStatusBadgeAttribute()
    {
        return $this->is_active 
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-secondary">Inactive</span>';
    }

    public function getOwnershipDisplayAttribute()
    {
        return number_format($this->ownership_percentage, 1) . '%';
    }

    public static function getTotalInvestments()
    {
        return OwnerEquity::where('type', 'investment')->sum('amount') ?: 0;
    }

    public static function getTotalDrawings()
    {
        return OwnerEquity::where('type', 'drawing')->sum('amount') ?: 0;
    }

    public static function getNetEquity()
    {
        return self::getTotalInvestments() - self::getTotalDrawings();
    }
}