@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 48rem">
    <div class="mb-4">
        <a href="{{ route('users.index') }}" class="link-secondary text-decoration-none"><i class="fa-solid fa-arrow-right ms-1" aria-hidden="true"></i> الموظفون</a>
        <h1 class="h3 mt-2">{{ $user->name }}</h1>
    </div>
    <dl class="row border-top pt-3">
        <dt class="col-sm-3">اسم المستخدم</dt><dd class="col-sm-9">{{ $user->username }}</dd>
        <dt class="col-sm-3">البريد الإلكتروني</dt><dd class="col-sm-9" dir="ltr">{{ $user->email }}</dd>
        <dt class="col-sm-3">الدور</dt><dd class="col-sm-9">{{ $user->roles->pluck('name')->map(fn ($role) => ['Admin' => 'مدير النظام', 'Cashier' => 'كاشير', 'Inventory Manager' => 'مدير المخزون', 'Sales Manager' => 'مدير المبيعات'][$role] ?? $role)->join('، ') ?: 'غير محدد' }}</dd>
        <dt class="col-sm-3">الحالة</dt><dd class="col-sm-9">{{ $user->is_active ? 'نشط' : 'غير نشط' }}</dd>
    </dl>
</div>
@endsection