@extends('layouts.app')

@section('main')
    <form method="POST" action="{{ route('login') }}">
      @csrf

      <div class="mb-3">
        <label for="email" class="form-label">البريد الإلكتروني</label>
        <input type="email" class="form-control" name="email" id="email">
      </div>
      <div class="mb-3">
        <label for="password" class="form-label">كلمة المرور</label>
        <input type="password" class="form-control" name="password" id="password">
      </div>
      <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="remember">
        <label class="form-check-label" for="remember">تذكرني</label>
      </div>
      <button type="submit" class="btn btn-primary">تسجيل الدخول</button>
    </form>
     <div class="mt-3">
        <a href="{{ route('home') }}" class="btn btn-danger">رجوع</a>
    </div>
@endsection
