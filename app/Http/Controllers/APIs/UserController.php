<?php

namespace App\Http\Controllers\APIs;

use App\Constants\DefineCode;
use App\Constants\DocumentCode;
use App\Constants\ResponseStatusCode;
use App\Http\Requests\UserRequest;
use App\Http\Resources\ImageResource;
use App\Models\Image;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use JWTAuth;
use App\Http\ServiceAPIs\ApiUser;
use Illuminate\Support\Facades\Hash;
use Mockery\Exception;

class UserController extends Controller
{
    protected $user = '';
    protected $api_user = '';


    public function __construct(User $user, ApiUser $api_user)
    {
        $this->user = $user;
        $this->api_user = $api_user;
    }

    /**
     * Register **************
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        if (!empty($request->all())) {
            try {
                $check_phone = User::where('phone', $request->phone)->where('email', '!=', null)->first();
                $check_email = User::where('email', $request->email)->where('email', '!=', null)->first();
                if (!$check_phone && !$check_email) {
                    $user = $this->api_user->addUser($request->all());
                    $token = JWTAuth::fromUser($user);
                    return response()->json(
                        [
                            'code'    => ResponseStatusCode::OK,
                            'message' => 'successfuly',
                            'data'    => $user,
                            'token'   => $token,
                        ]
                    );
                } elseif (!$check_phone && $check_email) {
                    return response()->json(
                        [
                            'code'    => ResponseStatusCode::EMAIL_ALREADY_EXIST,
                            'message' => 'Email already exist',
                        ]
                    );
                } else {
                    return response()->json(
                        [
                            'code'    => ResponseStatusCode::PHONE_ALREADY_EXIST,
                            'message' => 'Phone already exist',
                        ]
                    );
                }
            } catch (Exception $e) {
                return response()->json(
                    [
                        'code'    => ResponseStatusCode::INTERNAL_SERVER_ERROR,
                        'message' => 'Server Error',
                    ]
                );
            }
        }
    }

    /**
     * Function Login with email or phone
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->all();
        try {
            if (isset($credentials['username']) && $credentials['username'] != null) {
                $user = User::where('phone', $credentials['username'])
                    ->orWhere('email', $credentials['username'])
                    ->first();

                if ($user && Hash::check($credentials['password'], $user->password)) {
                    $token = JWTAuth::fromUser($user);
                    return response()->json(
                        [
                            'code'  => ResponseStatusCode::OK,
                            'data'  => $user,
                            'token' => $token,
                        ]
                    );
                } else {
                    return response()->json(
                        [
                            'code'    => ResponseStatusCode::NOT_FOUND,
                            'message' => 'Sai tài khoản hoặc mật khẩu',
                        ]
                    );
                }
            }
        } catch (Exception $e) {
            return response()->json(
                [
                    'code'    => ResponseStatusCode::INTERNAL_SERVER_ERROR,
                    'message' => 'Server Error',
                ]
            );
        }
    }

    /**
     * Function login social facebook && google
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginSocial(Request $request)
    {
        if (!empty($request->all())) {
            try {
                if ($request->login_type == DefineCode::FACEBOOK) {
                    $user = User::select('*')
                        ->where('token_social', $request->token)
                        ->first();

                    if (!$user) {
                        $user = $this->api_user->addUserSocial($request->all());
                    }
                    $token = JWTAuth::fromUser($user);
                    return response()->json(
                        [
                            'code'  => ResponseStatusCode::OK,
                            'data'  => $user,
                            'token' => $token,
                        ]
                    );
                } elseif ($request->login_type == DefineCode::GOOGLE) {
                    $user = User::select('*')
                        ->where('email', $request->email)
                        ->first();

                    if (!$user) {
                        $user = $this->api_user->addUserSocial($request->all());
                    }

                    $token = JWTAuth::fromUser($user);
                    return response()->json(
                        [
                            'code'  => ResponseStatusCode::OK,
                            'data'  => $user,
                            'token' => $token,
                        ]
                    );
                }
            } catch (\Exception $e) {
                return response()->json(
                    [
                        'code'    => ResponseStatusCode::INTERNAL_SERVER_ERROR,
                        'message' => "Server Error",
                    ]
                );
            }
        }
    }

    /**
     * get Info User current
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser()
    {
        try {
            $user = auth('api')->user();
            $image = Image::where('commom_id', $user->id)->first();
            if ($image) {
                $image = new ImageResource($image);
            }

            $data = [];
            $data['name'] = $user->name;
            $data['image'] = $image;
            $data['phone'] = $user->phone;
            $data['email'] = $user->email;
            $data['gender'] = $user->gender;
            $data['address'] = $user->address;
            $data['company'] = $user->company;
            $data['tax_code'] = $user->tax_code;
            return response()->json(
                [
                    'code' => ResponseStatusCode::OK,
                    'data' => $data,
                ]
            );
        } catch (\Exception $e) {
            return response()->json(
                [
                    'code'    => ResponseStatusCode::INTERNAL_SERVER_ERROR,
                    'message' => "Server Error",
                ]
            );
        }
    }

    /**
     * Function Update Info User
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateInfoUser(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $user_id = $user->id;

        if (!empty($request->all())) {
            $user = User::select('*')->where('email', $request->email)->where('email', '!=', null)->first();
            $user_tax_code = User::where('tax_code', $request->tax_code)->where('tax_code', '!=', null)->first();
            if (!$user && !$user_tax_code) {
                $user = $this->api_user->updateUser($request->all(), $user_id);

                if (!empty($user)) {
                    return response()->json(
                        [
                            'code' => ResponseStatusCode::OK,
                            'data' => $user,
                        ]
                    );
                } else {
                    return response()->json(
                        [
                            'code'    => ResponseStatusCode::INTERNAL_SERVER_ERROR,
                            'message' => "Server Error",
                        ]
                    );
                }
            } elseif ($user && !$user_tax_code) {
                return response()->json(
                    [
                        'code'    => ResponseStatusCode::EMAIL_ALREADY_EXIST,
                        'message' => "Email already exist",
                    ]
                );
            } elseif (!$user && $user_tax_code) {
                return response()->json(
                    [
                        'code'    => ResponseStatusCode::TAX_CODE_ALREADY_EXIST,
                        'message' => "Tax code already exist",
                    ]
                );
            }
        }
    }

    /**
     * Function check Phone && Email
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkPhoneAndEmail(Request $request)
    {
        if (!empty($request->all())) {
            if (isset($request->phone) && $request->phone) {
                $check_phone = User::where('phone', $request->phone)
                    ->where('phone', '!=', null)
                    ->first();

                if (!empty($check_phone)) {
                    $token = JWTAuth::fromUser($check_phone);
                    return response()->json([
                        'code'    => ResponseStatusCode::PHONE_ALREADY_EXIST,
                        'message' => "Phone already exist",
                        'token'   => $token,
                    ]);
                } else {
                    return response()->json([
                        'code'    => ResponseStatusCode::OK,
                        'message' => "Passed",
                    ]);
                }
            } elseif (isset($request->email) && $request->email) {
                $check_email = User::where('email', $request->email)
                    ->where('phone', '!=', null)
                    ->first();

                if (!empty($check_email)) {
                    $token = JWTAuth::fromUser($check_email);
                    return response()->json([
                        'code'    => ResponseStatusCode::EMAIL_ALREADY_EXIST,
                        'message' => "Email already exist",
                        'token'   => $token,
                    ]);
                } else {
                    return response()->json([
                        'code'    => ResponseStatusCode::OK,
                        'message' => "Passed",
                    ]);
                }
            }
        }
    }

    /**
     * Function forgot password on user
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request)
    {
        $user = auth('api')->user();
        if (!empty($user)) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'code'    => ResponseStatusCode::OK,
                'message' => 'Update password successfully',
            ]);
        } else {
            return response()->json([
                'code'    => ResponseStatusCode::TOKEN_NOT_FOUND,
                'message' => 'Token Not found',
            ]);
        }
    }

    /**
     * Function change password on user
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $user = auth('api')->user();
        if ($user && Hash::check($request['old_password'], $user->password)) {
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            return response()->json([
                'code'    => ResponseStatusCode::OK,
                'message' => 'Update password successfully',
            ]);
        } else {
            return response()->json([
                'code'    => ResponseStatusCode::PASSWORD_NOT_FOUND,
                'message' => 'Mật khẩu cũ nhập vào không đúng',
            ]);
        }
    }

    /**
     * Function update phone login social
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePhone(Request $request)
    {
        try {
            $user = auth('api')->user();
            $check_user = User::where('phone', $request->phone)->first();
            if ($user->login_type == DefineCode::FACEBOOK && $check_user) {
                $check_user->update([
                        'token_social' => $user->token_social,
                    ]);
                $user->delete();
                return response()->json([
                    'code' => ResponseStatusCode::OK,
                    'data' => $check_user,
                ]);
            } elseif ($user->login_type == DefineCode::GOOGLE && $check_user) {
                $check_user->update([
                        'email' => $user->email,
                    ]);
                $user->delete();
                return response()->json([
                    'code' => ResponseStatusCode::OK,
                    'data' => $check_user,
                ]);
            } elseif (!$check_user) {
                $user->update([
                    'phone' => $request->phone,
                ]);
                return response()->json([
                    'code' => ResponseStatusCode::OK,
                    'data' => $user,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(
                [
                    'code'    => ResponseStatusCode::INTERNAL_SERVER_ERROR,
                    'message' => "Server Error",
                ]
            );
        }
    }
}
