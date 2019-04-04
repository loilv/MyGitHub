<?php

namespace App\Http\ServiceAPIs;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Runner\Exception;
use App\Models\Image;
use App\Constants\DefineCode;

class ApiUser
{
    /**
     * Create User
     *
     * @param $data
     *
     * @return mixed
     */
    public function addUser($data)
    {
        if (!empty($data)) {
            $user = User::create(
                [
                    'name'           => $data['name'],
                    'phone'          => $data['phone'],
                    'email'          => isset($data['email']) && $data['email'] ? $data['email'] : "",
                    'password'       => Hash::make($data['password']),
                    'status'         => DefineCode::STATUS_CODE_ACTIVE,
                    'vip_package_id' => DefineCode::VIP_DEFAULT,
                    'role'           => DefineCode::ROLE_MEMBER,
                    'type'           => DefineCode::TYPE_USER_NEMBER,

                ]
            );
        }
        return $user;
    }

    /**
     * Update Info User******
     *
     * @param $data
     *
     * @return array
     */
    public function updateUser($data, $user_id)
    {
        try {
            if (!empty($data)) {
                if (isset($data['password']) && $data['password']) {
                    $data['password'] = Hash::make($data['password']);
                }

                if (isset($data['company']) && $data['company'] || isset($data['tax_code']) && $data['tax_code']) {
                    $data['role'] = DefineCode::ROLE_INVESTOR;
                }

                $user = User::find($user_id);
                $user->update($data);
            }
            return $user;
        } catch (\Exception $e) {
            return $user = [];
        }
    }

    /**
     * Function login social
     * @param $data
     *
     * @return mixed
     */
    public function addUserSocial($data)
    {
        if (!empty($data)) {
            $user = User::create(
                [
                    'name'           => $data['name'],
                    'phone'          => isset($data['phone']) && $data['phone'] ? $data['phone'] : "",
                    'email'          => isset($data['email']) && $data['email'] ? $data['email'] : "",
                    'password'       => "",
                    'status'         => DefineCode::STATUS_CODE_ACTIVE,
                    'vip_package_id' => DefineCode::VIP_DEFAULT,
                    'role'           => DefineCode::ROLE_MEMBER,
                    'type'           => DefineCode::TYPE_USER_NEMBER,
                    'token_social'   => isset($data['token']) && $data['token'] ? $data['token'] : "",
                    'login_type'     => $data['login_type'],
                ]
            );
        }
        return $user;
    }
}
