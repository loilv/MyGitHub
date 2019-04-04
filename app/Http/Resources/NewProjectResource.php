<?php

namespace App\Http\Resources;

use App\Models\UserProject;
use Illuminate\Http\Resources\Json\JsonResource;

class NewProjectResource extends JsonResource
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
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'name_description' => $this->name_description,
            'status'           => $this->status,
            'type_project'     => $this->type_project,
            'time_start'       => $this->time_start,
            'time_end'         => $this->time_end,
            'address'          => $this->address,
        ];
    }
}
