<?php

namespace App\Http\Resources;

use App\Helpers\Functions;
use App\Models\Image;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificatonResource extends JsonResource
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
        $user = new ImageResource(Image::where('commom_id', $this->sender_id)->first());
        $time = Functions::getDateFormatAgo($this->created_at);
        return [
            'id'        => $this->id,
            'message'   => $this->message,
            'common_id' => $this->common_id,
            'type'      => $this->type,
            'status'    => $this->status,
            'time'      => $time,
            'image'     => $user,
        ];
    }
}
