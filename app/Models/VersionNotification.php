<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VersionNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'title',
        'description',
        'features',
        'release_date',
        'is_active',
        'priority'
    ];

    protected $casts = [
        'features' => 'array',
        'release_date' => 'date',
        'is_active' => 'boolean'
    ];

    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';

    const PRIORITIES = [
        self::PRIORITY_LOW => 'Low',
        self::PRIORITY_MEDIUM => 'Medium', 
        self::PRIORITY_HIGH => 'High'
    ];

    public function userNotifications()
    {
        return $this->hasMany(UserVersionNotification::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('release_date', 'desc');
    }

    public function getPriorityBadgeAttribute()
    {
        $badges = [
            'low' => 'bg-secondary',
            'medium' => 'bg-warning',
            'high' => 'bg-danger'
        ];

        return $badges[$this->priority] ?? 'bg-secondary';
    }

    public function getPriorityDisplayAttribute()
    {
        return self::PRIORITIES[$this->priority] ?? ucfirst($this->priority);
    }
}