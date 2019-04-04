<?php

namespace App\Http\Controllers\APIs;

use App\Constants\ResponseStatusCode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Services\ProductService;

class PostController extends Controller
{
    public $new_post;
    public function __construct(ProductService $new_post)
    {
        $this->new_post = $new_post;
    }

    /**
     * Function create post
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPost(Request $request)
    {
        $user = auth('api')->user();
        $request->merge([
            'user_id' => $user->id,
        ]);
        try {
            $new_post = $this->new_post->createPostProduct($request);

            return response()->json([
                'code'    => ResponseStatusCode::OK,
                'message' => "Create post successfully",
                'data'    => $new_post,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'code'    => ResponseStatusCode::NOT_ACCEPTABLE,
                'message' => "Data transmitted to error",
            ]);
        }
    }
}
