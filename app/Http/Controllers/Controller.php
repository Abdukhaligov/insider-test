<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class Controller
{
    protected function handleError(\Exception $e): JsonResponse
    {
        return response()->json(
            ['message' => 'Something went wrong.', 'error' => $e->getMessage()],
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }
}
