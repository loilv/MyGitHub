<?php

namespace App\Http\Controllers\Backend;

use App\Constants\DefineCode;
use App\Models\SellProduct;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\OrderPackage;
use App\Models\User;
use App\Constants\OrderPackageCode;
use DB;

class OrderPackageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = OrderPackage::orderBy('id', 'desc')->get();
        return view('backend.pages.package.order', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $name = Package::get();
        $status = [
            OrderPackageCode::STATUS_ORDER_UNPAID  => 'Chưa thanh toán',
            OrderPackageCode::STATUS_ORDER_PAID    => 'Đã thanh toán',
            OrderPackageCode::STATUS_ORDER_EXPIRED => 'Hết hạn',
        ];
        $users = User::where('role', '<>', DefineCode::ROLE_ADMIN)->orderBy('id', 'desc')->get();
        return view('backend.pages.package._formorder', compact('name', 'status', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $package = Package::where('id', $request->name)->first();

//        dd($request->all());
        $aaa = OrderPackage::where('user_id', $request->user_id)
            ->where('package_id', $package->package_id)
            ->where('status', OrderPackageCode::STATUS_ORDER_UNPAID)
            ->first();
//        dd($aaa, $package);
        if ($aaa) {
            return redirect('backend/order')->with('error', 'Gói đã tồn tại');
        }

        $limit_news_project = OrderPackage::where('user_id', $request->user_id)
            ->where('package_id', $package->package_id)
            ->where('status', OrderPackageCode::STATUS_ORDER_PAID)->first();
        $limit_news_bidding = OrderPackage::where('user_id', $request->user_id)
            ->where('package_id', $package->package_id)
            ->where('status', OrderPackageCode::STATUS_ORDER_PAID)->first();
        $limit_product = OrderPackage::where('user_id', $request->user_id)
            ->where('package_id', $package->package_id)
            ->where('status', OrderPackageCode::STATUS_ORDER_PAID)->first();
        $time_end = 1;
        $total_product = -1;
        if ($limit_news_project) {
            $time_end = strtotime($limit_news_project->limit);
        } else {
            if ($limit_news_bidding) {
                $time_end = strtotime($limit_news_bidding->limit);
            }
        }
        if ($limit_product) {
            $total_product = (int)$limit_product->limit;
        }
        $sell_product = SellProduct::where('user_id', $request->user_id)->count();
//        dd($sell_product, $total_product);
        if (($package->package_id == OrderPackageCode::PACKAGE_PROJECT && $time_end >= strtotime(now()))
            || ($package->package_id == OrderPackageCode::PACKAGE_BIDDING && $time_end >= strtotime(now()))
            || ($package->package_id == OrderPackageCode::PACKAGE_PRODUCT && $total_product > $sell_product)) {
            return redirect('backend/order')->with('error', 'Gói vẫn còn thời hạn');
        } else {
            if ($request->status == OrderPackageCode::STATUS_ORDER_PAID) {
                if ($package->type == OrderPackageCode::TYPE_NEWS) {
                    $limit = now()->copy()->addMonth($package->unit);
                } else {
                    $limit = $package->unit;
                }
            }
            if ($request->status == OrderPackageCode::STATUS_ORDER_UNPAID) {
                $limit = '';
            }
            $request->merge([
                'name'       => $package->name,
                'price'      => preg_replace('/[^0-9.]/', '', $request->price),
                'unit'       => $package->unit,
                'type'       => $package->type,
                'package_id' => $package->package_id,
                'limit'      => $limit,
            ]);
            OrderPackage::create($request->all());
            return redirect('backend/order')->with('success', 'Đã tạo gói thành công');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
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
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(OrderPackage $order)
    {
        $name = Package::get();
        $status = [
            OrderPackageCode::STATUS_ORDER_UNPAID  => 'Chưa thanh toán',
            OrderPackageCode::STATUS_ORDER_PAID    => 'Đã thanh toán',
            OrderPackageCode::STATUS_ORDER_EXPIRED => 'Hết hạn',
        ];
        $users = User::where('role', '<>', DefineCode::ROLE_ADMIN)->orderBy('id', 'desc')->get();
        return view('backend.pages.package._formorder', compact('order', 'name', 'status', 'users'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        OrderPackage::destroy($id);
        return redirect()->back()->with('success', 'Đã xóa thành công');
    }

    /*
     * Function update status
     *
     * @params $id, $status
     */
    public function updateStatus($id, $status)
    {
        $data = OrderPackage::where('id', $id)->first();
        if ($data) {
            $limit = '';
            $data->status = $status;
            if ($status == OrderPackageCode::STATUS_ORDER_PAID) {
                $check = OrderPackage::where('user_id', $data->user_id)->first();
                if ($data->type == OrderPackageCode::TYPE_NEWS) {
                    $limit = now()->copy()->addMonth($data->unit);
                } else {
                    $limit = $data->unit;
                }
            }
        }
        $arr = [
            'limit' => $limit,
        ];
        $data->update($arr);
        return redirect('backend/order')->with('success', 'Cập nhật trạng thái thành công');
    }
}
