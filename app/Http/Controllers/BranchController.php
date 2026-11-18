<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveBranchRequest;
use App\Models\Branch;
use App\Services\BranchService;
use Illuminate\Http\RedirectResponse;

class BranchController extends Controller
{
    public function store(SaveBranchRequest $request, BranchService $branches): RedirectResponse
    {
        $branches->create($request->safe()->except('is_active'));

        return back()->with('success', 'تمت إضافة الفرع.');
    }

    public function update(SaveBranchRequest $request, Branch $branch, BranchService $branches): RedirectResponse
    {
        $branches->update($branch, $request->validated());

        return back()->with('success', 'تم تحديث بيانات الفرع.');
    }

    public function destroy(Branch $branch, BranchService $branches): RedirectResponse
    {
        $branches->delete($branch);

        return back()->with('success', 'تم حذف الفرع.');
    }
}
