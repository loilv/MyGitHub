<?php

namespace App\Http\Controllers\Backend;

use App\Models\Taxonomy;
use Illuminate\Http\Request;
use App\Constants\DefineCode;
use App\Http\Services\ProductService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SellProduct;
use App\Models\Image;
use App\Models\Category;
use Carbon\Carbon;
use Excel;

class SellProductController extends Controller
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
        $data = SellProduct::orderBy('id', 'desc');
        if ($request->search) {
            $data = $data->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('price', 'like', '%' . $request->search . '%');
        }
        if ($request->status) {
            $data = $data->where('status', $request->status);
        }
        $data = $data->get();
        $this->generateParams();
        return view('backend.pages.product.sell', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'Thêm sản phẩm bán';
        $this->generateParams();
        return view('backend.pages.product._formsell', compact('title'));
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
        $user = User::where('id', $request->user_id)->first();
        $check = $this->serve->createSellProduct($request);
        if (!isset($check)) {
            return redirect('backend/sell')->with('error', 'Người dùng ' . $user->name . ' chưa mua gói or đã hết hạn');
        } else {
            return redirect('backend/sell')->with('success', 'Thêm sản phẩm thành công');
        }
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
    public function edit(SellProduct $sell)
    {
        $title = 'Sửa sản phẩm bán';
        $this->generateParams();
        $district = Taxonomy::where('parent_id', $sell->city_id)->pluck('name', 'id')->toArray();
        return view('backend.pages.product._formsell', compact('sell', 'title', 'district'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SellProduct $sell)
    {
        $this->serve->updateSellProduct($request, $sell);
        return redirect('backend/sell')->with('success', 'Cập nhật sản phẩm thành công');
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
        $this->serve->deleteSellProduct($id);
        return redirect()->back()->with('success', 'Xóa sản phẩm thành công');
    }

    /**
     * Function param
     *
     */
    private function generateParams()
    {
        $status = [
            DefineCode::STATUS_SELL_NEW         => 'Hàng mới',
            DefineCode::STATUS_SELL_LIQUIDATION => 'Hàng thanh lý',
            DefineCode::STATUS_SELL_VIOLATE     => 'Hàng vi phạm',
        ];
        $category = Category::where('type', DefineCode::TYPE_CATEGORY_PRODUCT)->get();
        $users = User::where('role', '<>', DefineCode::ROLE_ADMIN)->orderBy('id', 'desc')->get();
        $city = Taxonomy::where('type', 'city')->pluck('name', 'id')->toArray();
        view()->share([
            'status'   => $status,
            'users'    => $users,
            'category' => $category,
            'city'     => $city,
        ]);
    }

    /*
     * Function Export Excel
     */
    public function exportSell(Request $request)
    {
        $rq = $request->all();
        $data = SellProduct::orderBy('id', 'desc');
        if ($rq['search']) {
            $data = $data->where('name', 'like', '%' . $rq['search'] . '%')
                ->orWhere('price', 'like', '%' . $rq['search'] . '%');
        }
        if ($rq['status']) {
            $data = $data->where('status', $rq['status']);
        }
        $data = $data->get();
        Excel::create('Danh sách sản phẩm bán', function ($excel) use ($data) {
            $excel->sheet('Sheet 1', function ($sheet) use ($data) {
                $sheet->cell('A1:P1', function ($row) {
                    $row->setBackground('#008686');
                    $row->setFontColor('#ffffff');
                });
                $sheet->row(1, [
                    'ID',
                    'Mã sản phẩm',
                    'Tên sản phẩm',
                    'Giá SP',
                    'Giá Sau Giảm',
                    'Số lương bán',
                    'Số lượng còn lại',
                    'Thời gian bắt đầu',
                    'Thời gian kết thúc',
                    'Trạng thái',
                    'Danh mục',
                    'Địa điểm',
                    'Nhà sản xuất',
                    'Năm sản xuất',
                    'Mô tả',
                    'Người bán',
                ]);
                $i = 1;
                if ($data) {
                    foreach ($data as $k => $ex) {
                        $i++;
                        $sheet->row($i, [
                            @$ex->id,
                            @$ex->product_code,
                            @$ex->name,
                            @$ex->price,
                            @$ex->price_reduced,
                            @$ex->amount_sell,
                            @$ex->amount_remain,
                            @$ex->time_start,
                            @$ex->time_end,
                            @$ex->status == 0 ? 'Hàng mới' : ($ex->status == 1 ? 'Hàng thanh lý' : 'Hàng vi phạm'),
                            @$ex->sellToCategory->name,
                            @$ex->address,
                            @$ex->manufacturer,
                            @$ex->year_of_manufacture,
                            @$ex->description,
                            @$ex->sellToUser->id,
                        ]);
                    }
                }
            });
        })->export('xlsx');
    }

    /*
     *
     */
    public function importSell(Request $request)
    {
        if ($request->hasFile('file')) {
            Excel::load($request->file('file')->getRealPath(), function ($render) {
                $result = $render->toArray();
                foreach ($result as $k => $row) {
                    SellProduct::create([
                        'id'                  => @$row['id'],
                        'product_code'        => @$row['ma_san_pham'],
                        'name'                => @$row['ten_san_pham'],
                        'price'               => @$row['gia_sp'],
                        'price_reduced'       => @$row['gia_sau_giam'],
                        'amount_sell'         => @$row['so_luong_ban'],
                        'amount_remain'       => @$row['amount_remain'],
                        'time_start'          => @$row['thoi_gian_bat_dau'],
                        'time_end'            => @$row['thoi_gian_ket_thuc'],
                        'status'              => @$row['status'] == 'Hàng mới'
                            ? 0 : ($row['status' == 'Hàng thanh lý'] ? 1 : 2),
                        'category_id'         => @$row['danh_muc'] == 'Sản phẩm bán chạy'
                            ? 0 : ($row['danh_muc'] == 'Sản phẩm mới' ? 1 : 0),
                        'address'             => @$row['dia_diem'],
                        'manufacturer'        => @$row['nha_san_xuat'],
                        'year_of_manufacture' => @$row['nam_san_xuat'],
                        'description'         => @$row['mo_ta'],
                        'user_id'             => @$row['nguoi_ban'],
                    ]);
                }
            });
        }
        return redirect('backend/sell')->with('success', 'Import sản phẩm thành công');
    }
}
