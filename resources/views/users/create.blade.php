@extends('layouts.app')

@section('content')
<div class="container py-3" style="max-width: 48rem">
    <div class="mb-4">
        <a href="{{ route('users.index') }}" class="link-secondary text-decoration-none"><i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i> الموظفون</a>
        <h1 class="h3 mt-2">إضافة موظف جديد</h1>
    </div>

    <form method="POST" action="{{ route('users.store') }}" data-disable-submit>
        @csrf
        <div class="mb-3">
            <label for="username" class="form-label">اسم المستخدم</label>
            <input id="username" name="username" value="{{ old('username') }}" class="form-control @error('username') is-invalid @enderror" autocomplete="username" required maxlength="80">
            @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">الاسم</label>
            <input id="name" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" required maxlength="255">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">البريد الإلكتروني</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" autocomplete="email" required maxlength="255" dir="ltr">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="role_id" class="form-label">الدور الوظيفي</label>
            <select id="role_id" name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                <option value="">اختر الدور</option>
                @foreach($roles as $role)
                    <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>{{ ['Admin' => 'مدير النظام', 'Cashier' => 'كاشير', 'Inventory Manager' => 'مدير المخزون', 'Sales Manager' => 'مدير المبيعات'][$role->name] ?? $role->name }}</option>
                @endforeach
            </select>
            @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="row g-3">
            <div class="col-md-6 mb-3">
                <label for="password" class="form-label">كلمة المرور</label>
                <input id="password" name="password" type="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password" required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6 mb-3">
                <label for="password_confirmation" class="form-label">تأكيد كلمة المرور</label>
                <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" autocomplete="new-password" required>
            </div>
        </div>
        <div class="form-check mb-4">
            <input type="hidden" name="is_active" value="0">
            <input id="is_active" name="is_active" type="checkbox" value="1" class="form-check-input" @checked(old('is_active', '1') == '1')>
            <label for="is_active" class="form-check-label">الحساب نشط</label>
        </div>
        <div class="form-actions d-flex flex-wrap align-items-center gap-2 mt-3">
            <button type="submit" class="btn btn-primary">إنشاء الحساب</button>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">إلغاء</a>
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