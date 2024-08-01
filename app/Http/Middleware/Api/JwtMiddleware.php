<?php

namespace App\Http\Middleware\Api;

use App\Helpers\ApiResponseHandler;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $token = $request->header('x-access-token');
            if (!$token && $request->hasCookie('session')) {
                $token = $request->cookie('session');
            }

            if (!$token) {
                return response()->json([
                    'status' => 401,
                    'message' => "UNAUTHORIZED_ACCESS",
                ], 401)->withCookie(Cookie::forget('session'));
            }

            auth('customers')->setToken($token)->authenticate();
            return $next($request);
        } catch (\Exception $e) {
            return ApiResponseHandler::error("UNAUTHORIZED_ACCESS", 401);
        }
    }
}
