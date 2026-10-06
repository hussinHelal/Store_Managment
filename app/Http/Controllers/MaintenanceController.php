<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use App\Http\Requests\SaveMaintenanceRequest;
use App\Services\PictureStorageService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MaintenanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $maintenances = Maintenance::when($request->filled('search'), function ($query) use ($request) {
                $search = '%' . $request->input('search') . '%';
                $query->where('name', 'like', $search);
            })
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('Maintenance.index', compact('maintenances'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('Maintenance.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SaveMaintenanceRequest $request, PictureStorageService $pictures)
    {
        $validated = $request->validated();
        $imagePath = $request->hasFile('image') ? $pictures->store($request->file('image'), 'maintenance') : null;
        unset($validated['image'], $validated['remove_image']);
        $validated['image_path'] = $imagePath;

        try {
            DB::transaction(fn () => Maintenance::create($validated));
        } catch (\Throwable $exception) {
            $pictures->delete($imagePath, 'maintenance');
            throw $exception;
        }

        return redirect()->route('maintenance.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Maintenance $maintenance)
    {
        return view('Maintenance.show', compact('maintenance'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Maintenance $maintenance)
    {
        return view('Maintenance.edit', compact('maintenance'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SaveMaintenanceRequest $request, Maintenance $maintenance, PictureStorageService $pictures)
    {
        $validated = $request->validated();
        if (($validated['status'] ?? null) === 'مكتمل') {
            $validated['completed_date'] = $maintenance->completed_date ?? now();
        } else {
            $validated['completed_date'] = null;
        }
        $oldImage = $maintenance->image_path;
        $removeImage = (bool) ($validated['remove_image'] ?? false);
        $newImage = $request->hasFile('image') ? $pictures->store($request->file('image'), 'maintenance') : null;
        unset($validated['image'], $validated['remove_image']);
        if ($removeImage && $newImage === null) {
            $validated['image_path'] = null;
        } elseif ($newImage !== null) {
            $validated['image_path'] = $newImage;
        }

        try {
            DB::transaction(fn () => $maintenance->update($validated));
        } catch (\Throwable $exception) {
            $pictures->delete($newImage, 'maintenance');
            throw $exception;
        }

        if ($newImage !== null || $removeImage) {
            $pictures->delete($oldImage, 'maintenance');
        }

        return redirect()->route('maintenance.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Maintenance $maintenance, PictureStorageService $pictures)
    {
        $imagePath = $maintenance->image_path;
        $maintenance->delete();
        $pictures->delete($imagePath, 'maintenance');

        return redirect()->route('maintenance.index');
    }

    public function showRepaired(Maintenance $maintenance)
    {
        return view('Maintenance.repaired', compact('maintenance'));
    }

    public function repaired(Request $request, Maintenance $maintenance)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['مكتمل', 'مرفوض'])],
        ]);

        $maintenance->update([
            'status' => $validated['status'],
            'completed_date' => $validated['status'] === 'مكتمل' ? now() : null,
        ]);

        return redirect()->route('maintenance.index');
    }
}
