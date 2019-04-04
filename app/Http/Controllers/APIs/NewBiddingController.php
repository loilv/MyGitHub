<?php

namespace App\Http\Controllers\APIs;

use App\Constants\OrderPackageCode;
use App\Constants\ResponseStatusCode;
use App\Http\Resources\NewBiddingDetail;
use App\Http\Resources\NewBiddings;
use App\Models\NewsBidding;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NewBiddingController extends Controller
{
    /**
     * Get all new biddings
     *
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = [];
        $users = (auth('api')->user())?auth('api')->user()->orderPackageBiddings : '';
        $now = strtotime(Carbon::now()->format('Y-m-d H:i:s'));
        if ($users && count($users)) {
            foreach ($users as $user) {
                $expiration_date = strtotime($user->limit);
                /** Check current time with expiration date on order package */
                if ($user && $expiration_date >= $now) {
                    $data = NewBiddings::collection(NewsBidding::orderBy('time_action', 'desc')
                        ->where('time_action', '<=', Carbon::now()->format('Y-m-d H:i:s'))
                        ->paginate(20));
                } else {
                    $data = NewBiddings::collection(NewsBidding::orderBy('time_action', 'desc')
                        ->where('time_end', '<=', Carbon::now()->format('Y-m-d H:i:s'))
                        ->where('time_action', '<=', Carbon::now()->format('Y-m-d H:i:s'))
                        ->paginate(20));
                }
            }
        } else {
            $data = NewBiddings::collection(NewsBidding::orderBy('time_action', 'desc')
                ->where('time_end', '<=', Carbon::now()->format('Y-m-d H:i:s'))
                ->where('time_action', '<=', Carbon::now()->format('Y-m-d H:i:s'))
                ->paginate(20));
        }

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

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($bidding_id)
    {
        $bidding = NewsBidding::find($bidding_id);
        if ($bidding) {
            $data = new NewBiddingDetail($bidding);

            return response()->json([
                'code' => ResponseStatusCode::OK,
                'data' => $data,
            ]);
        } else {
            return response()->json([
                'code'    => ResponseStatusCode::NOT_FOUND,
                'message' => 'New bidding not found',
            ]);
        }
    }
}
