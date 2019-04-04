<?php

namespace App\Http\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Image;
use App\Constants\DefineCode;

class UserService
{
    /*
     * Function create user
     *
     * @param $request
     */
    public function createUser($request)
    {
        $password = bcrypt($request->password);
        $birth_day = $request->birth_day
            ? Carbon::createFromFormat('d/m/Y', $request->birth_day)
                ->format('Y-m-d') : null;
        if ($request->role == DefineCode::ROLE_COMPANY) {
            $verification = DefineCode::NOT_VERIFICATION;
        } else {
            $verification = null;
        }
        $request->merge([
            'password'     => $password,
            'birth_day'    => $birth_day,
            'verification' => $verification,
        ]);
        $user = User::create($request->all());
        if ($request->hasFile('image')) {
            $path = 'uploads/users';
            $file = $request->image;
            $request = new Request($request->all());
            $data = \Func::uploadImage($file, $path);
            $request->merge(['imgname' => $data['image'], 'path' => $data['path']]);
            $imggg = [
                'name'      => $request->imgname,
                'path'      => 'users',
                'commom_id' => $user->id,
            ];
            Image::create($imggg);
        }

        return $user;
    }

    /*
     * Function Update User
     *
     * @param $request $user
     */
    public function updateUser($request, $user)
    {
        if ($request->role == DefineCode::ROLE_COMPANY) {
            $verification = DefineCode::NOT_VERIFICATION;
        } else {
            $verification = null;
        }
        $data = [
            'name'         => @$request->name,
            'email'        => @$request->email,
            'password'     => $request->password ? bcrypt($request->password) : $user->password,
            'gender'       => @$request->gender,
            'type'         => @$request->type,
            'phone'        => @$request->phone,
            'birth_day'    => $request->birth_day ? Carbon::createFromFormat('m/d/Y', $request->birth_day)
                ->format('Y-m-d') : null,
            'address'      => @$request->address,
            'role'         => @$request->role,
            'company'      => @$request->company,
            'tax_code'     => @$request->tax_code,
            'city_id'      => @$request->city_id,
            'district_id'  => @$request->district_id,
            'verification' => $verification,
        ];
        $user->update($data);
        if ($request->hasFile('image')) {
            $path = 'uploads/users';
            $file = $request->image;
            if (!$user->getImage) {
                $data = \Func::uploadImage($file, $path);
                $imggg = [
                    'name'      => $data['image'],
                    'path'      => 'users',
                    'commom_id' => $user->id,
                ];
                Image::create($imggg);
            } else {
                $image = $user->getImage();
                \File::delete('uploads/' . $user->getImage->path . '/' . $user->getImage->name);
                $data = \Func::uploadImage($file, $path);
                $imggg = [
                    'name'      => $data['image'],
                    'path'      => 'users',
                    'commom_id' => $user->id,
                ];
                $image->update($imggg);
            }
        }
    }

    /*
     * Function delete user
     *
     * @params $request
     */
    public function deleteUser($id)
    {
        $data = User::find($id);
        if ($data) {
            if ($data->getImage) {
                $img = $data->getImage->id;
                \File::delete('uploads/' . $data->getImage->path . '/' . $data->getImage->name);
                Image::destroy($img);
            }
            User::destroy($id);
        }
    }
}
