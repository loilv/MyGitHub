<?php

namespace App\Http\Controllers\APIs;

use App\Constants\DefineCode;
use App\Models\Image;
use Illuminate\Http\Request;
use App\Constants\ResponseStatusCode;
use App\Http\Controllers\Controller;
use Mockery\Exception;

class ImageController extends Controller
{
    protected $image = '';

    public function __construct(Image $image)
    {
        $this->image = $image;
    }

    /**
     * Funtion update image user current
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAvatar(Request $request)
    {
        try {
            if (!empty($request->all())) {
                $image = $this->image->addAvatarUser($request->file('image'));
                if (isset($image['code']) && $image['code'] == 0) {
                    return response()->json(
                        [
                            'code' => ResponseStatusCode::TYPE_IMAGE_ERROR,
                            'message' => 'Type Image Error',
                        ]
                    );
                } else {
                    return response()->json(
                        [
                            'code' => ResponseStatusCode::OK,
                            'message' => 'Update image successfully',
                        ]
                    );
                }
            }
        } catch (\Exception $e) {
            return response()->json(
                [
                    'code'    => ResponseStatusCode::INTERNAL_SERVER_ERROR,
                    'message' => "Server Error",
                ]
            );
        }
    }
}
