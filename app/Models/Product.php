<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $guarded = ['id'];

    public function productToImage()
    {
        return $this->hasOne('App\Models\Image', 'commom_id', 'id')
            ->where('path', '=', 'products');
    }

    public function productToCategory()
    {
        return $this->belongsTo('App\Models\Category', 'category_id');
    }
}
