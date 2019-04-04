<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use App\Models\Image;
use App\Models\Package;
use App\Models\Category;
use App\Models\NewsProject;
use DB;

class AjaxController extends Controller
{
    public function getProductImage()
    {
        $post = Post::with('images')->where('id', 11)->first();
        $domain = request()->root();

        $html = '';
        if ($post && count($post->images)) {
            foreach ($post->images as $key => $value) {
                $html .= '<div class="file-preview-frame krajee-default  kv-preview-thumb" 
                id="preview-1550645723268_40-0" data-fileindex="0" data-template="image"><div class="kv-file-content">';
                $html .= '<img src="' . $domain . '/uploads/' . $value->path . '/' . $value->name . '" 
                class="file-preview-image kv-preview-data" title="' . @$value->name . '" 
                alt="' . @$value->name . '" style="width:auto;height:auto;max-width:100%;max-height:100%;">';
                $html .= '</div><div class="file-thumbnail-footer">';
                $html .= '<div class="file-footer-caption" title="' . @$value->name . '">';
                $html .= '<div class="file-caption-info">' . @$value->name . '</div>';
                $html .= '</div>';
                $html .= '<div class="file-thumb-progress kv-hidden"><div class="progress">';
                $html .= '<div class="progress-bar bg-success progress-bar-success progress-bar-striped active" 
                    role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%;">';
                $html .= 'Initializing...';
                $html .= '</div>';
                $html .= '</div></div>';
                $html .= '<div class="file-upload-indicator" title="Not uploaded yet">
                            <i class="fa fa-plus-circle text-warning"></i></div>';
                $html .= '<div class="file-actions">';
                $html .= '<div class="file-footer-buttons">';
                $html .= '<button type="button" class="kv-file-upload btn btn-kv btn-default btn-outline-secondary" 
                            title="Upload file"><i class="fa fa-upload">
                            </i></button> <button type="button" 
                            class="kv-file-remove btn btn-kv btn-default btn-outline-secondary" 
                            title="Remove file"><i class="fa fa-trash"></i></button>';
                $html .= '<button type="button" class="kv-file-zoom btn btn-kv btn-default btn-outline-secondary" 
                            title="View Details"><i class="fa fa-search-plus"></i></button>     </div>';
                $html .= '</div>';
                $html .= '<div class="clearfix"></div>';
                $html .= '</div>';
                $html .= '</div>';
            }
        }

        return $html;
    }

    public function getOrderPackage($id)
    {
        $package = Package::where('id', $id)->get();
        return response()->json($package);
    }

    /*
     * Function check unique email
     */
    public function checkMail(Request $request)
    {
        $result = DB::table($request->table)->where('email', $request->email)->first();
        if ($result) {
            return $result->id == $request->id ? 'true' : 'false';
        }
        return 'true';
    }

    /*
     * Function check unique phone
     */
    public function checkPhone(Request $request)
    {
        $result = DB::table($request->table)->where('phone', $request->phone)->first();
        if ($result) {
            return $result->id == $request->id ? 'true' : 'false';
        }
        return 'true';
    }

    /*
     * Function check unique code
     */
    public function checkCode(Request $request)
    {
        $result = DB::table($request->table)->where('product_code', $request->product_code)->first();
        if ($result) {
            return $result->id == $request->id ? 'true' : 'false';
        }
        return 'true';
    }

    /*
     * Function check unique code
     */
    public function checkPost(Request $request)
    {
        $result = DB::table($request->table)->where('post_code', $request->post_code)->first();
        if ($result) {
            return $result->id == $request->id ? 'true' : 'false';
        }
        return 'true';
    }

    /*
     * Function check unique code
     */
    public function checkProject(Request $request)
    {
        $result = DB::table($request->table)->where('project_code', $request->project_code)->first();
        if ($result) {
            return $result->id == $request->id ? 'true' : 'false';
        }
        return 'true';
    }

    /*
     * Function check unique code
     */
    public function checkNumberTBMT(Request $request)
    {
        $result = DB::table($request->table)->where('number_tbmt', $request->number_tbmt)->first();
        if ($result) {
            return $result->id == $request->id ? 'true' : 'false';
        }
        return 'true';
    }

    /*
     * Function check unique code
     */
    public function checkMST(Request $request)
    {
        $result = DB::table($request->table)->where('tax_code', $request->tax_code)->first();
        if ($result) {
            return $result->id == $request->id ? 'true' : 'false';
        }
        return 'true';
    }

    /*
     * Ajax popup detail project
     */
    public function detailProject($id)
    {
        $html = '';
        $project = NewsProject::where('id', $id)->with('projectToUserProject')->first();
        if ($project) {
            foreach ($project->projectToUserProject as $k => $v) {
                $html .= '<tr>';
                $html .= '<td>' . @$v->userprojectToProject->name . '</td>';
                $html .= '<td>' . @$v->userprojectToUser->name . '</td>';
                $html .= '<td>' . @$v->role_name_id . '</td>';
                $html .= '<td>' . @$v->userprojectToUser->company . '</td>';
            }
        } else {
            $html .= '<tr><td colspan="3">No Result</td></tr>';
        }
        return $html;
    }

    /*
     * Function get category
     */
    public function getCategory($id)
    {
        return response()->json(Category::find($id), 200);
    }
}
