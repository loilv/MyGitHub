<?php

namespace App\Http\Controllers\APIs;

use App\Constants\OrderPackageCode;
use App\Constants\ResponseStatusCode;
use App\Helpers\Functions;
use App\Http\Resources\ListProductResource;
use App\Http\Resources\NewBiddings;
use App\Http\Resources\NewProjectResource;
use App\Models\OrderPackage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\ServiceAPIs\Follows;
use Illuminate\Pagination\LengthAwarePaginator;
use JWTAuth;

class FollowController extends Controller
{
    public $follow;

    public function __construct(Follows $follow)
    {
        $this->follow = $follow;
    }

    /**
     * Function create follow on user
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addFollow(Request $request)
    {
        $user = auth('api')->user();
        $user_packages = OrderPackage::where('user_id', $user->id)
            ->where('package_id', '=', OrderPackageCode::PACKAGE_BIDDING)
            ->where('limit', '>=', Carbon::now()->format('Y-m-d H:i:s'))
            ->orWhere('package_id', '=', OrderPackageCode::PACKAGE_PROJECT)
            ->where('user_id', $user->id)
            ->where('limit', '>=', Carbon::now()->format('Y-m-d H:i:s'))
            ->get();
        $request->merge([
            'current_user' => $user->id,
        ]);

        if ($request->table == 'UserDocument') {
            try {
                $result = $this->follow->addFollow($request);

                return response()->json([
                    'code' => ResponseStatusCode::OK,
                    'data' => $result,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'code'    => ResponseStatusCode::NOT_ACCEPTABLE,
                    'message' => "Data transmitted to error",
                ]);
            }
        } else {
            if (count($user_packages)) {
                try {
                    $result = $this->follow->addFollow($request);

                    return response()->json([
                        'code' => ResponseStatusCode::OK,
                        'data' => $result,
                    ]);
                } catch (\Exception $e) {
                    return response()->json([
                        'code'    => ResponseStatusCode::NOT_ACCEPTABLE,
                        'message' => "Data transmitted to error",
                    ]);
                }
            } else {
                return response()->json([
                    'code'    => ResponseStatusCode::USER_NOT_PERMISSION,
                    'message' => "User not permission",
                ]);
            }
        }
    }

    /**
     * Function Unfollow
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function delFollow(Request $request)
    {
        $result = $this->follow->delFollow($request);
        if ($result) {
            return response()->json([
                'code'    => ResponseStatusCode::OK,
                'message' => "Delete successfully",
            ]);
        } else {
            return response()->json([
                'code'    => ResponseStatusCode::NOT_FOUND,
                'message' => "NOT FOUND",
            ]);
        }
    }

    /**
     * Function get list Project on user follows
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListObjectFollow(Request $request)
    {
        $data = [];
        if ($request->type == 'project') {
            $users = auth('api')->user()->userProject;
            if (count($users)) {
                foreach ($users as $user) {
                    $project = $user->newProject;
                    if ($project) {
                        $data[] = new NewProjectResource($project);
                    }
                }
            }
        } elseif ($request->type == 'bidding') {
            $users = auth('api')->user()->userBidding;
            if (count($users)) {
                foreach ($users as $user) {
                    $bidding = $user->newBidding;
                    if ($bidding) {
                        $data[] = new NewBiddings($bidding);
                    }
                }
            }
        } elseif ($request->type == 'product') {
            $users = auth('api')->user()->userProduct;
            if (count($users)) {
                foreach ($users as $user) {
                    $products = $user->userProduct;
                    if ($products) {
                        $data[] = new ListProductResource($products);
                    }
                }
            }
        }

        $data = Functions::paginateArray($data, 20);

        if (count($data)) {
            return response()->json([
                'code' => ResponseStatusCode::OK,
                'data' => $data,
            ]);
        } else {
            return response()->json([
                'code'    => ResponseStatusCode::NO_CONTENT,
                'message' => 'No-content',
            ]);
        }
    }
}
