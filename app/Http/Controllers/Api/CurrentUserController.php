<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrentUserController
{
    public function __invoke(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}