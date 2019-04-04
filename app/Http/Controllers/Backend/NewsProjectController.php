<?php

namespace App\Http\Controllers\Backend;

use App\Constants\DefineCode;
use App\Models\User;
use App\Models\UserProject;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Services\NewsService;
use App\Models\NewsProject;
use Excel;
use App\Models\Taxonomy;

class NewsProjectController extends Controller
{

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
        $data = NewsProject::orderBy('id', 'desc');
        if ($request->search) {
            $data = $data->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('project_code', 'like', '%' . $request->search . '%');
        }
        $data = $data->get();
        return view('backend.pages.news.project', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'Thêm tin dự án';
        $this->generateParams();
        return view('backend.pages.news._formproject', compact('title'));
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
        $this->serve->createNewsProject($request);
        return redirect('backend/project')->with('success', 'Thêm tin dự án thành công');
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
    public function edit(NewsProject $project)
    {
        $title = 'Sửa tin dự án';
        $user_project = UserProject::where('project_id', $project->id)->get();
        $this->generateParams();
        $district = Taxonomy::where('parent_id', $project->city_id)->pluck('name', 'id')->toArray();
        return view('backend.pages.news._formproject', compact(
            'title',
            'project',
            'district',
            'user_project'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, NewsProject $project)
    {
        $this->serve->updateNewsProject($request, $project);
        return redirect('backend/project')->with('success', 'Cập nhật tin dự án thành công');
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
        $data = NewsProject::find($id);
        if ($data) {
            UserProject::where('project_id', $id)->delete();
            $data->delete();
        }
        return redirect()->back()->with('success', 'Xóa tin dự án thành công');
    }

    /**
     * Function param
     *
     */
    private function generateParams()
    {
        $city = Taxonomy::where('type', 'city')->pluck('name', 'id')->toArray();
        $investor = User::where('role', '<>', DefineCode::ROLE_ADMIN)->get();
        view()->share([
            'city'     => $city,
            'investor' => $investor,
        ]);
    }

    /*
     * Function import investor
     */
    public function importProject(Request $request)
    {
        if ($request->hasFile('file')) {
            Excel::load($request->file('file')->getRealPath(), function ($render) {
                $result = $render->formatDates(false)->toArray();
                foreach ($result as $k => $row) {
                    $project_code = NewsProject::where('project_code', $row['projectid'])->first();
                    if (!$project_code) {
                        NewsProject::create([
                            'project_code'     => @$row['projectid'],
                            'name'             => @$row['l_project_type'],
                            'name_description' => @$row['l_project_name'],
                            'status'           => @$row['l_project_status'],
                            'value'            => @$row['value'],
                            'owner_type'       => @$row['owner_type'],
                            'dev_type'         => @$row['dev_type'],
                            'site_area'        => @$row['site_area'],
                            'floor_area'       => @$row['floor_area'],
                            'storeys'          => @$row['storeys'],
                            'time_start'       => $row['const_start']
                                ? Carbon::createFromFormat('d/m/Y', $row['const_start'])
                                    ->format('Y-m-d H:i:s') : '',
                            'time_end'         => $row['const_end']
                                ? Carbon::createFromFormat('d/m/Y', $row['const_end'])
                                    ->format('Y-m-d') : '',
                            'time'             => $row['time_stamp']
                                ? Carbon::createFromFormat('d/m/Y H:i:s', $row['time_stamp'])
                                    ->format('Y-m-d H:i:s') : '',
                            'address'          => @$row['l_address'],
                            'country'          => @$row['l_country_name'],
                            'city_id'          => @$row['l_province'],
                            'district_id'      => @$row['l_town'],
                            'description'      => @$row['l_remarks'],
                        ]);
                    }
                }
            });
        }
        return redirect('backend/project')->with('success', 'Import tin dự án thành công');
    }

    public function exportProject(Request $request)
    {
        $rq = $request->all();
        $data = NewsProject::orderBy('id', 'desc');
        if ($rq['search']) {
            $data = $data->where('name', 'like', '%' . $rq['search'] . '%')
                ->orWhere('project_code', 'like', '%' . $rq['search'] . '%');
        }

        $data = $data->get();
        Excel::create('Danh sách TIN-DU-AN', function ($excel) use ($data) {
            $excel->sheet('Sheet 1', function ($sheet) use ($data) {
                $sheet->cell('A1:R1', function ($row) {
                    $row->setBackground('#008686');
                    $row->setFontColor('#ffffff');
                });
                $sheet->row(1, [
                    'Project_code',
                    'Name',
                    'Name_description',
                    'Status',
                    'Value',
                    'Owner_type',
                    'Dev_type',
                    'Site_area',
                    'Floor_area',
                    'Storeys',
                    'Time_start',
                    'Time_end',
                    'Time',
                    'Country',
                    'Address',
                    'Town',
                    'Province',
                    'Description',
                ]);
                $i = 1;
                if ($data) {
                    foreach ($data as $k => $ex) {
                        $i++;
                        $sheet->row($i, [
                            @$ex->project_code,
                            @$ex->name,
                            @$ex->name_description,
                            @$ex->status,
                            @$ex->value,
                            @$ex->owner_type,
                            @$ex->dev_type,
                            @$ex->site_area,
                            @$ex->floor_area,
                            @$ex->storeys,
                            @$ex->time_start,
                            @$ex->time_end,
                            @$ex->time,
                            @$ex->country,
                            @$ex->address,
                            @$ex->projectToDistrict->name,
                            @$ex->projectToCity->name,
                            @$ex->description,
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
            NewsProject::where('id', $del)->delete();
            UserProject::where('project_id', $del)->delete();
        }
        return 1;
    }
}
