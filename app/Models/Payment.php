<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;
    protected $fillable = ['amount', 'paid_at', 'sale_id', 'note'];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

}
