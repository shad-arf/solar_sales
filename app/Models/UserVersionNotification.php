<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVersionNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'version_notification_id',
        'viewed_at',
        'dismissed_at'
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
        'dismissed_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function versionNotification()
    {
        return $this->belongsTo(VersionNotification::class);
    }

    public function scopeViewed($query)
    {
        return $query->whereNotNull('viewed_at');
    }

    public function scopeDismissed($query)
    {
        return $query->whereNotNull('dismissed_at');
    }

    public function scopePending($query)
    {
        return $query->whereNull('viewed_at');
    }

    public function markAsViewed()
    {
        if (!$this->viewed_at) {
            $this->update(['viewed_at' => now()]);
        }
    }

    public function markAsDismissed()
    {
        $this->update(['dismissed_at' => now()]);
    }
}