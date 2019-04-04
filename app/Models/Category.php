<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at'];

    /*
     * Function get parent_id to id Category
     */
    public function subToCategory()
    {
        return $this->belongsTo('App\Models\Category', 'parent_id', 'id');
    }
}
