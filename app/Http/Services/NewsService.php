<?php

namespace App\Http\Services;

use App\Constants\NotificationCode;
use App\Models\Notification;
use App\Models\UserInvestor;
use App\Models\UserProject;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Image;
use App\Models\Post;
use App\Models\NewsBidding;
use App\Models\NewsProject;
use App\Constants\DefineCode;

class NewsService
{
    /*
     * Function create news bidding
     */
    public function createNewsBidding($request)
    {
        $time_open_close = Carbon::createFromFormat('d/m/Y H:i', $request->time_open_close)
            ->format('Y-m-d H:i');
        $time_action = Carbon::createFromFormat('d/m/Y H:i', $request->time_action)
            ->format('Y-m-d H:i');
        $time_start = $request->time_start
            ? Carbon::createFromFormat('d/m/Y H:i', $request->time_start)
                ->format('Y-m-d H:i') : null;
        $time_end = $request->time_end
            ? Carbon::createFromFormat('d/m/Y H:i', $request->time_end)
                ->format('Y-m-d H:i') : null;

        $request->merge([
            'time_open_close'  => $time_open_close,
            'time_action'      => $time_action,
            'time_start'       => $time_start,
            'time_end'         => $time_end,
            'bidding_document' => $request->bidding_document ? html_entity_decode($request->bidding_document) : '',
        ]);
        $newsBidding = NewsBidding::create($request->all());

        $check_investor = UserInvestor::where('investor_id', $request->partner)->first();
        if (isset($check_investor) && $check_investor->getUserInvestor) {
            $arr = [
                'message'           => 'Nhà thầu ' . $check_investor->getUserInvestor->name
                    . ' vừa được nhắc đến trong tin đấu thầu ' . $newsBidding->name,
                'user_id'           => $check_investor->user_id,
                'common_id'         => $newsBidding->id,
                'type'              => 'bidding',
                'status'            => NotificationCode::UNREAD,
                'sender_id'         => $check_investor->investor_id,
                'type_notification' => NotificationCode::TYPE_FOLLOW,
            ];
            Notification::create($arr);
        }

        $check_investor = UserInvestor::where('investor_id', $request->investor)->first();
        if (isset($check_investor) && $check_investor->getUserInvestor) {
            $arr = [
                'message'           => 'Nhà thầu ' . $check_investor->getUserInvestor->name
                    . ' vừa được nhắc đến trong tin đấu thầu ' . $newsBidding->name,
                'user_id'           => $check_investor->user_id,
                'common_id'         => $newsBidding->id,
                'type'              => 'bidding',
                'status'            => NotificationCode::UNREAD,
                'sender_id'         => $check_investor->investor_id,
                'type_notification' => NotificationCode::TYPE_FOLLOW,
            ];
            Notification::create($arr);
        }
    }

    /*
     * Function update news bidding
     */
    public function updateNewsBidding($request, $bidding)
    {
        $data = [
            'notification_form' => @$request->notification_form,
            'notification_type' => @$request->notification_type,
            'number_tbmt'       => @$request->number_tbmt,
            'time_action'       => $request->time_action
                ? Carbon::createFromFormat('d/m/Y H:i', $request->time_action)
                    ->format('Y-m-d H:i') : null,
            'number_khlcnt'     => @$request->number_khlcnt,
            'name_khlcnt'       => @$request->name_khlcnt,
            'field'             => @$request->field,
            'partner'           => @$request->partner,
            'investor'          => @$request->investor,
            'name_bidding'      => @$request->name_bidding,
            'type'              => @$request->type,
            'name_project'      => @$request->name_project,
            'source_detail'     => @$request->source_detail,
            'type_contract'     => @$request->type_contract,
            'contractor_form'   => @$request->contractor_form,
            'method_lcnt'       => @$request->method_lcnt,
            'perform_contract'  => @$request->perform_contract,
            'bidding_form'      => @$request->bidding_form,
            'time_start'        => $request->time_start
                ? Carbon::createFromFormat('d/m/Y H:i', $request->time_start)
                    ->format('Y-m-d H:i') : null,
            'time_end'          => $request->time_end
                ? Carbon::createFromFormat('d/m/Y H:i', $request->time_end)
                    ->format('Y-m-d H:i') : null,
            'released'          => @$request->released,
            'address_hsdt'      => @$request->address_hsdt,
            'time_open_close'   => $request->time_open_close
                ? Carbon::createFromFormat('d/m/Y H:i', $request->time_open_close)
                    ->format('Y-m-d H:i') : '',
            'address_bidding'   => @$request->address_bidding,
            'estimates_bidding' => @$request->estimates_bidding,
            'point'             => @$request->point,
            'bidding_document'  => $request->bidding_document ? html_entity_decode($request->bidding_document) : '',
            'care'              => @$request->care,
            'clarify_hsmt'      => @$request->clarify_hsmt,
            'price'             => @$request->price,
            'amount_bidding'    => @$request->amount_bidding,
        ];
        $bidding->update($data);

        $check_investor = UserInvestor::where('investor_id', $request->partner)->first();
        if (isset($check_investor) && $check_investor->getUserInvestor) {
            $arr = [
                'message'           => 'Nhà thầu ' . $check_investor->getUserInvestor->name
                    . ' vừa được nhắc đến trong tin đấu thầu ' . $bidding->name,
                'user_id'           => $check_investor->user_id,
                'common_id'         => $bidding->id,
                'type'              => 'bidding',
                'status'            => NotificationCode::UNREAD,
                'sender_id'         => $check_investor->investor_id,
                'type_notification' => NotificationCode::TYPE_FOLLOW,
            ];
            Notification::create($arr);
        }

        $check_investor = UserInvestor::where('investor_id', $request->investor)->first();
        if (isset($check_investor) && $check_investor->getUserInvestor) {
            $arr = [
                'message'           => 'Nhà thầu ' . $check_investor->getUserInvestor->name
                    . ' vừa được nhắc đến trong tin đấu thầu ' . $bidding->name,
                'user_id'           => $check_investor->user_id,
                'common_id'         => $bidding->id,
                'type'              => 'bidding',
                'status'            => NotificationCode::UNREAD,
                'sender_id'         => $check_investor->investor_id,
                'type_notification' => NotificationCode::TYPE_FOLLOW,
            ];
            Notification::create($arr);
        }
    }

