<?php

namespace App\Http\ServiceAPIs;

use App\Models\NewsProject;
use App\Models\UserProject;

class NewProject
{
    /**
     * Functions get detailed project information
     * @param $project_id
     *
     * @return mixed
     */
    public function projectDetail($project_id)
    {
        $project = NewsProject::find($project_id);
        if ($project) {
            $user = UserProject::where('project_id', $project->id)
                ->join('users', 'user_projects.user_id', '=', 'users.id')
                ->join('taxonomies', 'user_projects.role_name_id', '=', 'taxonomies.id')
                ->select(
                    'users.id as user_id',
                    'users.name as user_name',
                    'phone',
                    'fax',
                    'address',
                    'email',
                    'company',
                    'position',
                    'taxonomies.name as sub'
                )
                ->get();

            $project->partner = @$user;
        }
        return $project;
    }
}
