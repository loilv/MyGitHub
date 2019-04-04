<?php

namespace App\Http\Controllers\Backend;

use App\Constants\DefineCode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\NewsBidding;
use App\Models\Category;
use App\Http\Services\NewsService;
use Excel;

class NewsBiddingController extends Controller
{
    /*
     * Function constructor
     * @param $newsService
     */
    public function __construct(NewsService $newsService)
    {
        $this->serve = $newsService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = NewsBidding::orderBy('id', 'desc');
        if ($request->search) {
            $data = $data->where('number_tbmt', 'like', '%' . $request->search . '%')
                ->orWhere('name_bidding', 'like', '%' . $request->search . '%');
        }
        $data = $data->get();
        return view('backend.pages.news.bidding', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'Thêm tin đấu thầu';
        $this->generateParams();
        return view('backend.pages.news._formbidding', compact('title'));
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
        $this->serve->createNewsBidding($request);
        return redirect('backend/bidding')->with('success', 'Thêm tin đấu thầu thành công');
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
    public function edit(NewsBidding $bidding)
    {
        $title = 'Sửa tin đấu thầu';
        $this->generateParams();
        return view('backend.pages.news._formbidding', compact('title', 'bidding'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, NewsBidding $bidding)
    {
        $this->serve->updateNewsBidding($request, $bidding);
        return redirect('backend/bidding')->with('success', 'Cập nhật tin đấu thầu thành công');
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
        NewsBidding::destroy($id);
        return redirect()->back()->with('success', 'Xóa dự án thành công');
    }

    /**
     * Function param
     *
     */
    private function generateParams()
    {
        $field = [
            DefineCode::FIELD_GOODS       => 'Hàng hóa',
            DefineCode::FIELD_BUILD       => 'Xây lắp',
            DefineCode::FIELD_ADVISORY    => 'Tư vấn',
            DefineCode::FIELD_UN_ADVISORY => 'Phi tư vấn',
            DefineCode::FIELD_MIXTURE     => 'Hỗn hợp',
        ];
        $category = Category::where('type', DefineCode::TYPE_CATEGORY_NEWS)->get();
        $users = User::where('role', '<>', DefineCode::ROLE_ADMIN)->get();
        view()->share([
            'category' => $category,
            'field'    => $field,
            'users'    => $users,
        ]);
    }

    /*
     *
     */
    public function exportBidding(Request $request)
    {
        $rq = $request->all();
        $data = NewsBidding::orderBy('id', 'desc');
        if ($rq['search']) {
            $data = $data->where('number_tbmt', 'like', '%' . $rq['search'] . '%')
                ->orWhere('name_bidding', 'like', '%' . $rq['search'] . '%');
        }

        $data = $data->get();
        Excel::create('Danh sách TIN-DAU-THAU', function ($excel) use ($data) {
            $excel->sheet('Sheet 1', function ($sheet) use ($data) {
                $sheet->cell('A1:T1', function ($row) {
                    $row->setBackground('#008686');
                    $row->setFontColor('#ffffff');
                });
                $sheet->cell('U1:W1', function ($row) {
                    $row->setBackground('#f1b53d');
                    $row->setFontColor('#ffffff');
                });
                $sheet->cell('X1:AA1', function ($row) {
                    $row->setBackground('#495057');
                    $row->setFontColor('#ffffff');
                });
                $sheet->row(1, [
                    'Số TBMT',
                    'Thời điểm đăng tải ',
                    'Tên gói thầu',
                    'Phân loại',
                    'Hình thức thông báo',
                    'Loại thông báo',
                    'Chi tiết nguồn vốn ',
                    'Loại hợp đồng ',
                    'Bên mời thầu',
                    'Hình thức lựa chọn nhà thầu',
                    'Số hiệu KHLCNT ',
                    'Tên KHLCNT',
                    'Phương thức LCNT',
                    'Thời gian thực hiện hợp đồng',
                    'Thời điểm mở thầu',
                    'Hình thức bảo đảm dự thầu',
                    'Thời gian bán từ',
                    'Thời gian bán đến ngày',
                    'Hồ sơ đấu thầu',
                    'Lĩnh vực',
                    'Giá bán',
                    'Số tiền đảm bảo dự thầu',
                    'Địa điểm thực hiện gói thầu',
                    'Phát hành E-HSMT ',
                    'Địa điểm nhận HSDT',
                    'Quan tâm ',
                    'Làm rõ E-HSMT',
                ]);
                $i = 1;
                if ($data) {
                    foreach ($data as $k => $ex) {
                        $i++;
                        if ($ex->field == 0) {
                            $hh = 'Hàng hóa';
                        } else {
                            if ($ex->field == 1) {
                                $hh = 'Xây lắp';
                            } else {
                                if ($ex->field == 2) {
                                    $hh = 'Tư vấn';
                                } else {
                                    if ($ex->field == 3) {
                                        $hh = 'Phi tư vấn';
                                    } else {
                                        if ($ex->field == 4) {
                                            $hh = 'Hỗn hợp';
                                        }
                                    }
                                }
                            }
                        }
                        $sheet->row($i, [
                            @$ex->number_tbmt,
                            @$ex->time_action,
                            @$ex->name_bidding,
                            @$ex->type,
                            @$ex->notification_form,
                            @$ex->notification_type,
                            @$ex->source_detail,
                            @$ex->type_contract,
                            @$ex->partner,
                            @$ex->contractor_form,
                            @$ex->number_khlcnt,
                            @$ex->name_khlcnt,
                            @$ex->method_lcnt,
                            @$ex->perform_contract,
                            @$ex->time_open_close,
                            @$ex->bidding_form,
                            @$ex->time_start,
                            @$ex->time_end,
                            @$ex->bidding_document,
                            $hh,
                            @$ex->price,
                            @$ex->amount_bidding,
                            @$ex->address_bidding,
                            @$ex->released,
                            @$ex->address_hsdt,
                            @$ex->care,
                            @$ex->clarify_hsmt,
                        ]);
                    }
                }
            });
        })->export('xlsx');
    }

    /*
     * Function import bidding
     */
    public function importBidding(Request $request)
    {
        if ($request->hasFile('file')) {
            Excel::load($request->file('file')->getRealPath(), function ($render) {
                $result = $render->formatDates(false)->toArray();
                foreach ($result as $k => $row) {
                    $time_action = str_replace('-', '', $row['thoi_diem_dang_tai']);
                    $time_open_close = trim(str_replace('-', '', $row['thoi_diem_mo_thau']));
                    $time_start = trim(str_replace('-', '', $row['thoi_gian_ban_tu']));
                    $time_end = trim(str_replace('-', '', $row['thoi_gian_ban_den_ngay']));

                    $ma_thau = NewsBidding::where('number_tbmt', $row['so_tbmt'])->first();
                    if (!$ma_thau && $row['so_tbmt']) {
                        if (trim($row['linh_vuc']) == 'Hàng hóa') {
                            $hh = 0;
                        } elseif (trim($row['linh_vuc']) == 'Xây lắp') {
                            $hh = 1;
                        } elseif (trim($row['linh_vuc']) == 'Tư vấn') {
                            $hh = 2;
                        } elseif (trim($row['linh_vuc']) == 'Phi tư vấn') {
                            $hh = 3;
                        } elseif (trim($row['linh_vuc']) == 'Hỗn hợp') {
                            $hh = 4;
                        }

                        if ($row['ben_moi_thau']) {
                            $user = User::create([
                                'name'    => $row['ben_moi_thau'],
                                'company' => $row['ben_moi_thau'],
                                'role'    => DefineCode::ROLE_COMPANY,
                            ]);
                        }
                        if ($row['chu_dau_tu']) {
                            $investor = User::create([
                                'name'    => $row['chu_dau_tu'],
                                'company' => $row['chu_dau_tu'],
                                'role'    => DefineCode::ROLE_COMPANY,
                            ]);
                        }

                        NewsBidding::create([
                            'number_tbmt'       => @$row['so_tbmt'],
                            'time_action'       => $row['thoi_diem_dang_tai']
                                ? Carbon::createFromFormat('d/m/Y H:i', $time_action)
                                    ->format('Y-m-d H:i') : null,
                            'name_bidding'      => @$row['ten_goi_thau'],
                            'type'              => @$row['phan_loai'],
                            'notification_form' => @$row['hinh_thuc_thong_bao'],
                            'notification_type' => @$row['loai_thong_bao'],
                            'source_detail'     => @$row['chi_tiet_nguon_von'],
                            'type_contract'     => @$row['loai_hop_dong'],
                            'partner'           => $user->id,
                            'investor'          => $investor ? $investor->id : null,
                            'contractor_form'   => @$row['hinh_thuc_lua_chon_nha_thau'],
                            'number_khlcnt'     => @$row['so_hieu_khlcnt'],
                            'name_khlcnt'       => @$row['ten_khlcnt'],
                            'method_lcnt'       => @$row['phuong_thuc_lcnt'],
                            'perform_contract'  => @$row['thoi_gian_thuc_hien_hop_dong'],
                            'time_open_close'   => $row['thoi_diem_mo_thau']
                                ? Carbon::createFromFormat('d/m/Y H:i', $time_open_close)
                                    ->format('Y-m-d H:i') : null,
                            'bidding_form'      => @$row['hinh_thuc_bao_dam_du_thau'],
                            'bidding_document'  => @$row['ho_so_dau_thau'],
                            'field'             => $hh,
                            'price'             => @$row['gia_ban'],
                            'amount_bidding'    => @$row['so_tien_dam_bao_du_thau'],
                            'time_start'        => $row['thoi_gian_ban_tu']
                                ? Carbon::createFromFormat('d/m/Y H:i', $time_start)
                                    ->format('Y-m-d H:i') : null,
                            'time_end'          => $row['thoi_gian_ban_den_ngay']
                                ? Carbon::createFromFormat('d/m/Y H:i', $time_end)
                                    ->format('Y-m-d H:i') : null,
                            'address_bidding'   => @$row['dia_diem_thuc_hien_goi_thau'],
                            'released'          => @$row['phat_hanh_e_hsmt'],
                            'address_hsdt'      => @$row['dia_diem_nhan_hsdt'],
                            'care'              => @$row['quan_tam'],
                            'clarify_hsmt'      => @$row['lam_ro_e_hsmt'],
                        ]);
                    }
                }
            });
        }

        return redirect('backend/bidding')->with('success', 'Import tin đấu thầu thành công');
    }

    /**
     * @param Request $request
     *
     * @return int
     */
    public function deleteAll(Request $request)
    {
        $delid = $request->ids;
        foreach ($delid as $del) {
            NewsBidding::where('id', $del)->delete();
        }
        return 1;
    }
}
