<?php

namespace App\Http\Resources;

use App\Constants\DocumentCode;
use Illuminate\Http\Resources\Json\JsonResource;

class ListProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $image = $this->sellToImage->first();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'image' => $image ? $request->root()
                . '/uploads/'
                . $image->path
                . '/'
                . $image->name
                : DocumentCode::IMAGE_DEFAULT,
        ];
    }
}
