<?php

namespace App\Http\Middleware;

use App\Constants\ResponseStatusCode;
use JWTAuth;
use Closure;

class JwtMiddleware
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
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json([
                    'code'    => ResponseStatusCode::TOKEN_IS_INVALID,
                    'massage' => 'Token is Invalid',
                ]);
            } else {
                if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                    return response()->json([
                        'code'    => ResponseStatusCode::TOKEN_EXPIRED,
                        'message' => 'Token expired',
                    ]);
                } else {
                    return response()->json([
                        'code'    => ResponseStatusCode::TOKEN_NOT_FOUND,
                        'message' => 'Token not found',
                    ]);
                }
            }
        }
        return $next($request);
    }
}
