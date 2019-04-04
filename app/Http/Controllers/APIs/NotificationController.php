<?php

namespace App\Http\Controllers\APIs;

use App\Constants\NotificationCode;
use App\Constants\ResponseStatusCode;
use App\Http\Resources\NotificatonResource;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    /**
     * Function get list notification with type
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListNotification(Request $request)
    {
        $user = auth('api')->user();
        if (isset($request->type) && $request->type == 'system') {
            $data = NotificatonResource::collection(
                Notification::where('type_notification', '=', NotificationCode::TYPE_SYSTEM)
                    ->where('user_id', $user->id)
                    ->orderBy('id', 'desc')
                    ->paginate(20)
            );
        } else {
            $data = NotificatonResource::collection(
                Notification::where('type_notification', '=', NotificationCode::TYPE_FOLLOW)
                    ->where('user_id', $user->id)
                    ->orderBy('id', 'desc')
                    ->paginate(20)
            );
        }

        if (count($data)) {
            return response()->json([
                'code' => ResponseStatusCode::OK,
                'data' => $data,
            ]);
        } else {
            return response()->json([
                'code'    => ResponseStatusCode::NO_CONTENT,
                'message' => 'NO CONTENT',
            ]);
        }
    }

    /**
     * Function update status notification
     * @param $notification_id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus($notification_id)
    {
        $notification = Notification::find($notification_id);

        if ($notification) {
            $notification->update([
                'status' => NotificationCode::READ
            ]);

            return response()->json([
                'code' => ResponseStatusCode::OK,
                'data' => $notification,
            ]);
        } else {
            return response()->json([
                'code'    => ResponseStatusCode::NOT_FOUND,
                'message' => 'NOT FOUND',
            ]);
        }
    }
}
