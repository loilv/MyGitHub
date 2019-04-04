<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Constants\DefineCode;
use App\Http\Services\CategoryService;

class CategoryController extends Controller
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
        $data = Category::where('parent_id', DefineCode::NUMBER_ZERO);
        $type = [
            DefineCode::TYPE_CATEGORY_PRODUCT => 'Danh mục sản phẩm',
            DefineCode::TYPE_CATEGORY_NEWS    => 'Danh mục tin tức',
        ];
        if ($request->search) {
            $data = $data->where('name', 'like', '%' . $request->search . '%');
        }
        $data = $data->get();
        return view('backend.pages.category.index', compact('data', 'type'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'Thêm mới Danh mục';
        $type = [
            DefineCode::TYPE_CATEGORY_PRODUCT => 'Danh mục sản phẩm',
            DefineCode::TYPE_CATEGORY_NEWS    => 'Danh mục tin tức',
        ];
        return view('backend.pages.category._form', compact('title', 'type'));
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
        $this->serve->createCategory($request);
        return redirect('backend/category')->with('success', 'Thêm danh mục thành công');
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
    public function edit(Category $category)
    {
        $type = [
            DefineCode::TYPE_CATEGORY_PRODUCT => 'Danh mục sản phẩm',
            DefineCode::TYPE_CATEGORY_NEWS    => 'Danh mục tin tức',
        ];
        $title = 'Chỉnh sửa danh mục';
        return view('backend.pages.category._form', compact('title', 'category', 'type'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        $this->serve->updateCategory($request, $category);
        return redirect('backend/category')->with('success', 'Cập nhật danh mục thành công');
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
        return redirect('backend/category')->with('success', 'Xóa danh mục thành công');
    }
}
