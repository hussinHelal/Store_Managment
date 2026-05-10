<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user  = Auth::user();

        return response()->json([
            'user'  => $user,
        ]);
    }
    
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed', 
        ]);
    
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);
    
        // $user->assignRole('cashier'); 
    
        // $token = $user->createToken('shop-token')->plainTextToken;
    
        return response()->json([
            'user'        => $user,
            // 'role'        => $user->getRoleNames()->first(),
            // 'permissions' => $user->getAllPermissions()->pluck('name'),
            'token'       => $token,
        ], 201);
    }
    
    public function logout(Request $request)
    {

        Auth::logout();

        return redirect()->route('login');
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
