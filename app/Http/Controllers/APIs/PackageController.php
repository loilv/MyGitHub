<?php

namespace App\Http\Controllers\APIs;

use App\Constants\OrderPackageCode;
use App\Constants\ResponseStatusCode;
use App\Http\Resources\PackageResource;
use App\Models\OrderPackage;
use App\Models\Package;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\ServiceAPIs\PackageAPI;

class PackageController extends Controller
{
    public $package;

    public function __construct(PackageAPI $package)
    {
        $this->package = $package;
    }

    /**
     * Function get all package
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListPackage()
    {
        $user = auth('api')->user();
        $package = Package::select('id', 'name', 'price', 'unit', 'type', 'package_id')
            ->orderBy('type', 'desc')
            ->get();
        $product = $user->product;
        $now = now()->format('Y-m-d H:i:s');
        if ($package) {
            foreach ($package as $value) {
                if ($value->type == OrderPackageCode::TYPE_NEWS) {
                    $value->end_date = 0;
                    $order = OrderPackage::where('package_id', $value->package_id)
                        ->where('type', OrderPackageCode::TYPE_NEWS)
                        ->where('user_id', $user->id)
                        ->where('limit', '>', $now)
                        ->first();
                    if ($order) {
                        $value->end_date = $order->limit;
                    }
                } elseif ($value->type == OrderPackageCode::TYPE_PRODUCT) {
                    $value->end_date = 0;
                    $order = OrderPackage::where('package_id', $value->package_id)
                        ->where('type', OrderPackageCode::TYPE_PRODUCT)
                        ->where('user_id', $user->id)
                        ->where('limit', '>', count($product))
                        ->first();
                    if ($order) {
                        $value->end_date = $order->limit - count($product);
                    }
                }
            }
        }

        if (count($package)) {
            return response()->json([
                'code' => ResponseStatusCode::OK,
                'data' => $package,
            ]);
        } else {
            return response()->json([
                'code'    => ResponseStatusCode::NO_CONTENT,
                'message' => 'No-content',
            ]);
        }
    }

    /**
     * Function create order package on user
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function orderPackage(Request $request)
    {
        $order = $this->package->orderPackage($request);

        if (!empty($order)) {
            return response()->json([
                'code' => ResponseStatusCode::OK,
                'data' => $order,
            ]);
        } else {
            return response()->json([
                'code'    => ResponseStatusCode::NOT_FOUND,
                'message' => 'Package not found',
            ]);
        }
    }

    /**
     * Function get list package order on user
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function payDetail(Request $request)
    {
        $pay = $this->package->payDetail($request);

        if (!empty($pay)) {
            return response()->json([
                'code' => ResponseStatusCode::OK,
                'data' => $pay,
            ]);
        } else {
            return response()->json([
                'code' => ResponseStatusCode::NOT_FOUND,
                'data' => 'Package not found',
            ]);
        }
    }

    /**
     * Function get history order package on user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHistory()
    {
        $user = auth('api')->user();
        $list_history = PackageResource::collection(OrderPackage::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->paginate(20));

        if (count($list_history)) {
            return response()->json([
                'code' => ResponseStatusCode::OK,
                'data' => $list_history,
            ]);
        } else {
            return response()->json([
                'code'    => ResponseStatusCode::NO_CONTENT,
                'message' => 'NO CONTENT',
            ]);
        }
    }
}
