@extends('layouts.app')

@section('content')

    <span class="text-center border border-1 rounded text-bold">تحديث فاتورة</span>
    <form action="{{ route('profile.update', $user->id) }}" method="POST">
      @csrf
      @method('PUT')
      @if ($errors->any())
          <div class="alert alert-danger">
              <ul class="mb-0" style="list-style-type: none; padding: 0;">
                  @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                  @endforeach
              </ul>
          </div>
      @endif
      
      <div class="mb-3">
        <label for="name" class="form-label">الاسم</label>
        <input type="text" class="form-control" id="name" name="name" value="{{ $user->name }}">
      </div>
      
      <div class="mb-3">
        <label for="email" class="form-label">البريد الإلكتروني</label>
        <input type="email" class="form-control" id="email" name="email" value="{{ $user->email }}">
      </div>

      <div class="mb-3">
        <label for="password" class="form-label">كلمة المرور</label>
        <input type="password" class="form-control" id="password" name="password">
      </div>
      
      <div class="mb-3">
        <label for="password_confirmation" class="form-label">تأكيد كلمة المرور</label>
        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
      </div>
      
      {{-- <div class="mb-3">
        <label for="role" class="form-label">الدور</label>
        <select class="form-control" id="role" name="role">
          <option value="user" {{ $user->role == 'user' ? 'selected' : '' }}>مستخدم</option>
          <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>مدير</option>
        </select>
      </div>
       --}}

      <button type="submit" class="btn btn-primary">تحديث</button>
    </form>
    
    
@endsection