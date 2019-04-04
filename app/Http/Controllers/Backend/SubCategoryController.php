<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Constants\DefineCode;
use App\Http\Services\CategoryService;

class SubCategoryController extends Controller
{
    /*
     * Function constructor
     * @param $videoService
     */
    public function __construct(CategoryService $categoryService)
    {
        $this->serve = $categoryService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->generateParams();
        $data = Category::where('parent_id', '<>', DefineCode::NUMBER_ZERO);
        if ($request->search) {
            $data = $data->where('name', 'like', '%' . $request->search . '%');
        }
        $data = $data->get();
        return view('backend.pages.category.sub', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'Thêm Danh mục con';
        $this->generateParams();
        return view('backend.pages.category._formsub', compact('title'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->serve->createSubCategory($request);
        return redirect('backend/sub_category')->with('success', 'Thêm danh mục thành công');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $sub_category)
    {
        $title = 'Sửa Danh mục con';
        $this->generateParams();
        return view('backend.pages.category._formsub', compact('title', 'sub_category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $sub_category)
    {
        $this->serve->updateSubCategory($request, $sub_category);
        return redirect('backend/sub_category')->with('success', 'Cập nhật danh mục thành công');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->serve->deleteCategory($id);
        return redirect()->back()->with('success', 'Xóa danh mục thành công');
    }

    /**
     * Function param
     *
     */
    private function generateParams()
    {
        $list = Category::where('parent_id', DefineCode::NUMBER_ZERO)->get();
        $type = [
            DefineCode::TYPE_CATEGORY_PRODUCT => 'Danh mục sản phẩm',
            DefineCode::TYPE_CATEGORY_NEWS    => 'Danh mục tin tức',
        ];
        view()->share([
            'list' => $list,
            'type' => $type,
        ]);
    }
}
