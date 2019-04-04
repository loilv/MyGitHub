<?php

namespace App\Http\Resources;

use App\Constants\DefineCode;
use App\Models\UserDocument;
use Illuminate\Http\Resources\Json\JsonResource;

class OtherResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $user = auth('api')->user();
        if ($this->type == 'video') {
            $link = $this->link_id;
        } else {
            $link = $request->root() . '/uploads/document/' . $this->link_id;
        }
        $status_follow = $user && UserDocument::where('user_id', $user->id)
            ->where('document_id', $this->id)
            ->first() ? DefineCode::FOLLOW : DefineCode::NO_FOLLOW;

        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'date'        => $this->date,
            'description' => $this->description,
            'link'        => $link,
            'type'        => $this->type,
            'created_at'  => $this->created_at,
            'follow'      => $status_follow,
        ];
    }
}
