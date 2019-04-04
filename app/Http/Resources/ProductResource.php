<?php

namespace App\Http\Resources;

use App\Models\Category;
use App\Models\Image;
use App\Models\SellProduct;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
        $user = $this->sellToUser->first();
        $image = ImageResource::collection(Image::where('commom_id', $this->id)->get());
        $related_products = RelatedProductResource::collection(SellProduct::where('category_id', $this->category_id)
        ->limit(10)->get());
        $category = Category::select('id', 'name')->where('id', $this->category_id)->first();
        return [
            'id'                  => $this->id,
            'product_code'        => $this->product_code,
            'name'                => $this->name,
            'price'               => $this->price,
            'price_reduced'       => $this->price_reduced,
            'amount_sell'         => $this->amount_sell,
            'amount_remain'       => $this->amount_remain,
            'time_start'          => $this->time_start,
            'time_end'            => $this->time_end,
            'status'              => $this->status,
            'category_id'         => $category ? $category->name : '',
            'address'             => $this->address,
            'manufacturer'        => $this->manufacturer,
            'year_of_manufacture' => $this->year_of_manufacture,
            'description'         => strip_tags($this->description),
            'user_id'             => $this->user_id,
            'user_phone'          => $user->phone,
            'image'               => $image,
            'related_products'    => $related_products,
        ];
    }
}
