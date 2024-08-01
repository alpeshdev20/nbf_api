<?php

namespace App\Http\Middleware\Api\Ai;

use App\Helpers\ApiResponseHandler;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKey
{

    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key');

        if (!$this->isValidApiKey($apiKey)) {
            return ApiResponseHandler::error('INVALID_API_KEY', 401);
        }

        return $next($request);
    }

    private function isValidApiKey($apiKey)
    {
        return $apiKey === env('AI_API_KEY');
    }
}
