<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Constants\DefineCode;

class LoginController extends Controller
{
    /**
     * function return view
     *
     * @return view
     * */
    public function getLogin()
    {
        if (Auth::check()) {
            $role = Auth::user()->role;
            if ($role == DefineCode::ROLE_ADMIN) {
                return redirect('/backend/dashboard');
            }
        }
        return view('auth.login');
    }

    /**
     * function login
     *
     * @return view
     * */
    public function postLogin(Request $request)
    {
        $credentials = [
            'email'    => $request['email'],
            'password' => $request['password'],
        ];

        $status = 'Username hoặc mật khẩu không chính xác.';

        if (Auth::attempt($credentials)) {
            $role = Auth::user()->role;
            if ($role == DefineCode::ROLE_ADMIN) {
                return redirect('/backend/dashboard');
            }
        }
        return view('auth.login', compact('status'));
    }

    /**
     * function return view
     *
     * @return view
     * */
    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}
