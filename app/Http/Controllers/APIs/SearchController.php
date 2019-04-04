<?php

namespace App\Http\Controllers\APIs;

use App\Constants\DefineCode;
use App\Constants\ResponseStatusCode;
use App\Helpers\Functions;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SearchResource;
use App\Http\Services\SearchAllTable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SearchController extends Controller
{
    protected $search_service;

    public function __construct(SearchAllTable $search_service)
    {
        $this->search_service = $search_service;
    }

    /**
     * Function search all column on table
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchAll(Request $request)
    {
        $data = $this->search_service->searchAllTable($request);

        if (count($data)) {
            return response()->json([
                'code' => ResponseStatusCode::OK,
                'data' => $data,
            ]);
        } else {
            return response()->json([
                'code'    => ResponseStatusCode::NO_CONTENT,
                'message' => "NO CONTENT",
            ]);
        }
    }
}
