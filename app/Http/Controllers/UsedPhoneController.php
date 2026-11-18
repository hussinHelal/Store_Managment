<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveUsedPhoneRequest;
use App\Models\UsedPhone;
use App\Services\PictureStorageService;
use App\Services\UsedPhoneService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UsedPhoneController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:in_stock,reserved,sold,returned'],
        ]);
        $search = trim($filters['search'] ?? '');
        $phones = UsedPhone::query()
            ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                ->where('brand', 'like', "%{$search}%")
                ->orWhere('model', 'like', "%{$search}%")
                ->orWhere('imei', 'like', "%{$search}%")
                ->orWhere('seller_name', 'like', "%{$search}%")))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest('id')->paginate(15)->withQueryString();

        return view('used_phones.index', compact('phones', 'filters'));
    }

    public function store(SaveUsedPhoneRequest $request, UsedPhoneService $service, PictureStorageService $pictures)
    {
        $path = $request->hasFile('image') ? $pictures->store($request->file('image'), 'used-phones') : null;
        try {
            $service->create($request->validated(), (int) $request->user()->id, $path);
        } catch (\Throwable $exception) {
            $pictures->delete($path, 'used-phones');
            throw $exception;
        }

        return redirect()->route('used_phones.index')->with('success', 'تم تسجيل الجهاز المستعمل بنجاح.');
    }

    public function update(SaveUsedPhoneRequest $request, UsedPhone $usedPhone, UsedPhoneService $service, PictureStorageService $pictures)
    {
        $newPath = $request->hasFile('image') ? $pictures->store($request->file('image'), 'used-phones') : null;
        try {
            [, $oldPath] = $service->update($usedPhone, $request->validated(), $newPath);
        } catch (\Throwable $exception) {
            $pictures->delete($newPath, 'used-phones');
            throw $exception;
        }
        $pictures->delete($oldPath, 'used-phones');

        return redirect()->route('used_phones.index')->with('success', 'تم تحديث بيانات الجهاز بنجاح.');
    }

    public function destroy(UsedPhone $usedPhone, UsedPhoneService $service, PictureStorageService $pictures)
    {
        try {
            $imagePath = $service->delete($usedPhone);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }
        $pictures->delete($imagePath, 'used-phones');

        return redirect()->route('used_phones.index')->with('success', 'تم حذف الجهاز من السجلات.');
    }
}
