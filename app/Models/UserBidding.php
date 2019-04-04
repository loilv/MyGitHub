<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBidding extends Model
{
    protected $table = 'user_biddings';
    protected $guarded = ['id'];
    public $timestamps = false;

    public function newBidding()
    {
        return $this->belongsTo('App\Models\NewsBidding', 'bidding_id', 'id');
    }
}
