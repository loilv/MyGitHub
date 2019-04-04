<?php

namespace App\Http\Resources;

use App\Constants\DocumentCode;
use App\Helpers\Functions;
use Illuminate\Http\Resources\Json\JsonResource;

class LiquidationResource extends JsonResource
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
        $image = $this->getImages->first();
        $full_path = ($image) ?
            $request->root() . '/uploads/' . $image->path . '/' . $image->name : DocumentCode::IMAGE_DEFAULT;
        $time = Functions::getDateFormatAgo($this->created_at);

        return [
            'name'        => $this->name,
            'description' => $this->description,
            'city_name'   => $this->getNameCity,
            'price'       => $this->price,
            'image'       => $full_path,
            'time'        => $time,
        ];
    }
}
