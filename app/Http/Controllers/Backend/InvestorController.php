<?php

namespace App\Http\Controllers\Backend;

use App\Constants\NotificationCode;
use App\Models\Notification;
use App\Models\UserInvestor;
use App\Models\UserProject;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Constants\DefineCode;
use App\Models\Taxonomy;
use Excel;
use App\Models\NewsProject;

class InvestorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = User::where('role', DefineCode::ROLE_INVESTOR);
        if ($request->search) {
            $data = $data->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%')
                ->orWhere('phone', 'like', '%' . $request->search . '%')
                ->orWhere('company', 'like', '%' . $request->search . '%');
        }
        $data = $data->get();
        return view('backend.pages.investor.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'Thêm nhà thầu';
        $city = Taxonomy::where('type', 'city')->pluck('name', 'id')->toArray();
        return view('backend.pages.investor._form', compact('title', 'city'));
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
        $role = DefineCode::ROLE_INVESTOR;
        $request->merge([
            'role' => $role,
        ]);
        User::create($request->all());
        return redirect('backend/investor')->with('success', 'Thêm nhà thầu thành công');
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
    public function edit(User $investor)
    {
        $title = 'Sửa Nhà Thầu';
        $city = Taxonomy::where('type', 'city')->pluck('name', 'id')->toArray();
        $district = Taxonomy::where('parent_id', $investor->city_id)->pluck('name', 'id')->toArray();
        return view('backend.pages.investor._form', compact('title', 'investor', 'city', 'district'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $investor)
    {
        $data = [
            'name'        => @$request->name,
            'email'       => @$request->email,
            'tax_code'    => @$request->tax_code,
            'phone'       => @$request->phone,
            'address'     => @$request->address,
            'company'     => @$request->company,
            'city_id'     => @$request->city_id,
            'district_id' => @$request->district_id,
        ];
        $investor->update($data);

        $check_investor = UserInvestor::where('investor_id', $investor->id)->first();
        if (isset($check_investor) && $check_investor->getUserInvestor) {
            $arr = [
                'message'           => 'Nhà thầu ' . $check_investor->getUserInvestor->name
                    . ' vừa thay đổi thông tin cá nhân',
                'user_id'           => $check_investor->user_id,
                'common_id'         => $investor->id,
                'type'              => 'user',
                'status'            => NotificationCode::UNREAD,
                'sender_id'         => $check_investor->investor_id,
                'type_notification' => NotificationCode::TYPE_FOLLOW,
            ];
            Notification::create($arr);
        }
        return redirect('backend/investor')->with('success', 'Cập nhật nhà thầu thành công');
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
        $data = User::find($id);
        if ($data) {
            UserProject::where('user_id', $id)->delete();
            User::destroy($id);
        }
        return redirect()->back()->with('success', 'Xóa nhà thầu thành công');
    }

    /*
     * Function import investor
     */
    public function importInvestor(Request $request)
    {
        if ($request->hasFile('file')) {
            Excel::load($request->file('file')->getRealPath(), function ($render) {
                $result = $render->toArray();
                foreach ($result as $k => $row) {
                    if ($row['contact_phone']) {
                        $arrPhone = explode(',', $row['contact_phone']);
                        $phone = str_replace(' ', '', $arrPhone[0]);
                    }
                    $project_code = NewsProject::where('project_code', $row['projectid'])->first();
                    $check_phone = User::where('phone', $phone)->first();
                    if ($row['projectid'] && $project_code) {
                        if (!$check_phone) {
                            $user = User::create([
                                'role'        => DefineCode::ROLE_INVESTOR,
                                'name'        => @$row['l_dear'],
                                'company'     => @$row['l_firm_name'],
                                'phone'       => $phone,
                                'fax'         => @$row['contact_fax'],
                                'email'       => @$row['contact_email'],
                                'position'    => @$row['l_position'],
                                'address'     => @$row['l_firm_address'],
                                'country'     => @$row['l_firm_country'],
                                'city_id'     => @$row['l_firm_province'],
                                'district_id' => @$row['l_firm_town'],
                            ]);
                            UserProject::create([
                                'user_id'      => $user->id,
                                'project_id'   => $project_code->id,
                                'role_name_id' => @$row['l_role_name'],
                            ]);
                        } else {
                            UserProject::create([
                                'user_id'      => $check_phone->id,
                                'project_id'   => $project_code->id,
                                'role_name_id' => @$row['l_role_name'],
                            ]);

                            $investor = UserInvestor::where('investor_id', $check_phone->id)->first();
                            if ($investor) {
                                Notification::create([
                                    'message'           => 'Nhà thầu ' . $check_phone->name
                                        . ' vừa được nhắc đến trong tin dự án ' . $project_code->name,
                                    'user_id'           => $investor->user_id,
                                    'common_id'         => $investor->investor_id,
                                    'type'              => 'project',
                                    'status'            => NotificationCode::NUMBER_ZERO,
                                    'sender_id'         => NotificationCode::NUMBER_ZERO,
                                    'type_notification' => NotificationCode::TYPE_FOLLOW,
                                ]);
                            }
                        }
                    }
                }
            });
        }
        return redirect('backend/project')->with('success', 'Import nhà thầu thành công');
    }

    /**
     * Function export excel investor
     *
     */
    public function exportInvestor(Request $request)
    {
        $rq = $request->all();
        $data = User::where('role', DefineCode::ROLE_INVESTOR)->orderBy('id', 'desc');
        if ($rq['search']) {
            $data = $data->where('name', 'like', '%' . $rq['search'] . '%')
                ->orWhere('email', 'like', '%' . $rq['search'] . '%')
                ->orWhere('phone', 'like', '%' . $rq['search'] . '%')
                ->orWhere('company', 'like', '%' . $rq['search'] . '%');
        }
        $data = $data->get();
//        dd($data);
        Excel::create('Danh sách Investor', function ($excel) use ($data) {
            $excel->sheet('Sheet 1', function ($sheet) use ($data) {
                $sheet->cell('A1:H1', function ($row) {
                    $row->setBackground('#008686');
                    $row->setFontColor('#ffffff');
                });
                $sheet->row(1, [
                    'Name',
                    'Email',
                    'SĐT',
                    'Company',
                    'MST',
                    'Địa chỉ',
                    'Thành phố',
                    'Quận',
                ]);
                $i = 1;
                if ($data) {
                    foreach ($data as $k => $ex) {
                        $i++;
                        $sheet->row($i, [
                            @$ex->name,
                            @$ex->email,
                            @$ex->phone,
                            @$ex->company,
                            @$ex->tax_code,
                            @$ex->address,
                            @$ex->userToCity->name,
                            @$ex->userToDistrict->name,
                        ]);
                    }
                }
            });
        })->export('xlsx');
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
            User::where('id', $del)->delete();
            UserProject::where('user_id', $del)->delete();
        }
        return 1;
    }
}
