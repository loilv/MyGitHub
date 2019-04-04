<?php

namespace App\Http\Controllers\Backend;

use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class EmailTemplateController extends Controller
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
            $data['selectedEmail'] = EmailTemplate::find($id);
            if (!isset($data['selectedEmail'])) {
                return redirect(\Request::url());
            }
        }
        $data['emails'] = EmailTemplate::select('id', 'title')->get()->toArray();
        $data['title'] = 'Mẫu Email Gửi Thông Báo';
        return view('backend.pages.email.index', compact('data'));
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
        if ($data['title'] != 0) {
            $row = EmailTemplate::find($data['title']);
        } else {
            $row = new EmailTemplate();
        }
        $row->title = $data['subject'];
        $row->slug = str_slug($data['subject']);
        $row->subject = $data['subject'];
        $row->body = $data['body'];
        $row->save();
        return redirect(\Request::url() . '?id=' . $row->id);
    }
}
