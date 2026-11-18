<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\SaveBranchRequest;
use App\Http\Resources\Api\BranchResource;
use App\Models\Branch;
use App\Services\BranchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BranchController
{
    public function index(): AnonymousResourceCollection
    {
        return BranchResource::collection(Branch::query()->orderByDesc('is_main')->orderBy('name')->paginate(100));
    }

    public function store(SaveBranchRequest $request, BranchService $branches): JsonResponse
    {
        $branch = $branches->create($request->validated());

        return response()->json(['data' => (new BranchResource($branch))->resolve($request)], 201);
    }

    public function update(SaveBranchRequest $request, Branch $branch, BranchService $branches): BranchResource
    {
        return new BranchResource($branches->update($branch, $request->validated()));
    }

    public function destroy(Branch $branch, BranchService $branches): JsonResponse
    {
        $branches->delete($branch);

        return response()->json(null, 204);
    }
}
