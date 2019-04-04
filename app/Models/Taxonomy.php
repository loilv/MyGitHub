<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Taxonomy extends Model
{
    protected $table = 'taxonomies';
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at'];
}
