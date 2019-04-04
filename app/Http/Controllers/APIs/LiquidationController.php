<?php

namespace App\Http\Controllers\APIs;

use App\Constants\DefineCode;
use App\Constants\ResponseStatusCode;
use App\Http\Resources\LiquidationResource;
use App\Models\Liquidation;
use App\Models\Post;
use App\Models\SellProduct;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\ServiceAPIs\Liquidations;

class LiquidationController extends Controller
{
    public $liquidation;

    public function __construct(Liquidations $liquidation)
    {
        $this->liquidation = $liquidation;
    }

    /**
     * Function post product liquidation
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createLiquidation(Request $request)
    {
        try {
            $result = $this->liquidation->createPostLiquidation($request);

            return response()->json([
                'code' => ResponseStatusCode::OK,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code' => ResponseStatusCode::NOT_ACCEPTABLE,
                'data' => "Data transmitted to error",
            ]);
        }
    }

    /**
     * Get all liquidation
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListLiquidation(Request $request)
    {
        $user = auth('api')->user();
        $user_id = $user->id;
        $data = LiquidationResource::collection(Post::where('user_id', $user_id)
            ->where('status', DefineCode::STATUS_SELL_LIQUIDATION)
            ->orderBy('id', 'desc')
            ->paginate(20));

        if (count($data)) {
            return response()->json([
                'code' => ResponseStatusCode::OK,
                'data' => $data,
            ]);
        } else {
            return response()->json([
                'code'    => ResponseStatusCode::NO_CONTENT,
                'message' => "No-content",
            ]);
        }
    }
}
