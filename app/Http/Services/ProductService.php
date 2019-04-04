<?php

namespace App\Http\Services;

use App\Constants\NotificationCode;
use App\Constants\OrderPackageCode;
use App\Http\Resources\ProductResource;
use App\Models\Notification;
use App\Models\OrderPackage;
use App\Models\UserInvestor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Image;
use App\Models\Post;
use App\Models\SellProduct;
use App\Models\PostImage;
use App\Constants\DefineCode;

class ProductService
{
    /*
     * Function create post product
     */
    public function createPostProduct($request)
    {
        $now = Carbon::now();
        $post_code = 'P' . $now->year . '-' . strtoupper(str_random(6));
        $image = $request->file('file');
        $time = Carbon::createFromFormat('d/m/Y', $request->time)->format('Y-m-d');
        $request->merge([
            'time'        => $time,
            'post_code'   => $post_code,
            'description' => $request->description ? html_entity_decode($request->description) : '',
        ]);
        $post = Post::create($request->except('file'));
        if ($image) {
            $path = 'uploads/post_products';
            foreach ($image as $key => $img) {
                $data = \Func::uploadImage($img, $path);
                $imggg = [
                    'name'      => $data['image'],
                    'path'      => 'post_products',
                    'commom_id' => $post->id,
                ];
                Image::create($imggg);
            }
        }

        $check_investor = UserInvestor::where('investor_id', $post->user_id)->first();
        if (isset($check_investor) && $check_investor->getUserInvestor) {
            $arr = [
                'message'           => 'Nhà thầu ' . $check_investor->getUserInvestor->name . ' vừa đăng tin mua',
                'user_id'           => $check_investor->user_id,
                'common_id'         => $post->id,
                'type'              => 'post',
                'status'            => NotificationCode::UNREAD,
                'sender_id'         => $check_investor->investor_id,
                'type_notification' => NotificationCode::TYPE_FOLLOW,
            ];
            Notification::create($arr);
        }

        return $post;
    }

    /*
     * Function Update Post Product
     */
    public function updatePostProduct($request, $post)
    {
        $tblImage = Image::where('commom_id', $post->id)->get()->toArray();
        $tblImage = array_column($tblImage, 'id');
        $cc = $request->image;
        if ($cc) {
            $a = array_unique($cc);
            $b = array_diff($tblImage, $a);
            foreach ($b as $k => $v) {
                $del = Image::where('id', $v)->first();
                \File::delete('uploads/' . $del->path . '/' . $del->name);
                Image::destroy($v);
            }
        } else {
            foreach ($tblImage as $k => $v) {
                $del = Image::where('id', $v)->first();
                \File::delete('uploads/' . $del->path . '/' . $del->name);
                Image::destroy($v);
            }
        }
        $image = $request->file('file');
        $data = [
            'post_code'   => $request->post_code,
            'type'        => $request->type,
            'user_id'     => $request->user_id,
            'title'       => $request->title,
            'category_id' => $request->category_id,
            'time'        => $request->time ? Carbon::createFromFormat('d/m/Y', $request->time)
                ->format('Y-m-d') : '',
            'description' => $request->description ? html_entity_decode($request->description) : '',
        ];
        $post->update($data);

        if ($image) {
            $path = 'uploads/post_products';
            foreach ($image as $key => $img) {
                $data = \Func::uploadImage($img, $path);
                $imgg = [
                    'name'      => $data['image'],
                    'path'      => 'post_products',
                    'commom_id' => $post->id,
                ];
                Image::create($imgg);
            }
        }
    }

    /*
     * Function delete post product
     */
    public function deletePostProduct($id)
    {
        $data = Post::find($id);
        if ($data->postToImage) {
            foreach ($data->postToImage as $item) {
                \File::delete('uploads/' . $item->path . '/' . $item->name);
                Image::destroy($item->id);
            }
        }
        Post::destroy($id);
    }

