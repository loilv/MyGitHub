<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
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
        $full_path = @$request->root() . '/uploads/' . @$this->path . '/' . @$this->name;
        return [
            'id'        => @$this->id,
            'full_path' => @$full_path,
        ];
    }
}
