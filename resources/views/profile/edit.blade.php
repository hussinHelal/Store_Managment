@extends('layouts.app')

@section('content')

    <span class="text-center border border-1 rounded text-bold">تحديث الملف الشخصي</span>
    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')


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

      <div class="mb-3">
        <label for="photo" class="form-label">الصورة الشخصية</label>
        <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
      </div>

      @if($user->photo)
      <div class="mb-3">
          <label class="form-label">الصورة الحالية</label>
          <div>
              <img src="{{ asset('uploads/users/' . $user->photo) }}" alt="User Photo" class="img-fluid rounded" style="max-width: 180px;">
          </div>
      </div>
      @endif

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
