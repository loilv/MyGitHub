<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProduct extends Model
{
    protected $table = 'user_products';
    public $timestamps = false;

    public function userProduct()
    {
        return $this->belongsTo('App\Models\SellProduct', 'product_id', 'id');
    }
}
