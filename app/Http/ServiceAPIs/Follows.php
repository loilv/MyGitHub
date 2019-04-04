<?php

namespace App\Http\ServiceAPIs;

use App\Constants\DefineCode;
use App\Models\UserBidding;
use App\Models\UserDocument;
use JWTAuth;
use App\Models\UserProject;
use App\Models\UserInvestor;

class Follows
{
    /**
     * Function create follow on user
     *
     * @param $request
     *
     * @return mixed
     */
    public function addFollow($request)
    {
        $data = [];
        if (isset($request->table) && $request->table == 'UserProject') {
            $data = [
                'user_id'      => $request->current_user,
                'project_id'   => $request->project_id,
                'role_name_id' => 0,
                'status'       => DefineCode::NEW_PROJECT_NOT_READ,
            ];
            $result = UserProject::create($data);
        } elseif (isset($request->table) && $request->table == 'UserBidding') {
            $data = [
                'user_id'      => $request->current_user,
                'bidding_id'   => $request->bidding_id,
                'status'       => DefineCode::NEW_PROJECT_NOT_READ,
            ];
            $result = UserBidding::create($data);
        } elseif (isset($request->table) && $request->table == 'UserInvestor') {
            $data = [
                'user_id'     => $request->current_user,
                'investor_id' => $request->investor_id,
            ];
            $result = UserInvestor::create($data);
        } elseif (isset($request->table) && $request->table == 'UserDocument') {
            $data = [
                'user_id'     => $request->current_user,
                'document_id' => $request->document_id,
            ];
            $result = UserDocument::create($data);
        }
        return $result;
    }

    /**
     * Function delete follow
     *
     * @param $request
     *
     * @return mixed
     */
    public function delFollow($request)
    {
        $user = auth('api')->user();
        if (isset($request->table) && $request->table == 'UserProject') {
            $result = UserProject::where('user_id', $user->id)
                ->where('project_id', $request->project_id);
        } elseif (isset($request->table) && $request->table == 'UserInvestor') {
            $result = UserInvestor::where('user_id', $user->id)
                ->where('investor_id', $request->investor_id);
        } elseif (isset($request->table) && $request->table == 'UserBidding') {
            $result = UserBidding::where('user_id', $user->id)
                ->where('bidding_id', $request->bidding_id);
        } elseif (isset($request->table) && $request->table == 'UserDocument') {
            $result = UserDocument::where('user_id', $user->id)
                ->where('document_id', $request->document_id);
        }

        return $result->delete();
    }
}
