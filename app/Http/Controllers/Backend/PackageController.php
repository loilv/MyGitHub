<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Constants\DefineCode;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Package::get();
        return view('backend.pages.package.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'Thêm gói';
        $name = [
            DefineCode::NAME_PACKAGE_INFO_PROJECT => 'Gói thông tin dự án',
            DefineCode::NAME_PACKAGE_INFO_BIDDING => 'Gói thông tin đấu thầu',
            DefineCode::NAME_PACKAGE_PRODUCT      => 'Gói đăng bán sản phẩm',
        ];
        $type = [
            DefineCode::TYPE_PACKAGE_MONTH   => 'tháng',
            DefineCode::TYPE_PACKAGE_PRODUCT => 'sản phẩm',
        ];
        return view('backend.pages.package._form', compact('title', 'name', 'type'));
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
        $name_request = explode(",", str_replace(['[', ']'], ['', ''], $request->name));
        $request->merge([
            'name'       => $name_request[1],
            'price'      => preg_replace('/[^0-9.]/', '', $request->price),
            'package_id' => $name_request[0],
        ]);
        Package::create($request->all());
        return redirect('backend/package')->with('success', 'Thêm gói thành công');
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
    public function edit(Package $package)
    {
        $title = 'Sửa gói';
        $name = [
            DefineCode::NAME_PACKAGE_INFO_PROJECT => 'Gói thông tin dự án',
            DefineCode::NAME_PACKAGE_INFO_BIDDING => 'Gói thông tin đấu thầu',
            DefineCode::NAME_PACKAGE_PRODUCT      => 'Gói đăng bán sản phẩm',
        ];
        $type = [
            DefineCode::TYPE_PACKAGE_MONTH   => 'tháng',
            DefineCode::TYPE_PACKAGE_PRODUCT => 'sản phẩm',
        ];
        return view('backend.pages.package._form', compact('package', 'title', 'name', 'type'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Package $package)
    {
        $data = [
            'name'  => $request->name,
            'price' => $request->price ? preg_replace('/[^0-9.]/', '', $request->price) : '',
            'unit'  => $request->unit,
            'limit' => $request->limit,
        ];
        $package->update($data);
        return redirect('backend/package')->with('success', 'Cập nhật gói thành công');
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
        Package::destroy($id);
        return redirect()->back()->with('success', 'Xóa gói thành công');
    }
}
