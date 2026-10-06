@extends('layouts.error-guest')

@section('content')
    <div class="auth-container d-flex align-items-center justify-content-center w-100">
        <div class="auth-form card shadow-sm border-0 w-100" style="max-width: 400px;">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="fa-solid fa-store fs-2 text-primary mb-2" aria-hidden="true"></i>
                    <div class="small text-body-secondary">{{ config('app.name', 'إدارة المتجر') }}</div>
                </div>
                <h2 class="mb-4 text-center fw-bold">تسجيل الدخول</h2>

                <form method="POST" action="{{ route('login') }}" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="username" class="form-label">اسم المستخدم</label>
                        <input type="text"
                               class="form-control @error('username') is-invalid @enderror"
                               name="username" id="username"
                               value="{{ old('username') }}"
                               autocomplete="username" required autofocus>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">كلمة المرور</label>
                        <input type="password"
                               class="form-control @error('password') is-invalid @enderror"
                               name="password" id="password"
                               autocomplete="current-password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" name="remember" id="remember" value="1" @checked(old('remember'))>
                        <label class="form-check-label" for="remember">تذكرني</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">تسجيل الدخول</button>
                </form>

            </div>
        </div>
    </div>
@endsection