    /*
     * Function create sell product
     */
    public function createSellProduct($request)
    {
        $package = OrderPackage::select(\DB::raw('user_id, sum(`limit`) as total'))
            ->where('user_id', $request->user_id)
            ->where('type', OrderPackageCode::TYPE_PRODUCT)->groupBy('user_id')->first();
//        $package = OrderPackage::where('user_id', $request->user_id)->where('type', OrderPackageCode::TYPE_PRODUCT)
//            ->where('status', 1)->get();
        $sold = SellProduct::where('user_id', $request->user_id)->count();
        if (isset($package) && $package->total > $sold) {
            $now = Carbon::now();
            $product_code = 'SP' . $now->year . '-' . strtoupper(str_random(6));
            $image = $request->file('file');
            $time_start = Carbon::createFromFormat('d/m/Y', $request->time_start)->format('Y-m-d');
            $time_end = Carbon::createFromFormat('d/m/Y', $request->time_end)->format('Y-m-d');
            $price = $request->price ? preg_replace('/[^0-9.]/', '', $request->price) : null;
            $price_reduced = $request->price_reduced ? preg_replace('/[^0-9.]/', '', $request->price_reduced) : null;
            $request->merge([
                'product_code'  => $product_code,
                'time_start'    => $time_start,
                'time_end'      => $time_end,
                'price'         => $price,
                'price_reduced' => $price_reduced,
                'description'   => $request->description ? html_entity_decode($request->description) : '',
            ]);
            $sell = SellProduct::create($request->except('file'));

            if ($image) {
                $path = 'uploads/sell_products';
                foreach ($image as $key => $img) {
                    $data = \Func::uploadImage($img, $path);
                    $data_image = [
                        'name'      => $data['image'],
                        'path'      => 'sell_products',
                        'commom_id' => $sell->id,
                    ];
                    Image::create($data_image);
                }
            }

            //update status package expired
            $sold = SellProduct::where('user_id', $request->user_id)->count();
            $minus = $package->total - $sold;
            if ($minus == 0) {
                $change = OrderPackage::where('user_id', $sell->user_id)
                    ->where('type', OrderPackageCode::TYPE_PRODUCT)
                    ->where('status', OrderPackageCode::STATUS_ORDER_PAID)
                    ->first();
                $change->status = OrderPackageCode::STATUS_ORDER_EXPIRED;
                $change->save();
            }

            $check_investor = UserInvestor::where('investor_id', $sell->user_id)->first();
            if (isset($check_investor) && $check_investor->getUserInvestor) {
                $arr = [
                    'message'           => 'Nhà thầu ' . $check_investor->getUserInvestor->name . ' vừa đăng tin bán',
                    'user_id'           => $check_investor->user_id,
                    'common_id'         => $sell->id,
                    'type'              => 'sellproduct',
                    'status'            => NotificationCode::UNREAD,
                    'sender_id'         => $check_investor->investor_id,
                    'type_notification' => NotificationCode::TYPE_FOLLOW,
                ];
                Notification::create($arr);
            }

            return $sell;
        }
    }

    /*
     * Function Update Sell Product
     */
    public function updateSellProduct($request, $sell)
    {
        $tblImage = Image::where('commom_id', $sell->id)->get()->toArray();
        $tblImage = array_column($tblImage, 'id');
        $cc = $request->image;
        if ($cc) {
            $a = array_unique($cc);
            $b = array_diff($tblImage, $a);
            foreach ($b as $k => $v) {
                $del = Image::where('id', $v)->first();
                \File::delete('uploads/' . $del->path . '/' . $del->name);
                Image::destroy($v);
            }
        } else {
            foreach ($tblImage as $k => $v) {
                $del = Image::where('id', $v)->first();
                \File::delete('uploads/' . $del->path . '/' . $del->name);
                Image::destroy($v);
            }
        }
        $image = $request->file('file');
        $data = [
            'product_code'        => $request->product_code,
            'name'                => $request->name,
            'price'               => $request->price ?
                preg_replace('/[^0-9.]/', '', $request->price) : null,
            'price_reduced'       => $request->price_reduced ?
                preg_replace('/[^0-9.]/', '', $request->price_reduced) : null,
            'amount_sell'         => $request->amount_sell,
            'amount_remain'       => $request->amount_remain,
            'time_start'          => $request->time_start ? Carbon::createFromFormat('d/m/Y', $request
                ->time_start)->format('Y-m-d') : '',
            'time_end'            => $request->time_end ? Carbon::createFromFormat('d/m/Y', $request->time_end)
                ->format('Y-m-d') : '',
            'status'              => $request->status,
            'category_id'         => $request->category_id,
            'address'             => $request->address,
            'manufacturer'        => @$request->manufacturer,
            'year_of_manufacture' => @$request->year_of_manufacture,
            'user_id'             => $request->user_id,
            'description'         => $request->description ? html_entity_decode($request->description) : '',
            'city_id'             => $request->city_id,
            'district_id'         => $request->district_id,
        ];
        $sell->update($data);

        if ($image) {
            $path = 'uploads/sell_products';
            foreach ($image as $key => $img) {
                $data = \Func::uploadImage($img, $path);
                $imgg = [
                    'name'      => $data['image'],
                    'path'      => 'sell_products',
                    'commom_id' => $sell->id,
                ];
                Image::create($imgg);
            }
        }
    }

    /*
     * Function delete sell product
     */
    public function deleteSellProduct($id)
    {
        $data = SellProduct::find($id);
        if ($data->sellToImage) {
            foreach ($data->sellToImage as $item) {
                \File::delete('uploads/' . $item->path . '/' . $item->name);
                Image::destroy($item->id);
            }
        }
        SellProduct::destroy($id);
    }

    public function productDetail($product_id)
    {
        $product = new ProductResource(SellProduct::find($product_id));

        return $product;
    }
}
