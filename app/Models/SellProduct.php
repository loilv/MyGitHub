<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellProduct extends Model
{
    protected $table = 'sell_products';
    protected $guarded = ['id'];

    public function sellToCategory()
    {
        return $this->belongsTo('App\Models\Category', 'category_id', 'id');
    }

    public function sellToUser()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function sellToImage()
    {
        return $this->hasMany('App\Models\Image', 'commom_id', 'id')
            ->where('path', '=', 'sell_products');
    }

    public function getNameCity()
    {
        return $this->belongsTo('App\Models\Taxonomy', 'city_id')
            ->where('type', '=', 'city');
    }
}
