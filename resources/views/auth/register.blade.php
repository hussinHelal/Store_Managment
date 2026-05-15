@extends('layouts.app')

@section('content')
    <form method="POST" action="{{ route('register') }}">
      @csrf
      @if ($errors->any())
          <div class="alert alert-danger">
              <ul class="mb-0">
                  @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                  @endforeach
              </ul>
          </div>
      @endif
      
      <div class="mb-3">
        <label for="name" class="form-label">الاسم</label>
        <input type="text" class="form-control" name="name" id="name">
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">البريد الإلكتروني</label>
        <input type="email" class="form-control" name="email" id="email">
      </div>
      
      <div class="mb-3">
        <label for="password" class="form-label">كلمة المرور</label>
        <input type="password" class="form-control" name="password" id="password">
      </div>
      <div class="mb-3">
        <label for="password_confirmation" class="form-label">تأكيد كلمة المرور</label>
        <input type="password" class="form-control" name="password_confirmation" id="password_confirmation">
      </div>
      
      <button type="submit" class="btn btn-primary">إنشاء حساب</button>
      
    </form>
@endsection