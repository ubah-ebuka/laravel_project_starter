<?php

namespace App\Traits;

use App\Enums\RequestActionEnum;

trait ApiResponse
{
    public function successResponse($data = null, $message = 'Request was successful', $statusCode = 200)
    {
        return response()->json([
            'status' => 'success',
            'response_code' => RequestActionEnum::SUCCESS->value,
            'message' => $message,
            'data' => $data,
            'errors' => null,
        ], $statusCode);
    }

    public function failedResponse($message = 'Request failed', $statusCode = 422, $responseCode = RequestActionEnum::REQUEST_ERROR, $errors = null)
    {
        return response()->json([
            'status' => 'failed',
            'response_code' => $responseCode->value,
            'message' => $message,
            'data' => null,
            'errors' => $errors,
        ], $statusCode);
    }

    public function errorResponse($message = 'Unknown error', $statusCode = 500, $responseCode = RequestActionEnum::SERVER_ERROR)
    {
        return response()->json([
            'status' => 'error',
            'response_code' => $responseCode->value,
            'message' => $message,
            'data' => null,
            'errors' => null,
        ], $statusCode);
    }
}