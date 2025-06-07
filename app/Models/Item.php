<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory ,SoftDeletes;

    protected $fillable = ['name', 'code', 'price' , 'quantity', 'base_price' , 'operator_price'];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
