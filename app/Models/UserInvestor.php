<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserInvestor extends Model
{
    protected $guarded = ['id'];
    public $timestamps = false;

    public function getUserInvestor()
    {
        return $this->belongsTo('App\Models\User', 'investor_id');
    }
}
