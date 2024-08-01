<?php

namespace App\Helpers;

class ApiResponseHandler
{
    public static function successWithData($data, $message, $status = 200)
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'response' => $data
        ], $status);
    }

    public static function success($message, $status = 200)
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
        ], $status);
    }

    public static function error($message, $status = 500)
    {
        return response()->json([
            'status' => $status,
            'message' => $message
        ], $status);
    }

    public static function errorWithData($data, $message, $status = 500)
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'response' => $data
        ], $status);
    }
}
