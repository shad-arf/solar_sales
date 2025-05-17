<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory , SoftDeletes;

    protected $fillable = ['item_id', 'customer_id', 'quantity', 'paid', 'date' ,'total' , 'discount'];

    protected $dates = ['date'];

    public function item()
    {
        return $this->belongsTo(Item::class)->withTrashed();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function totalPaid(): float
    {
        return $this->payments()->sum('amount');
    }


}
