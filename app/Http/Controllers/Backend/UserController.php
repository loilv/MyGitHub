<?php

namespace App\Http\Controllers\Backend;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Image;
use App\Constants\DefineCode;
use App\Http\Services\UserService;
use App\Models\Taxonomy;
use Excel;

class UserController extends Controller
{

    /*
     * Function constructor
     * @param $userService
     */
    public function __construct(UserService $userService)
    {
        $this->serve = $userService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


    public function index(Request $request)
    {
        $data = User::where('role', DefineCode::ROLE_MEMBER)->with('getImage')->orderBy('id', 'desc');
        if ($request->search) {
            $data = $data->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%')
                ->orWhere('phone', 'like', '%' . $request->search . '%');
        }
        $data = $data->get();
        return view('backend.pages.user.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->generateParams();
        return view('backend.pages.user._form', compact('gender', 'type', 'city'));
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
        $user = $this->serve->createUser($request);
        if (isset($user) && $user->role == DefineCode::ROLE_MEMBER) {
            return redirect('backend/user')->with('success', 'Thêm người dùng thành công');
        } else {
            return redirect('backend/company')->with('success', 'Thêm người dùng thành công');
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
    public function edit(User $user)
    {
        $this->generateParams();
        $district = Taxonomy::where('parent_id', $user->city_id)->pluck('name', 'id')->toArray();
        return view('backend.pages.user._form', compact('user', 'district'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $this->serve->updateUser($request, $user);
        if ($user->role == DefineCode::ROLE_MEMBER) {
            return redirect('backend/user')->with('success', 'Cập nhật user thành công');
        } else {
            return redirect('backend/company')->with('success', 'Cập nhật user thành công');
        }
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
        $this->serve->deleteUser($id);
        return redirect()->back()->with('success', 'Xóa user thành công');
    }

    /**
     * Function param
     *
     */
    private function generateParams()
    {
        $gender = [
            DefineCode::MALE   => 'Nam',
            DefineCode::FEMALE => 'Nữ',
        ];

        $type = [
            DefineCode::NORMAL => 'Thường',
            DefineCode::VIP    => 'Vip',
        ];
        $city = Taxonomy::where('type', 'city')->pluck('name', 'id')->toArray();
        view()->share([
            'gender' => $gender,
            'type'   => $type,
            'city'   => $city,
        ]);
    }

    /**
     * Function export excel user
     *
     */
    public function exportUser(Request $request)
    {
        $rq = $request->all();
        $data = User::where('role', DefineCode::ROLE_MEMBER)->orderBy('id', 'desc');
        if ($rq['search']) {
            $data = $data->where('name', 'like', '%' . $rq['search'] . '%')
                ->orWhere('email', 'like', '%' . $rq['search'] . '%')
                ->orWhere('phone', 'like', '%' . $rq['search'] . '%');
        }

        $data = $data->get();
        Excel::create('Danh sách User', function ($excel) use ($data) {
            $excel->sheet('Sheet 1', function ($sheet) use ($data) {
                $sheet->cell('A1:I1', function ($row) {
                    $row->setBackground('#008686');
                    $row->setFontColor('#ffffff');
                });
                $sheet->row(1, [
                    'ID',
                    'Name',
                    'Email',
                    'SĐT',
                    'Type',
                    'Address',
                    'Giới tính',
                    'Company',
                    'MST',
                ]);
                $i = 1;
                if ($data) {
                    foreach ($data as $k => $ex) {
                        $i++;
                        $sheet->row($i, [
                            @$ex->id,
                            @$ex->name,
                            @$ex->email,
                            @$ex->phone,
                            (@$ex->role == DefineCode::ROLE_MEMBER) ? 'Tài khoản cá nhân' : 'Tài khoản công ty',
                            @$ex->address,
                            (@$ex->gender == DefineCode::MALE) ? 'Nam' : 'Nữ',
                            @$ex->company,
                            @$ex->tax_code,
                        ]);
                    }
                }
            });
        })->export('xlsx');
    }

    /*
     * Function import user
     */
    public function importUser(Request $request)
    {
        if ($request->hasFile('file')) {
            Excel::load($request->file('file')->getRealPath(), function ($render) {
                $result = $render->toArray();
                foreach ($result as $k => $row) {
                    $phone = User::where('phone', $row['sdt'])->first();
                    $email = User::where('email', $row['email'])->first();
                    $mst = User::where('tax_code', $row['mst'])->first();
                    if ((!$phone || !$email || !$mst) && $row['id']) {
                        User::create([
                            'name'         => @$row['name'],
                            'password'     => \bcrypt('123456'),
                            'email'        => @$row['email'],
                            'phone'        => @$row['sdt'],
                            'role'         => $row['type'] == 'Tài khoản cá nhân' ? DefineCode::ROLE_MEMBER
                                : DefineCode::ROLE_COMPANY,
                            'address'      => @$row['address'],
                            'gender'       => @$row['gioi_tinh'] == 'Nam' ? DefineCode::MALE : DefineCode::FEMALE,
                            'company'      => @$row['company'],
                            'tax_code'     => @$row['mst'],
                            'verification' => DefineCode::NOT_VERIFICATION,
                        ]);
                    }
                }
            });
        }
        return redirect('backend/user')->with('success', 'Import user thành công');
    }

    /*
     * Function get district
     */
    public function district(Request $request, $id)
    {
        $district = Taxonomy::where('parent_id', $id)->get();
        if (count($district)) {
            return response()->json($district);
        } else {
            return response()->json();
        }
    }

    /**
     * @param $id
     * @param $status
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verification($id, $status)
    {
        $user = User::where('id', $id)->first();
        if ($user) {
            $user->update(['verification' => $status]);
        }
        return redirect()->back()->with('success', 'Cập nhật thành công');
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getUserCompany(Request $request)
    {
        $data = User::whereIn('role', [DefineCode::ROLE_COMPANY, DefineCode::ROLE_INVESTOR])->orderBy('id', 'desc');
        if ($request->search) {
            $data = $data->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%')
                ->orWhere('phone', 'like', '%' . $request->search . '%')
                ->orWhere('company', 'like', '%' . $request->search . '%')
                ->orWhere('tax_code', 'like', '%' . $request->search . '%');
        }
        $data = $data->get();
        return view('backend.pages.user.usercompany', compact('data'));
    }

    /**
     * Function export excel user
     *
     */
    public function exportCompany(Request $request)
    {
        $rq = $request->all();
        $data = User::whereIn('role', [DefineCode::ROLE_MEMBER, DefineCode::ROLE_INVESTOR])->orderBy('id', 'desc');
        if ($rq['search']) {
            $data = $data->where('name', 'like', '%' . $rq['search'] . '%')
                ->orWhere('email', 'like', '%' . $rq['search'] . '%')
                ->orWhere('phone', 'like', '%' . $rq['search'] . '%')
                ->orWhere('company', 'like', '%' . $rq['search'] . '%')
                ->orWhere('tax_code', 'like', '%' . $rq['search'] . '%');
        }

        $data = $data->get();
        Excel::create('Danh sách Công Ty', function ($excel) use ($data) {
            $excel->sheet('Sheet 1', function ($sheet) use ($data) {
                $sheet->cell('A1:H1', function ($row) {
                    $row->setBackground('#008686');
                    $row->setFontColor('#ffffff');
                });
                $sheet->row(1, [
                    'ID',
                    'Name',
                    'Email',
                    'SĐT',
                    'Type',
                    'Address',
                    'Company',
                    'MST',
                ]);
                $i = 1;
                if ($data) {
                    foreach ($data as $k => $ex) {
                        $i++;
                        $sheet->row($i, [
                            @$ex->id,
                            @$ex->name,
                            @$ex->email,
                            @$ex->phone,
                            'Tài khoản công ty',
                            @$ex->address,
                            @$ex->company,
                            @$ex->tax_code,
                        ]);
                    }
                }
            });
        })->export('xlsx');
    }
}
