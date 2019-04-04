<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsBidding extends Model
{
    protected $table = 'news_biddings';
    protected $guarded = ['id'];
    public $timestamps = false;

    public function userBidding()
    {
        return $this->hasMany('App\Models\UserBidding', 'bidding_id', 'id');
    }

    public function biddingToUser()
    {
        return $this->belongsTo('App\Models\User', 'partner');
    }
}
