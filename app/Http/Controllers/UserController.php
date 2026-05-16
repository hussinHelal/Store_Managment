<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('view-user');
        
        // Only superadmin can view all users
        if (!$request->user()->isSuperAdmin()) {
            abort(403, 'Only superadmin can view users.');
        }

        $users = User::paginate(15);
        return view('users.index', ['users' => $users]);
    }

    public function create(Request $request)
    {
        $this->authorize('create-user');
        
        // Only superadmin can create users
        if (!$request->user()->isSuperAdmin()) {
            abort(403, 'Only superadmin can create new users.');
        }

        return view('users.create', ['roles' => UserRole::options()]);
    }

    public function store(Request $request)
    {
        $this->authorize('create-user');
        
        // Only superadmin can create users
        if (!$request->user()->isSuperAdmin()) {
            abort(403, 'Only superadmin can create new users.');
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|in:cashier,admin,superadmin',
        ]);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role'     => $validated['role'],
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user, Request $request)
    {
        $this->authorize('update-user');
        
        // Only superadmin can modify user roles
        if (!$request->user()->isSuperAdmin()) {
            abort(403, 'Only superadmin can modify user roles.');
        }

        return view('users.edit', [
            'user'  => $user,
            'roles' => UserRole::options(),
        ]);
    }

    public function update(User $user, Request $request)
    {
        $this->authorize('update-user');
        
        // Only superadmin can modify user roles
        if (!$request->user()->isSuperAdmin()) {
            abort(403, 'Only superadmin can modify user roles.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:cashier,admin,superadmin',
        ]);

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user, Request $request)
    {
        $this->authorize('delete-user');
        
        // Only superadmin can delete users
        if (!$request->user()->isSuperAdmin()) {
            abort(403, 'Only superadmin can delete users.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
