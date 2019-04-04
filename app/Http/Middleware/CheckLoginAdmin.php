<?php

namespace App\Http\Middleware;

use App\Constants\DefineCode;
use Closure;
use Auth;

class CheckLoginAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check() && ((Auth::user()->role == DefineCode::ROLE_ADMIN) ||
                (Auth::user()->role == DefineCode::ROLE_MEMBER))) {
            return $next($request);
        }
        return redirect('/login');
    }
}
