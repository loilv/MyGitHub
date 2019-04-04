<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsProject extends Model
{
    protected $table = 'news_projects';
    protected $guarded = ['id'];
    public $timestamps = false;

    /*
     * Function get project to userproject
     */
    public function projectToUserProject()
    {
        return $this->hasMany('App\Models\UserProject', 'project_id', 'id');
    }

    /*
     * Function get project to taxonomy city_id
     */
    public function projectToCity()
    {
        return $this->belongsTo('App\Models\Taxonomy', 'city_id');
    }

    /*
     * Function get project to taxonomy district_id
     */
    public function projectToDistrict()
    {
        return $this->belongsTo('App\Models\Taxonomy', 'district_id');
    }
}
