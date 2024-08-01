<?php

namespace App\Http\Middleware\Api\Ai;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\ApiResponseHandler;

class BasicAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        $authorizationHeader = $request->header('Authorization');

        if (!$this->isValidBasicAuth($authorizationHeader)) {
            return ApiResponseHandler::error('UNAUTHORIZED', 401);
        }

        return $next($request);
    }

    private function isValidBasicAuth($authorizationHeader)
    {
        if (!$authorizationHeader) {
            return false;
        }

        list($type, $credentials) = explode(' ', $authorizationHeader, 2);

        if (strtolower($type) !== 'basic') {
            return false;
        }

        $credentials = base64_decode($credentials);
        list($username, $password) = explode(':', $credentials, 2);

        $validUsername = env('AI_BASIC_AUTH_USERNAME');
        $validPassword = env('AI_BASIC_AUTH_PASSWORD');

        return $username === $validUsername && $password === $validPassword;
    }
}
