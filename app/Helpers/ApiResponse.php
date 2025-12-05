<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success($message, $data = null, $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'status_code' => $code,
            'timestamp' => now()->toIso8601String(),
        ], $code);
    }

    public static function error($message, $code = 400, $data = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => $data,
            'status_code' => $code,
            'timestamp' => now()->toIso8601String(),
        ], $code);
    }
}
