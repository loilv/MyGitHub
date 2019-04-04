<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDocument extends Model
{
    protected $table = 'user_documents';
    protected $guarded = ['id'];
    public $timestamps = false;
}
