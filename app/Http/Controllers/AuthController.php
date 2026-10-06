<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    /** Failed attempts allowed per username + IP before a temporary lockout. */
    private const MAX_ATTEMPTS = 5;

    /** Lockout window in seconds. */
    private const DECAY_SECONDS = 60;

    public function showLogin(): View|RedirectResponse
    {
        // Already signed in: no reason to show the login form again.
        if (Auth::guard('web')->check()) {
            return redirect()->route('home');
        }

        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->safe()->only(['username', 'password']);
        $throttleKey = $this->throttleKey($credentials['username'], $request);

        // Brute-force protection (per username + IP).
        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'username' => "محاولات تسجيل دخول كثيرة. حاول مرة أخرى بعد {$seconds} ثانية.",
            ])->redirectTo(route('showLogin'));
        }

        $authenticated = Auth::guard('web')->attempt([
            'username'  => $credentials['username'],
            'password'  => $credentials['password'],
            'is_active' => true,
        ], $request->boolean('remember'));

        if (! $authenticated) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            // One generic message for wrong username, wrong password, and
            // inactive account, so attackers can't tell which accounts exist.
            throw ValidationException::withMessages([
                'username' => 'اسم المستخدم أو كلمة المرور غير صحيحة.',
            ])->redirectTo(route('showLogin'));
        }

        RateLimiter::clear($throttleKey);

        // Prevent session fixation.
        $request->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = $request->user();
        $token = $user?->currentAccessToken();

        // Session logins carry a TransientToken, which is not stored in the
        // database, so only real Sanctum tokens are deleted.
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('showLogin');
    }

    public function user(Request $request): JsonResponse
    {
        // Make sure the User model's $hidden contains password and
        // remember_token (it does by default in a fresh Laravel app).
        return response()->json($request->user());
    }

    private function throttleKey(string $username, Request $request): string
    {
        return Str::transliterate(Str::lower($username) . '|' . $request->ip());
    }
}