    /*
     * Function create news project
     */
    public function createNewsProject($request)
    {
        $time_start = Carbon::createFromFormat('d/m/Y H:i', $request->time_start)->format('Y-m-d H:i');
        $time_end = Carbon::createFromFormat('d/m/Y H:i', $request->time_end)->format('Y-m-d H:i');
        $time = Carbon::createFromFormat('d/m/Y H:i', $request->time)->format('Y-m-d H:i');
        $value = $request->value ? preg_replace('/[^0-9.]/', '', $request->value) : null;
        $request->merge([
            'time_start'  => $time_start,
            'time_end'    => $time_end,
            'time'        => $time,
            'value'       => $value,
            'description' => $request->description ? html_entity_decode($request->description) : '',
        ]);
        $newsProject = NewsProject::create($request->except('user_id', 'role_name_id'));
        $users = $request->user_id;
        $role = $request->role_name_id;
        if ($users && $role) {
            foreach ($users as $k => $user) {
                $data = [
                    'user_id'      => $user,
                    'project_id'   => $newsProject->id,
                    'role_name_id' => $role[$k],
                    'status'       => 0,
                ];
                $user_project = UserProject::create($data);
                $check_investor = UserInvestor::where('investor_id', $user_project->user_id)->first();
                if (isset($check_investor) && $check_investor->getUserInvestor) {
                    $arr = [
                        'message'           => 'Nhà thầu ' . $check_investor->getUserInvestor->name
                            . ' vừa được nhắc đến trong tin dự án ' . $newsProject->name,
                        'user_id'           => $check_investor->user_id,
                        'common_id'         => $newsProject->id,
                        'type'              => 'project',
                        'status'            => NotificationCode::UNREAD,
                        'sender_id'         => $check_investor->investor_id,
                        'type_notification' => NotificationCode::TYPE_FOLLOW,
                    ];
                    Notification::create($arr);
                }
            }
        }
    }

    /*
     * Function update news project
     */
    public function updateNewsProject($request, $project)
    {
        $data = [
            'name'             => @$request->name,
            'name_description' => @$request->name_description,
            'value'            => $request->value ? preg_replace('/[^0-9.]/', '', $request->value)
                : null,
            'status'           => @$request->status,
            'time_start'       => @$request->time_start
                ? Carbon::createFromFormat('d/m/Y H:i', $request->time_start)
                    ->format('Y-m-d H:i') : '',
            'address'          => @$request->address,
            'floor_area'       => @$request->floor_area,
            'owner_type'       => @$request->owner_type,
            'dev_type'         => @$request->dev_type,
            'site_area'        => @$request->site_area,
            'project_code'     => @$request->project_code,
            'time_end'         => $request->time_end
                ? Carbon::createFromFormat('d/m/Y H:i', $request->time_end)
                    ->format('Y-m-d H:i') : '',
            'time'             => $request->time
                ? Carbon::createFromFormat('d/m/Y H:i', $request->time)
                    ->format('Y-m-d H:i') : '',
            'storeys'          => @$request->storeys,
            'description'      => $request->description ? html_entity_decode($request->description) : '',
            'city_id'          => @$request->city_id,
            'district_id'      => @$request->district_id,
            'country'          => @$request->country,
        ];
        $project->update($data);
        $users = $request->user_id;
        $role = $request->role_name_id;
        if ($users && $role) {
            UserProject::where('project_id', $project->id)->delete();
            foreach ($users as $k => $user) {
                $data = [
                    'user_id'      => $user,
                    'project_id'   => $project->id,
                    'role_name_id' => $role[$k],
                    'status'       => 0,
                ];
                $user_project = UserProject::create($data);
                $check_investor = UserInvestor::where('investor_id', $user_project->user_id)->first();
                if (isset($check_investor) && $check_investor->getUserInvestor) {
                    $arr = [
                        'message'           => 'Nhà thầu ' . $check_investor->getUserInvestor->name
                            . ' vừa được nhắc đến trong tin dự án ' . $project->name,
                        'user_id'           => $check_investor->user_id,
                        'common_id'         => $project->id,
                        'type'              => 'project',
                        'status'            => NotificationCode::UNREAD,
                        'sender_id'         => $check_investor->investor_id,
                        'type_notification' => NotificationCode::TYPE_FOLLOW,
                    ];
                    Notification::create($arr);
                }
            }
        }
    }
}
