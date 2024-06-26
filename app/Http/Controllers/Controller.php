<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function jsonResponse(array $data, int $code): JsonResponse
    {
        return response()->json($data, $code);
    }

    public function data(array $data = [], int $code = 200): JsonResponse
    {
        return $this->jsonResponse([
            'success' => true,
            'data' => $data,
        ],$code);
    }

    public function error(string $message = 'error', int $code = 400): JsonResponse
    {
        return $this->jsonResponse([
            'success' => false,
            'message' => $message,
        ], $code);
    }

    public function success(string $message = 'successful', int $code = 200): JsonResponse
    {
        return $this->jsonResponse([
            'success' => true,
            'message' => $message,
        ], $code);
    }
}
