<?php

if (!function_exists("apiResponse")) {
    function apiResponse($data = null, $message = "Success", $code = 200)
    {
        return response()->json([
            "message" => $message,
            "data" => $data,
        ], $code);
    }
}