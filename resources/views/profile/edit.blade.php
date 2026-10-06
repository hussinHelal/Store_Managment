      @extends('layouts.app')

      @section('content')
      <div class="container py-4" style="max-width: 48rem">
        <div class="mb-4">
          <a href="{{ route('profile.index') }}" class="link-secondary text-decoration-none"><i class="fa-solid fa-arrow-right me-1" aria-hidden="true"></i> الملف الشخصي</a>
          <h1 class="h3 mt-2">تحديث الملف الشخصي</h1>
        </div>

        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" data-disable-submit>
          @csrf
          @method('PUT')
          <div class="mb-3">
            <label for="username" class="form-label">اسم المستخدم</label>
            <input id="username" value="{{ $user->username }}" class="form-control" readonly>
          </div>
          <div class="mb-3">
            <label for="name" class="form-label">الاسم</label>
            <input id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required maxlength="255">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">البريد الإلكتروني</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" autocomplete="email" required maxlength="255">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
          </div>

          <fieldset class="border rounded p-3 mb-3">
            <legend class="float-none w-auto px-2 fs-6">تغيير كلمة المرور</legend>
            <div class="mb-3">
              <label for="current_password" class="form-label">كلمة المرور الحالية</label>
              <input id="current_password" name="current_password" type="password" class="form-control @error('current_password') is-invalid @enderror" autocomplete="current-password">
              @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">كلمة المرور الجديدة</label>
              <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
              @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-0">
              <label for="password_confirmation" class="form-label">تأكيد كلمة المرور الجديدة</label>
              <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password">
            </div>
          </fieldset>

          <div class="mb-4">
            <label for="photo" class="form-label">الصورة الشخصية</label>
            <input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp" class="form-control @error('photo') is-invalid @enderror">
            @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  <div class="form-check mt-2">
                    <input id="remove_photo" name="remove_photo" value="1" type="checkbox" class="form-check-input" @checked(old('remove_photo'))>
                    <label for="remove_photo" class="form-check-label">إزالة الصورة الشخصية</label>
                  </div>
            @if($user->photo)
              <img src="{{ asset('uploads/users/' . basename($user->photo)) }}" alt="الصورة الحالية" class="rounded mt-3" style="max-width: 180px; max-height: 180px; object-fit: cover">
            @endif
          </div>

          <div class="form-actions d-flex flex-wrap align-items-center gap-2 mt-3">
            <button type="submit" class="btn btn-primary">حفظ التغييرات</button>
            <a href="{{ route('profile.index') }}" class="btn btn-outline-secondary">إلغاء</a>
          </div>
        </form>
      </div>
      @endsection

      @push('scripts')
      <script>
        document.querySelectorAll('form[data-disable-submit]').forEach((form) => {
          form.addEventListener('submit', () => form.querySelectorAll('button[type="submit"]').forEach((button) => button.disabled = true));
        });
      </script>
      @endpush
