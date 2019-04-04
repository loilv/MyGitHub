<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $table = 'packages';
    protected $guarded = ['id'];

    public function orderPackage()
    {
        return $this->hasMany('App\Models\OrderPackage', 'package_id', 'package_id');
    }
}
