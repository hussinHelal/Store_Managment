<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

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
            return redirect()->back()->withErrors(['email' => 'بيانات الدخول غير صحيحة'])->withInput();
        }

        $user  = Auth::user();

        return redirect()->route('home');
    }
    
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'nullable|in:cashier,admin,superadmin',
        ]);
    
        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role'     => $validated['role'] ?? 'cashier',
        ]);
    
        return redirect()->route('showLogin')->with('success', 'Registration successful. Please login.');
    }
    
    public function logout(Request $request)
    {

        Auth::logout();

        return redirect()->route('showLogin');
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
