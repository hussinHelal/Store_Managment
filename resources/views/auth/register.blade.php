@extends('layouts.app')

@section('content')
    <div class="auth-container d-flex align-items-center justify-content-center" style="min-height: calc(100vh - 56px);">
        <div class="auth-form w-100" style="max-width: 400px;">
            <h2 class="mb-4 text-center fw-bold">إنشاء حساب</h2>
            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">الاسم</label>
                    <input type="text" class="form-control" name="name" id="name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">البريد الإلكتروني</label>
                    <input type="email" class="form-control" name="email" id="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">كلمة المرور</label>
                    <input type="password" class="form-control" name="password" id="password" required>
                </div>
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">تأكيد كلمة المرور</label>
                    <input type="password" class="form-control" name="password_confirmation" id="password_confirmation" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">إنشاء حساب</button>
            </form>
            <div class="mt-3 text-center">
                <a href="{{ route('home') }}" class="btn btn-danger w-100">رجوع</a>
            </div>
        </div>
    </div>
@endsection
