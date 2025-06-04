<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;
    protected $fillable = ['sale_id', 'item_id', 'quantity', 'discount', 'line_total','unit_price', 'line_discount', 'status', 'note'];


    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class)->withTrashed(); // if soft deletes on items
    }
}


