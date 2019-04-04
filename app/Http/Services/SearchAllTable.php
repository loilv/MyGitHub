<?php

namespace App\Http\Services;

use App\Helpers\Functions;
use App\Http\Resources\SearchResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SearchAllTable
{
    /**
     * Function search all table
     *
     * @param $request
     *
     * @return array|\Illuminate\Pagination\LengthAwarePaginator
     */
    public function searchAllTable($request)
    {
        $data = [];
        $columns = Schema::getColumnListing($request->table);
        $query = DB::table($request->table)->orderBy('created_at', 'desc');

        if (isset($request->type) && $request->type) {
            $query = $query->where('type', $request->type);
        }

        /** search with table new bidding and new project with order package */
        if ($request->table == 'news_biddings') {
            $time = 'time_action';
            $users = (auth('api')->user()) ? auth('api')->user()->orderPackageBiddings : '';
            $query = $this->searchBiddingAndProject($query, $users, $request, $columns, $time);
        } elseif ($request->table == 'news_projects') {
            $time = 'time';
            $users = (auth('api')->user()) ? auth('api')->user()->orderPackageProjects : '';
            $query = $this->searchBiddingAndProject($query, $users, $request, $columns, $time);
        } else {
            $query = $query->where(function ($q) use (&$columns, $request) {
                foreach ($columns as $column) {
                    if ($column != $request->type) {
                        $q->orWhere($column, 'like', '%' . $request->keyword . '%');
                    }
                }
            });
        }

        $query = $query->get();
        $result = SearchResource::collection($query);
        $data = Functions::paginateArray($result, 20);

        return $data;
    }

    /**
     * Function search table new bidding && new project
     *
     * @param $query
     * @param $users
     * @param $request
     * @param $columns
     *
     * @return mixed
     */
    public function searchBiddingAndProject($query, $users, $request, $columns, $time)
    {
        $now = strtotime(Carbon::now()->format('Y-m-d H:i:s'));
        if ($users != '' && count($users)) {
            foreach ($users as $user) {
                $expiration_date = strtotime($user->limit);
                /** Check current time with expiration date on order package */
                if ($user && $expiration_date >= $now) {
                    $query = $query->where(function ($q) use (&$columns, $request, $user) {
                        foreach ($columns as $column) {
                            if ($column != $request->type) {
                                $q->orWhere($column, 'like', '%' . $request->keyword . '%');
                            }
                        }
                    });
                } else {
                    $query = $query->where('time_end', '<=', Carbon::now()->format('Y-m-d H:i:s'))
                        ->where($time, '<=', Carbon::now()->format('Y-m-d H:i:s'))
                        ->where(function ($q) use (&$columns, $request) {
                            foreach ($columns as $column) {
                                if ($column != $request->type) {
                                    $q->orWhere($column, 'like', '%' . $request->keyword . '%');
                                }
                            }
                        });
                }
            }
        } else {
            $query = $query->where('time_end', '<=', Carbon::now()->format('Y-m-d H:i:s'))
                ->where($time, '<=', Carbon::now()->format('Y-m-d H:i:s'))
                ->where(function ($q) use (&$columns, $request) {
                    foreach ($columns as $column) {
                        if ($column != $request->type) {
                            $q->orWhere($column, 'like', '%' . $request->keyword . '%');
                        }
                    }
                });
        }

        return $query;
    }
}
