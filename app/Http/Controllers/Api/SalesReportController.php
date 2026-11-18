<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\SalesReportRequest;
use App\Services\SalesReportService;
use Illuminate\Http\JsonResponse;

class SalesReportController
{
    public function index(SalesReportRequest $request, SalesReportService $reports): JsonResponse
    {
        return response()->json(['data' => $reports->build($request->validated())]);
    }
}
