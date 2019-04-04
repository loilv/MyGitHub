<?php

namespace App\Http\Resources;

use App\Constants\DefineCode;
use App\Models\Taxonomy;
use App\Models\User;
use App\Models\UserInvestor;
use App\Models\UserProject;
use Illuminate\Http\Resources\Json\JsonResource;

class NewProjectDetail extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $user = auth('api')->user();
        $status_follow = $user && UserProject::where('user_id', $user->id)
            ->where('project_id', $this->id)
            ->where('role_name_id', 0)
            ->first() ? DefineCode::FOLLOW : DefineCode::NO_FOLLOW;
        $city = isset($this->city_id) ? Taxonomy::select('id', 'name')
            ->where('id', $this->city_id)
            ->first() : null;
        $district = isset($this->city_id) ? Taxonomy::select('id', 'name')
            ->where('id', $this->district_id)
            ->first() : null;

        $partner = UserProject::where('project_id', $this->id)
            ->join('users', 'user_projects.user_id', '=', 'users.id')
            ->select(
                'users.id as users_id',
                'users.name as user_name',
                'users.email as user_email',
                'users.phone as user_phone',
                'users.fax as user_fax',
                'users.address as user_address',
                'users.company as user_company',
                'users.position as user_position',
                'user_projects.role_name_id as user_sub'
            )
            ->get();

        /** define response partner */
        $current_partner = [];
        foreach ($partner as $value) {
            $user_follow = $user && UserInvestor::where('user_id', $user->id)
                ->where('investor_id', $value->users_id)
                ->first() ? DefineCode::FOLLOW : DefineCode::NO_FOLLOW;

            $current_partner[$value->users_id]['user_id'] = $value->users_id;
            $current_partner[$value->users_id]['user_name'] = $value->user_name;
            $current_partner[$value->users_id]['user_email'] = $value->user_email;
            $current_partner[$value->users_id]['user_phone'] = $value->user_phone;
            $current_partner[$value->users_id]['user_fax'] = $value->user_fax;
            $current_partner[$value->users_id]['user_address'] = $value->user_address;
            $current_partner[$value->users_id]['user_company'] = $value->user_company;
            $current_partner[$value->users_id]['user_position'] = $value->user_position;
            $current_partner[$value->users_id]['follow'] = $user_follow;

            if (!isset($current_partner[$value->users_id]['user_sub'])) {
                $current_partner[$value->users_id]['user_sub'] = $value->user_sub;
            } else {
                $current_partner[$value->users_id]['user_sub'] .= ', ' . $value->user_sub;
            }
        }
        $current_partner = array_values($current_partner);
        /** end */

        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'name_description' => $this->name_description,
            'description'      => strip_tags($this->description),
            'project_code'     => $this->project_code,
            'value'            => $this->value,
            'status'           => $this->status,
            'owner_type'       => $this->owner_type,
            'dev_type'         => $this->dev_type,
            'site_area'        => $this->site_area,
            'floor_area'       => $this->floor_area,
            'storeys'          => $this->storeys,
            'time_start'       => $this->time_start,
            'time_end'         => $this->time_end,
            'time'             => $this->time,
            'country'          => $this->country,
            'address'          => $this->address . ' - ' . @$district->name . ' - ' . @$city->name,
            'follow'           => $status_follow,
            'partner'          => $current_partner,
        ];
    }
}
