<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sale extends Model
{
    use HasFactory , SoftDeletes;

    protected $fillable = ['code','customer_id', 'customer_type', 'sale_date', 'total', 'discount', 'paid_amount'];

    protected $dates = ['sale_date'];

     public function orderItems() // if your pivot table is named 'orders'
    {
        return $this->hasMany(Order::class); // or OrderItem::class
    }

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
