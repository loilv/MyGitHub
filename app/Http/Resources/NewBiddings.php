<?php

namespace App\Http\Resources;

use App\Constants\DefineCode;
use App\Models\UserBidding;
use Illuminate\Http\Resources\Json\JsonResource;

class NewBiddings extends JsonResource
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
        $status_follow = $user ? UserBidding::where('user_id', $user->id)
            ->where('bidding_id', $this->id)
            ->first() : null;
        $status_follow = $status_follow && $status_follow != null ? DefineCode::FOLLOW : DefineCode::NO_FOLLOW;
        return [
            'id'         => $this->id,
            'code'       => $this->number_tbmt,
            'name'       => $this->name_bidding,
            'time'       => $this->time_action,
            'partner'    => $this->partner,
            'time_start' => $this->time_start,
            'time_end'   => $this->time_end,
            'follow'     => $status_follow,
        ];
    }
}
