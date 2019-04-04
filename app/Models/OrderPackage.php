<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPackage extends Model
{
    protected $table = 'order_packages';
    protected $guarded = ['id'];

    public function orderToUser()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}
