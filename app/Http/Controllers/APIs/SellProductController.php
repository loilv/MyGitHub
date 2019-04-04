<?php

namespace App\Http\Controllers\APIs;

use App\Constants\ResponseStatusCode;
use App\Http\Resources\ListProductResource;
use App\Http\ServiceAPIs\SellProducts;
use App\Models\SellProduct;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Services\ProductService;
use JWTAuth;
use Mockery\Exception;
use PhpParser\Node\Stmt\DeclareDeclare;

class SellProductController extends Controller
{
    public $product;
    public $sell_product;

    public function __construct(ProductService $product, SellProducts $sell_product)
    {
        $this->product = $product;
        $this->sell_product = $sell_product;
    }

    /**
     * Function create sell product on my shop
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createSellProduct(Request $request)
    {
        try {
            $create_product = $this->product->createSellProduct($request);

            return response()->json([
                'code'    => ResponseStatusCode::OK,
                'message' => "Create product successfully",
                'data'    => $create_product,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code'    => ResponseStatusCode::NOT_ACCEPTABLE,
                'message' => "Data transmitted to error",
            ]);
        }
    }

    /**
     * Function get list product on user
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListMyShop(Request $request)
    {
        try {
            $products = $this->sell_product->getListProducts($request);

            if (count($products)) {
                return response()->json([
                    'code' => ResponseStatusCode::OK,
                    'data' => $products,
                ]);
            } else {
                return response()->json([
                    'code'    => ResponseStatusCode::NO_CONTENT,
                    'message' => "No-content",
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'code'    => ResponseStatusCode::INTERNAL_SERVER_ERROR,
                'message' => "Server Error",
            ]);
        }
    }

    /**
     * Function show detail product
     *
     * @param $product_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function productDetail($product_id)
    {
        try {
            $product = $this->product->productDetail($product_id);

            return response()->json([
                'code' => ResponseStatusCode::OK,
                'data' => $product,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code'    => ResponseStatusCode::NOT_FOUND,
                'message' => "Product not found",
            ]);
        }
    }

    /**
     * Function get all products
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listProducts(Request $request)
    {
        $sort = $request->sort && $request->sort == 'asc' ? 'asc' : 'desc';
        $products = SellProduct::orderBy('price', $sort)->paginate(20);
        if ($request->city && $request->category == '') {
            $products = SellProduct::where('city_id', $request->city)
                ->paginate(20);
        } elseif ($request->category && $request->city == '') {
            $products = SellProduct::where('category_id', $request->category)
                ->paginate(20);
        } elseif ($request->category && $request->city) {
            $products = SellProduct::where('category_id', $request->category)
                ->where('city_id', $request->city)
                ->paginate(20);
        }
        $data = ListProductResource::collection($products);

        if (count($data)) {
            return response()->json([
                'code' => ResponseStatusCode::OK,
                'data' => $data,
            ]);
        } else {
            return response()->json([
                'code'    => ResponseStatusCode::NO_CONTENT,
                'message' => "NO-CONTENT",
            ]);
        }
    }
}
