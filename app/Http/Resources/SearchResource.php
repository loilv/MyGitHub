<?php

namespace App\Http\Resources;

use App\Constants\DefineCode;
use App\Helpers\Functions;
use App\Models\Image;
use App\Models\Taxonomy;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class SearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $image = new ImageResource(Image::where('commom_id', $this->id)->first());
        $city = isset($this->city_id) ? Taxonomy::select('id', 'name')->where('id', $this->city_id)->first() : null;
        $time = Functions::getDateFormatAgo($this->created_at);

        if ($request->table == 'news_projects') {
            $follow = $this->getFollow('user_projects', 'project_id');
        } elseif ($request->table == 'news_biddings') {
            $follow = $this->getFollow('user_biddings', 'bidding_id');
        } elseif ($request->table == 'documents') {
            $follow = $this->getFollow('user_documents', 'document_id');
        }

        if (isset($this->link_id)) {
            if ($request->type == 'video') {
                @$link = $this->link_id;
            } else {
                @$link = $request->root() . '/uploads/document/' . $this->link_id;
            }
        }
        if (isset($request->data) && $request->data) {
            foreach ($request->data as $v) {
                $datas[$v]          = $this->$v;
                $datas['image']     = @$image;
                $datas['city']      = @$city->name;
                $datas['time']      = @$time;
                $datas['link']      = @$link;
                $datas['follow']    = @$follow;
            }
            return $datas;
        } else {
            $data = $this->resource;
            $data->image    = @$image;
            $data->city     = @$city->name;
            $data->time     = @$time;
            $data->link     = @$link;
            $data->follow   = @$follow;
            return $data;
        }
    }

    /**
     * get status follow on user
     *
     * @param $table
     * @param $column
     *
     * @return int
     */
    private function getFollow($table, $column)
    {
        $user = auth('api')->user();
        $status_follow = $user && DB::table($table)->where('user_id', $user->id)
            ->where($column, $this->id)
            ->first() ? DefineCode::FOLLOW  : DefineCode::NO_FOLLOW;
        return $status_follow;
    }
}
