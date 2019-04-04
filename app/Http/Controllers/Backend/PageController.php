<?php

namespace App\Http\Controllers\Backend;

use App\Models\Page;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $id = (isset($_GET['id'])) ? $_GET['id'] : false;
        if ($id) {
            $data['selectedPage'] = Page::find($id);
            if (!isset($data['selectedPage'])) {
                return redirect(\Request::url());
            }
        }
        $data['pages'] = Page::get()->toArray();
        $data['title'] = 'Giới thiệu PCCC';
        return view('backend.pages.page.index', compact('data'));
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
        $data = $request->all();
        if ($data['page'] != 0) {
            $row = Page::find($data['page']);
        } else {
            $row = new Page();
        }
        $row->title = $data['title'];
        $row->slug = str_slug($data['title']);
        $row->description = $data['description'];
        $row->save();
        return redirect(\Request::url() . '?id=' . $row->id);
    }
}
