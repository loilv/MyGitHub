<?php

namespace App\Http\Resources;

use App\Constants\OrderPackageCode;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageResource extends JsonResource
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
//        if ($this->type == OrderPackageCode::TYPE_NEWS) {
//            $price = $this->price.'/tháng';
//        } else {
//            $price = $this->price.'/'.$this->unit.' sản phẩm';
//        }
        return [
            'id'     => $this->id,
            'name'   => $this->name,
            'price'  => $this->price,
            'unit'   => $this->unit,
            'type'   => $this->type,
            'limit'  => $this->limit,
            'status' => $this->status,
        ];
    }
}
