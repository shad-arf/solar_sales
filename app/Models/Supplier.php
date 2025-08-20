<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'tax_id',
        'notes',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all purchases from this supplier
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get only active suppliers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get supplier's purchase history with totals
     */
    public function getPurchaseStatsAttribute()
    {
        return [
            'total_purchases' => $this->purchases()->count(),
            'total_amount' => $this->purchases()->sum('total_amount'),
            'last_purchase_date' => $this->purchases()->latest('purchase_date')->value('purchase_date'),
        ];
    }

    /**
     * Get formatted contact information
     */
    public function getContactInfoAttribute()
    {
        $contact = [];
        if ($this->contact_person) $contact[] = $this->contact_person;
        if ($this->phone) $contact[] = $this->phone;
        if ($this->email) $contact[] = $this->email;
        
        return implode(' • ', $contact);
    }
}