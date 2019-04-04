<?php

namespace App\Http\Controllers\APIs;

use App\Constants\ResponseStatusCode;
use App\Helpers\Functions;
use App\Http\Resources\OtherResource;
use App\Models\Document;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class OtherController extends Controller
{
    /**
     * Function takes data with input param
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getData(Request $request)
    {
        $result = DB::table($request->table)
            ->get();

        if (count($result)) {
            return response()->json([
                'code' => ResponseStatusCode::OK,
                'data' => $result
            ]);
        } else {
            return response()->json([
                'code' => ResponseStatusCode::NO_CONTENT,
                'data' => "No-content"
            ]);
        }
    }

    /**
     * Function get document
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDocument(Request $request)
    {
        $data = OtherResource::collection(Document::where('type', $request->type)
//            ->where('date', '<=', Carbon::now()->format('Y-m-d H:i:s'))
            ->orderBy('created_at', 'desc')
            ->paginate(20));

        if (count($data)) {
            return response()->json([
                'code' => ResponseStatusCode::OK,
                'data' => $data
            ]);
        } else {
            return response()->json([
                'code' => ResponseStatusCode::NO_CONTENT,
                'data' => "No-content"
            ]);
        }
    }

    public function getListDocumentFollow(Request $request)
    {
        $data =[];
        $user_document = auth('api')->user()->userDocument;
        if (count($user_document)) {
            $result = [];
            foreach ($user_document as $document) {
                $documents = Document::where('type', $request->type)->where('id', $document->document_id)->get();
                if ($documents) {
                    foreach ($documents as $value) {
                        $result[] = new OtherResource($value);
                    }
                }
            }
            $data = Functions::paginateArray($result, 20);
        }

        if (count($data)) {
            return response()->json([
                'code' => ResponseStatusCode::OK,
                'data' => $data
            ]);
        } else {
            return response()->json([
                'code' => ResponseStatusCode::NO_CONTENT,
                'data' => "No-content"
            ]);
        }
    }
}
