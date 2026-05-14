<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\profile;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $profiles = profile::all();
        $user = User::findOrFail(auth()->user()->id);
        return view('profile.index', compact('user'));
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
    public function update(Request $request, profile $profile, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'nullable|string|min:8|confirmed',
        ]);
        if(isDirty($validated)) {
            $user->update($validated);
            $profile->update($validated);
        }
        return redirect()->route('profile.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(profile $profile,$id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        $profile->delete();
        return redirect()->route('profile.index');
    }
}
