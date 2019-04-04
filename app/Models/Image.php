<?php

namespace App\Models;

use App\Constants\DefineCode;
use Illuminate\Database\Eloquent\Model;
use JWTAuth;

class Image extends Model
{
    protected $table = 'images';
    protected $guarded = ['id'];

    /**
     * Function add avatar user current
     *
     * @param $data
     *
     * @return array
     */
    public function addAvatarUser($data)
    {
        $user             = auth('api')->user();
        $fileExt          = $data->getClientOriginalExtension();
        $file_name        = 'IMG-'.strtoupper(str_random(4)).'-'.$user->phone.'.'. $fileExt;
        $allowedExtension = ['png', 'jpg'];
        $destinationPath  = 'uploads/users';
        $image = Image::where('commom_id', $user->id)->first();

        if (in_array($fileExt, $allowedExtension)) {
            if ($image && $image->commom_id == $user->id) {
                $image->delete();
            }
            $data->move($destinationPath, $file_name);
            $image = Image::create(
                [
                    'name'      => $file_name,
                    'path'      => 'users',
                    'commom_id' => $user->id,
                    'alt'       => $destinationPath.'/' . $file_name,
                    'title'     => $file_name,
                ]
            );
            return $image;
        } else {
            return [
                'code'    => DefineCode::TYPE_IMAGE_ERROR,
                'message' => 'Type image error',
            ];
        }
    }
}
