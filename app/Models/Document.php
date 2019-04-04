<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $table = 'documents';
    protected $guarded = ['id'];

    public function userDocument()
    {
        return $this->hasMany('App\Models\UserDocument', 'document_id', 'id');
    }
}
