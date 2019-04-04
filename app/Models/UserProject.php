<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProject extends Model
{
    protected $table = 'user_projects';
    protected $guarded = ['id'];
    public $timestamps = false;

    /*
     * Function get userproject to project
     */
    public function userprojectToProject()
    {
        return $this->belongsTo('App\Models\NewsProject', 'project_id');
    }

    /*
     * Function get userproject to user
     */
    public function userprojectToUser()
    {
        return $this->belongsTo('App\Models\User', 'user_id');
    }

    /*
     * Function get userproject to role
     */
    public function userprojectToRole()
    {
        return $this->belongsTo('App\Models\Taxonomy', 'role_name_id');
    }

    public function newProject()
    {
        return $this->belongsTo('App\Models\NewsProject', 'project_id', 'id');
    }
}
