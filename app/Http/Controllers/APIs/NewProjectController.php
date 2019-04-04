<?php

namespace App\Http\Controllers\APIs;

use App\Constants\ResponseStatusCode;
use App\Http\Resources\NewBiddingDetail;
use App\Http\Resources\NewProjectDetail;
use App\Http\Resources\NewProjectResource;
use App\Models\NewsProject;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\ServiceAPIs\NewProject;
use App\Http\Controllers\Controller;

class NewProjectController extends Controller
{
    public $project_detail;

    public function __construct(NewProject $project_detail)
    {
        $this->project_detail = $project_detail;
    }

    /**
     * Function get list new projects
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListProject(Request $request)
    {
        $data = [];
        $users = (auth('api')->user())? auth('api')->user()->orderPackageProjects : '';
        $now = strtotime(Carbon::now()->format('Y-m-d H:i:s'));
        if ($users && count($users)) {
            foreach ($users as $user) {
                $expiration_date = strtotime($user->limit);
                /** Check current time with expiration date on order package */
                if ($user && $expiration_date >= $now) {
                    $data = NewProjectResource::collection(NewsProject::orderBy('time', 'desc')
                        ->where('time', '<=', Carbon::now()->format('Y-m-d H:i:s'))
                        ->paginate(20));
                } else {
                    $data = NewProjectResource::collection(NewsProject::orderBy('time', 'desc')
                        ->where('time_end', '<=', Carbon::now()->format('Y-m-d H:i:s'))
                        ->where('time', '<=', Carbon::now()->format('Y-m-d H:i:s'))
                        ->paginate(20));
                }
            }
        } else {
            $data = NewProjectResource::collection(NewsProject::orderBy('time', 'desc')
                ->where('time_end', '<=', Carbon::now()->format('Y-m-d H:i:s'))
                ->where('time', '<=', Carbon::now()->format('Y-m-d H:i:s'))
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
     * Function get data project detail
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function projectDetail($project_id)
    {
        $project = NewsProject::find($project_id);

        if ($project) {
            $project = new NewProjectDetail($project);
            return response()->json([
                'code' => ResponseStatusCode::OK,
                'data' => $project,
            ]);
        } else {
            return response()->json([
                'code'    => ResponseStatusCode::NOT_FOUND,
                'message' => 'Project not found',
            ]);
        }
    }
}
