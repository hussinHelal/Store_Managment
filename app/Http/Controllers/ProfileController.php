<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\profile;
use App\Models\User;
use App\Enums\UserRole;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $profile = auth()->user();
        $users = null;
        $roles = [];

        if ($profile->isSuperAdmin()) {
            $users = User::where('id', '!=', $profile->id)
                ->orderByRaw("CASE role WHEN 'superadmin' THEN 1 WHEN 'admin' THEN 2 WHEN 'cashier' THEN 3 ELSE 4 END")
                ->paginate(15);
            $roles = UserRole::options();
        }

        return view('profile.index', compact('profile', 'users', 'roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(profile $profile)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(profile $profile)
    {
        $user = User::findOrFail(auth()->user()->id);
        return view('profile.edit', compact('profile', 'user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        if ($request->user()->cannot('update', profile::class)) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $user = auth()->user();

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $folder = public_path('uploads/users');
            if (!file_exists($folder)) {
                mkdir($folder, 0755, true);
            }
            if ($user->photo && file_exists($folder . DIRECTORY_SEPARATOR . $user->photo)) {
                @unlink($folder . DIRECTORY_SEPARATOR . $user->photo);
            }
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9\.\-]/', '_', $photo->getClientOriginalName());
            $photo->move($folder, $filename);
            $validated['photo'] = $filename;
        }

        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('profile.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        if ($request->user()->cannot('destroy', profile::class)) {
            abort(403);
        }

        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('profile.index');
    }

    public function updateUserRole(Request $request, User $user)
    {
        if (!$request->user()->isSuperAdmin()) {
            abort(403, 'Only superadmin can assign roles.');
        }

        $validated = $request->validate([
            'role' => 'required|in:cashier,admin,superadmin',
        ]);

        $user->update(['role' => $validated['role']]);

        return redirect()->route('profile.index')->with('success', 'تم تحديث دور المستخدم بنجاح.');
    }
}
