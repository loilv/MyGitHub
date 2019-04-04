<?php

namespace App\Http\ServiceAPIs;

use App\Constants\OrderPackageCode;
use App\Models\OrderPackage;
use App\Models\Package;

class PackageAPI
{
    /**
     * Function create order package on user
     *
     * @param $request
     *
     * @return |null
     */
    public function orderPackage($request)
    {
        $data = [];
        $user = auth('api')->user();
        $order = null;
        foreach ($request->package_id as $p) {
            $package = Package::find($p);
            if ($package) {
                $data['name']       = $package->name;
                $data['price']      = $package->price;
                $data['unit']       = $package->unit;
                $data['type']       = $package->type;
                $data['limit']      = '';
                $data['status']     = OrderPackageCode::STATUS_ORDER_UNPAID;
                $data['user_id']    = $user->id;
                $data['package_id'] = $package->package_id;

                $order[] = OrderPackage::create($data);
            }
        }
        return $order;
    }

    /**
     * Function get list package order on user
     *
     * @param $request
     *
     * @return array
     */
    public function payDetail($request)
    {
        $data = [];
        $pay_detail = [];
        foreach ($request->package_id as $p) {
            $package = Package::find($p);
            if ($package) {
                $data['id'] = $package->id;
                $data['name'] = $package->name;
                $data['price'] = $package->price;

                $pay_detail[] = $data;
            }
        }
        return $pay_detail;
    }
}
