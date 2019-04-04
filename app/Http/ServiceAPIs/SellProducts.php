<?php

namespace App\Http\ServiceAPIs;

use App\Constants\DocumentCode;
use App\Models\SellProduct;
use JWTAuth;
use Illuminate\Http\Request;

class SellProducts
{
    /**
     * Function get list product on user
     *
     * @param $request
     *
     * @return mixed
     */
    public function getListProducts(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $user_id = $user->id;
        $item = 20;
        $skip = isset($request->page) && $request->page ? $item * $request->page : 0;

        $products = SellProduct::select(
            'sell_products.id as product_id',
            'product_code',
            'sell_products.name as product_name',
            'price',
            'images.name as image_name',
            'images.path as image_path'
        )
            ->where('user_id', $user_id)
            ->leftJoin('images', function ($join) {
                $join->on('sell_products.id', '=', 'images.commom_id')->limit(1);
            })
            ->take($item)
            ->skip($skip)
            ->orderby('sell_products.id', 'DESC')
            ->get();

        foreach ($products as $product) {
            if ($product->image_path != null && $product->image_name != null) {
                $product->full_path = $request->root()."/uploads/".$product->image_path."/".$product->image_name;
            } else {
                $product->full_path = DocumentCode::IMAGE_DEFAULT;
            }
        }
        return $products;
    }
}
