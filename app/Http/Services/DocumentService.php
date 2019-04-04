<?php

namespace App\Http\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Document;

class DocumentService
{
    /*
     * Function create video
     *
     * @param $request
     */
    public function createDocument($request)
    {
        $path = 'uploads/document';
        if ($request->type == 'catalog') {
            if ($request->hasFile('link_id1')) {
                $file = $request->link_id1;
                $data = \Func::uploadImage($file, $path);
                $link_id = $data['image'];
            }
        } else {
            if ($request->type == 'document') {
                if ($request->hasFile('link_id2')) {
                    $file = $request->link_id2;
                    $data = \Func::uploadImage($file, $path);
                    $link_id = $data['image'];
                }
            } else {
                $link_id = $request->link_id;
            }
        }

        $request->merge([
            'date'    => $request->date
                ? Carbon::createFromFormat('d/m/Y', $request->date)->format('Y-m-d') : null,
            'link_id' => $link_id,
        ]);
        Document::create($request->except('link_id1', 'link_id2'));
    }

    /*
     * Function Update video
     *
     * @param $request $video
     */
    public function updateDocument($request, $document)
    {
//        dd($request->all());
        $path = 'uploads/document';
        if ($request->link_id) {
            $link_id = $request->link_id;
        } else {
            if ($request->hasFile('link_id1')) {
                \File::delete('uploads/document/' . $document->link_id);
                $file = $request->link_id1;
                $data = \Func::uploadImage($file, $path);
                $link_id = $data['image'];
            } else {
                if ($request->hasFile('link_id2')) {
                    \File::delete('uploads/document/' . $document->link_id);
                    $file = $request->link_id2;
                    $data = \Func::uploadImage($file, $path);
                    $link_id = $data['image'];
                } else {
                    $link_id = $document->link_id;
                }
            }
        }

        $updated = [
            'name'        => $request->name,
            'type'        => $request->type,
            'description' => $request->description,
            'link_id'     => $link_id,
        ];
        $document->update($updated);
    }

    /*
     * Function delete video
     *
     * @params $id
     */
    public function deleteDocument($id)
    {
        $data = Document::find($id);
        if ($data) {
            if ($data->type != 'video') {
                \File::delete('uploads/document/' . $data->link_id);
            }
            $data->delete();
        }
    }
}
