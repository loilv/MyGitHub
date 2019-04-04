<?php

namespace App\Http\Controllers\Backend;

use App\Constants\DefineCode;
use App\Http\Services\ProductService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Post;
use App\Models\Image;
use App\Models\Category;
use Carbon\Carbon;
use Excel;

class PostProductController extends Controller
{
    /*
     * Function constructor
     */
    public function __construct(ProductService $productService)
    {
        $this->serve = $productService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = Post::with('getUser', 'postToImage')->orderBy('id', 'desc');
        if ($request->search) {
            $data = $data->where('post_code', 'like', '%' . $request->search . '%')
                ->orWhere('title', 'like', '%' . $request->search . '%');
        }
        $data = $data->get();
        return view('backend.pages.product.post', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'Thêm mới Sản phẩm';
        $this->generateParams();
        return view('backend.pages.product._formpost', compact('title'));
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
        $this->serve->createPostProduct($request);
        return redirect('backend/post')->with('success', 'Thêm sản phẩm thành công');
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
    public function edit(Post $post)
    {
        $title = 'Sửa sản phẩm';
        $this->generateParams();
        return view('backend.pages.product._formpost', compact('post', 'title'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        $this->serve->updatePostProduct($request, $post);
        return redirect('backend/post')->with('success', 'Cập nhật sản phẩm thành công');
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
        $this->serve->deletePostProduct($id);
        return redirect()->back()->with('success', 'Xóa sản phẩm thành công');
    }

    /**
     * Function param
     *
     */
    private function generateParams()
    {
        $status = [
            DefineCode::NEW => 'Hàng mới',
            DefineCode::OLD => 'Hàng cũ',
        ];
        $users = User::where('role', '<>', DefineCode::ROLE_ADMIN)->get();
        $category = Category::where('type', DefineCode::TYPE_CATEGORY_PRODUCT)->get();

        view()->share([
            'status'   => $status,
            'users'    => $users,
            'category' => $category,
        ]);
    }

    /*
     * Function export excel
     */
    public function exportPost(Request $request)
    {
        $rq = $request->all();
        $data = Post::with('postToCategory', 'getUser')->orderBy('id', 'desc');
        if ($rq['search']) {
            $data = $data->where('post_code', 'like', '%' . $rq['search'] . '%')
                ->orWhere('title', 'like', '%' . $rq['search'] . '%');
        }
        $data = $data->get();
        Excel::create('Danh sách sản phẩm mua', function ($excel) use ($data) {
            $excel->sheet('Sheet 1', function ($sheet) use ($data) {
                $sheet->cell('A1:H1', function ($row) {
                    $row->setBackground('#008686');
                    $row->setFontColor('#ffffff');
                });
                $sheet->row(1, [
                    'ID',
                    'Mã bài viết',
                    'Tên bài viết',
                    'Trạng thái',
                    'Thời gian',
                    'Danh mục',
                    'Người đăng',
                    'Mô tả',
                ]);
                $i = 1;
                if ($data) {
                    foreach ($data as $k => $ex) {
                        $i++;
                        $sheet->row($i, [
                            @$ex->id,
                            @$ex->post_code,
                            @$ex->title,
                            @$ex->status == 0 ? 'Hàng mới' : 'Hàng cũ',
                            @$ex->time,
                            @$ex->postToCategory->name, //
                            @$ex->getUser->name,
                        ]);
                    }
                }
            });
        })->export('xlsx');
    }
}
