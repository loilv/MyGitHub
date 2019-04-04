<?php

namespace App\Http\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Constants\DefineCode;

class CategoryService
{
    /*
     * Function create category
     *
     * @param $request
     */
    public function createCategory($request)
    {
        $request->merge([
            'parent_id' => DefineCode::NUMBER_ZERO,
        ]);
        Category::create($request->all());
    }

    /*
     * Function Update category
     *
     * @param $request $category
     */
    public function updateCategory($request, $category)
    {
        $data = [
            'name'      => $request->name,
            'type'      => $request->type,
            'parent_id' => DefineCode::NUMBER_ZERO,
        ];
        $category->update($data);
        $sub = Category::where('parent_id', $category->id)->first();
        if ($sub) {
            $arr = [
                'type' => $category->type,
            ];
            $sub->update($arr);
        }
    }

    /*
     * Function delete category
     *
     * @params $id
     */
    public function deleteCategory($id)
    {
        $category = Category::find($id);
        if ($category) {
            $category->delete();
        }
    }

    /*
     * Function create sub-category
     *
     * @param $request
     */
    public function createSubCategory($request)
    {
        $category = Category::where('id', $request->parent_id)->first();
        if ($category) {
            $request->merge([
                'type' => $category->type,
            ]);
        }
        Category::create($request->all());
    }

    /*
     * Function Update sub-category
     *
     * @param $request $sub_category
     */
    public function updateSubCategory($request, $sub_category)
    {
        $category = Category::where('id', $request->parent_id)->first();
        $data = [
            'name'      => $request->name,
            'type'      => $category->type,
            'parent_id' => $request->parent_id,
        ];
        $sub_category->update($data);
    }
}
