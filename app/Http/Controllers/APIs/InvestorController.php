<?php

namespace App\Http\Controllers\APIs;

use App\Constants\NotificationCode;
use App\Constants\ResponseStatusCode;
use App\Helpers\Functions;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserInvestor;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InvestorController extends Controller
{
    /**
     * Function get list investor user follow
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListInvestorOnUser()
    {
        $user = auth('api')->user();
        $investors = UserInvestor::where('user_id', $user->id)
            ->join('users', 'user_investors.investor_id', '=', 'users.id')
            ->select('id', 'name', 'email', 'phone', 'fax', 'address', 'position', 'company')
            ->get();

        /** Check notification investor return status */
        if ($investors) {
            foreach ($investors as $investor) {
                $status = Notification::where('sender_id', $investor->id)->get();
                $investor->status = NotificationCode::INVESTOR_NO_CHANGE_STATUS;
                if ($status) {
                    foreach ($status as $value) {
                        if ($value->status == NotificationCode::UNREAD) {
                            $investor->status = NotificationCode::INVESTOR_CHANGE_STATUS;
                        } else {
                            $investor->status = NotificationCode::INVESTOR_NO_CHANGE_STATUS;
                        }
                    }
                }
            }
        }
        /** end */
        if (count($investors)) {
            return response()->json(
                [
                    'code' => ResponseStatusCode::OK,
                    'data' => $investors,
                ]
            );
        } else {
            return response()->json(
                [
                    'code'    => ResponseStatusCode::NO_CONTENT,
                    'message' => "NO CONTENT",
                ]
            );
        }
    }

    /**
     * Function show investor detail
     *
     * @param $investor_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInvestorDetail($investor_id)
    {
        $investor = User::where('id', $investor_id)
            ->select('name', 'email', 'phone', 'address', 'company')
            ->first();

        $investor_content = User::where('users.id', $investor_id)
            ->join('notifications', 'users.id', '=', 'notifications.sender_id')
            ->where('notifications.type_notification', NotificationCode::TYPE_FOLLOW)
            ->select(
                'notifications.common_id as common_id',
                'notifications.type as type',
                'notifications.message as message',
                'notifications.created_at as time',
                'notifications.status as status'
            )
            ->get();

        if (count($investor_content)) {
            foreach ($investor_content as $value) {
                $value->time = Functions::getDateFormatAgo($value->time);
            }
        }

        $investor->content = $investor_content;

        if (!empty($investor)) {
            return response()->json(
                [
                    'code' => ResponseStatusCode::OK,
                    'data' => $investor,
                ]
            );
        } else {
            return response()->json(
                [
                    'code'    => ResponseStatusCode::NOT_FOUND,
                    'message' => "NOT FOUND",
                ]
            );
        }
    }
}
