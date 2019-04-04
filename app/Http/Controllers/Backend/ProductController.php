<?php

namespace App\Http\Controllers\Backend;

use App\Models\Category;
use App\Models\Image;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Product::orderBy('id', 'desc')->get();
        return view('backend.pages.product.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'Thêm sản phẩm';
        $category = Category::where('parent_id', '<>', 0)->get();
        return view('backend.pages.product._form', compact('title', 'category'));
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
        $image = $request->file('image');
        $product = Product::create($request->except('image'));
        if ($image) {
            $path = 'uploads/products';
            $data = \Func::uploadImage($image, $path);
            Image::create([
                'name'      => $data['image'],
                'path'      => 'products',
                'commom_id' => $product->id,
            ]);
        }
        return redirect('backend/product')->with('success', 'Thêm sản phẩm thành công');
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
    public function edit(Product $product)
    {
        $category = Category::where('parent_id', '<>', 0)->get();
        return view('backend.pages.product._form', compact('product', 'category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $data = [
            'name'                => $request->name,
            'producer'            => $request->producer,
            'year_of_manufacture' => $request->year_of_manufacture,
            'category_id'         => $request->category_id,
        ];
        $product->update($data);
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
        $data = Product::find($id);
        if ($data) {
            if ($data->productToImage) {
                $img = $data->productToImage->id;
                \File::delete('uploads/' . $data->productToImage->path . '/' . $data->productToImage->name);
                Image::destroy($img);
            }
            Product::destroy($id);
        }
        return redirect()->back()->with('succes', 'Xóa thành công');
    }
}
