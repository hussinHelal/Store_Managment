<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ProfileSettingsController extends Controller
{
    public function index(Request $request)
    {
        return view('profile.index', ['profile' => $request->user()]);
    }

    public function edit(User $profile, Request $request)
    {
        $user = $profile;
        abort_unless($request->user()->is($user), 404);

        return view('profile.edit', compact('user'));
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $validated = $request->validated();
        $oldPhoto = $user->photo;
        $newPhoto = null;
        $removePhoto = (bool) ($validated['remove_photo'] ?? false);
        $photoPath = public_path('uploads/users');

        if ($request->hasFile('photo')) {
            File::ensureDirectoryExists($photoPath);
            $newPhoto = (string) Str::uuid().'.'.$request->file('photo')->extension();
            $request->file('photo')->move($photoPath, $newPhoto);
        }

        $passwordChanged = filled($validated['password'] ?? null);
        $currentSessionId = $request->session()->getId();

        try {
            DB::transaction(function () use ($validated, $user, $newPhoto, $removePhoto, $passwordChanged, $currentSessionId): void {
                $user->forceFill([
                    'email' => $validated['email'],
                    'name' => $validated['name'],
                ]);

                if ($removePhoto) {
                    $user->photo = null;
                }

                if ($newPhoto !== null) {
                    $user->photo = $newPhoto;
                }

                if ($passwordChanged) {
                    $user->password = $validated['password'];
                }

                $user->save();

                if ($passwordChanged) {
                    $user->tokens()->delete();

                    if (Schema::hasTable('sessions')) {
                        DB::table('sessions')->where('user_id', $user->id)
                            ->where('id', '!=', $currentSessionId)->delete();
                    }
                }
            });
        } catch (\Throwable $exception) {
            if ($newPhoto !== null) {
                File::delete($photoPath.DIRECTORY_SEPARATOR.$newPhoto);
            }

            throw $exception;
        }

        if (($newPhoto !== null || $removePhoto) && $oldPhoto && basename($oldPhoto) === $oldPhoto) {
            File::delete($photoPath.DIRECTORY_SEPARATOR.$oldPhoto);
        }

        if ($passwordChanged) {
            $request->session()->regenerate();
        }

        return redirect()->route('profile.index')->with('success', 'Profile updated.');
    }
}