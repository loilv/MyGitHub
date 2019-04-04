<?php

namespace App\Http\Resources;

use App\Constants\DefineCode;
use App\Models\UserBidding;
use Illuminate\Http\Resources\Json\JsonResource;

class NewBiddingDetail extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $user = auth('api')->user();
        $status_follow = $user && UserBidding::where('user_id', $user->id)
            ->where('bidding_id', $this->id)
            ->first() ? DefineCode::FOLLOW  : DefineCode::NO_FOLLOW;
        $data = $this->resource;

        if ($data) {
            $data->bidding_document = strip_tags($this->bidding_document);
            $data->follow = $status_follow;
        }
        return $data;
    }
}
