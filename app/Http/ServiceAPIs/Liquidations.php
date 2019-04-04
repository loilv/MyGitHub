<?php

namespace App\Http\ServiceAPIs;

use App\Constants\DefineCode;
use App\Models\Image;
use App\Models\Post;
use App\Models\SellProduct;
use Carbon\Carbon;
use JWTAuth;
use App\Models\Liquidation;

class Liquidations
{
    /**
     * Function post product liquidation
     *
     * @param $request
     *
     * @return mixed
     */
    public function createPostLiquidation($request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $now = Carbon::now();
        $post_code = 'TL' . $now->year . '-' . strtoupper(str_random(6));
        $user_id = $user->id;
        $image = $request->file('file');
        $price = $request->price ? preg_replace('/[^0-9.]/', '', $request->price) : null;
        $time = $request->time ? Carbon::createFromFormat('d/m/Y', $request
            ->time)->format('Y-m-d') : '';
        $request->merge([
            'post_code' => $post_code,
            'price'     => $price,
            'time'      => $time,
            'user_id'   => $user_id,
            'status'    => DefineCode::STATUS_SELL_LIQUIDATION,
        ]);

        $liquidation = Post::create($request->except('file'));

        if ($image) {
            $path = 'uploads/liquidation';
            foreach ($image as $key => $img) {
                $data = \Func::uploadImage($img, $path);
                $data_image = [
                    'name'      => $data['image'],
                    'path'      => 'liquidation',
                    'commom_id' => $liquidation->id,
                ];
                Image::create($data_image);
            }
        }
        return $liquidation;
    }
}
