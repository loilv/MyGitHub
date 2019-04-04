<?php

namespace App\Http\Controllers\Backend;

use App\Http\Services\DocumentService;
use App\Models\Document;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DocumentController extends Controller
{

    public function __construct(DocumentService $documentService)
    {
        $this->serve = $documentService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = Document::orderBy('id', 'desc');
        if ($request->search) {
            $data = $data->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->status) {
            $data = $data->where('type', $request->status);
        }
        $data = $data->get();
        $status = [
            'video'    => 'Video',
            'catalog'  => 'Catalog',
            'document' => 'Document',
        ];
        $this->countParams();
        return view('backend.pages.document.index', compact('data', 'status'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'Thêm Document';
        return view('backend.pages.document._form', compact('title'));
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
        $this->serve->createDocument($request);
        return redirect('backend/document')->with('success', 'Thêm document thành công');
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
    public function edit(Document $document)
    {
        $title = 'Sửa Document';
        return view('backend.pages.document._form', compact('title', 'document'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Document $document)
    {
        $this->serve->updateDocument($request, $document);
        return redirect('backend/document')->with('success', 'Cập nhật document thành công');
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
        $this->serve->deleteDocument($id);
        return redirect()->back()->with('success', 'Xóa document thành công');
    }

    /**
     *
     */
    private function countParams()
    {
        $totalVideo      = Document::where('type', 'video')->get()->count();
        $totalCatalog    = Document::where('type', 'catalog')->get()->count();
        $totalDocument   = Document::where('type', 'document')->get()->count();
        view()->share([
            'totalVideo'    => $totalVideo,
            'totalCatalog'  => $totalCatalog,
            'totalDocument' => $totalDocument,
        ]);
    }
}
