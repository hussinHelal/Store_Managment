@extends('layouts.app')
@section('title', '- الملف الشخصي')
@section('content')

<div class="container py-5">
    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card rounded-4 shadow-sm border-0 overflow-hidden">
                <div class="card-body p-0">
                    <div class="p-4 text-white bg-gradient">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div>
                                <small class="text-uppercase opacity-75">الملف الشخصي</small>
                                <h5 class="mb-0">{{ $profile->name ?? 'لا يوجد اسم' }}</h5>
                            </div>
                            @if($profile->photo)
                                <img src="{{ asset('uploads/users/' . $profile->photo) }}" alt="Profile Photo" class="rounded-circle" style="width: 70px; height: 70px; object-fit: cover;">
                            @else
                                <div class="avatar-circle bg-white text-dark">
                                    {{ strtoupper(substr($profile->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <p class="mb-1 opacity-85">{{ $profile->email ?? 'لا يوجد بريد' }}</p>
                        <span class="badge bg-light text-dark fw-semibold">{{ ucfirst($profile->role) }}</span>
                    </div>
                    <div class="p-4 bg-white">
                        <div class="d-grid gap-2">
                            <a href="{{ route('profile.edit', $profile->id) }}" class="btn btn-primary btn-lg rounded-4">
                                <i class="fas fa-pencil-alt me-2"></i> تعديل الملف
                            </a>
                            <form action="{{ route('profile.destroy', $profile->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-lg rounded-4" onclick="return confirm('هل أنت متأكد من الحذف؟')">
                                    <i class="fas fa-trash me-2"></i> حذف الحساب
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card rounded-4 shadow-sm border-0">
                <div class="card-header bg-white border-0 py-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-1">لوحة تحكم المستخدمين</h5>
                            <p class="text-muted mb-0">يمكن للمشرف الأعلى تغيير دور المستخدمين من هنا.</p>
                        </div>
                        <span class="badge rounded-pill text-white" style="background: linear-gradient(135deg, #0ea5e9 0%, #7c3aed 100%);">
                            {{ $profile->isSuperAdmin() ? 'سوبر أدمن' : 'عرض فقط' }}
                        </span>
                    </div>
                </div>
                <div class="card-body bg-light p-4">
                    @if($profile->isSuperAdmin())
                        @if($users && $users->count())
                            <div class="table-responsive">
                                <table class="table table-borderless align-middle mb-0">
                                    <thead>
                                        <tr class="text-muted small text-uppercase">
                                            <th>المستخدم</th>
                                            <th>الدور الحالي</th>
                                            <th>تعيين دور</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $roleClasses = [
                                                'superadmin' => 'bg-danger',
                                                'admin' => 'bg-primary',
                                                'cashier' => 'bg-success',
                                            ];
                                        @endphp
                                        @foreach($users as $user)
                                            <tr class="bg-white rounded-4 mb-2 shadow-sm">
                                                <td>
                                                    <div class="fw-semibold">{{ $user->name }}</div>
                                                    <div class="text-muted small">{{ $user->email }}</div>
                                                </td>
                                                <td>
                                                    <span class="badge {{ $roleClasses[$user->role] ?? 'bg-secondary' }} text-white">
                                                        {{ $roles[$user->role] ?? ucfirst($user->role) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <form action="{{ route('profile.users.role.update', $user->id) }}" method="POST" class="d-flex gap-2 align-items-center flex-wrap">
                                                        @csrf
                                                        @method('PUT')
                                                        <select name="role" class="form-select form-select-sm w-auto" aria-label="Role">
                                                            @foreach($roles as $key => $label)
                                                                <option value="{{ $key }}" @selected($user->role === $key)>{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" class="btn btn-sm btn-success rounded-3">حفظ</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @include('components.pagination', ['collection' => $users])
                        @else
                            <div class="alert alert-info mb-0 rounded-4">
                                لا يوجد مستخدمين آخرين لإدارة أدوارهم.
                            </div>
                        @endif
                    @else
                        <div class="alert alert-secondary mb-0 rounded-4">
                            فقط المشرف الأعلى يمكنه إدارة أدوار المستخدمين.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-circle {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        font-weight: 700;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.12);
    }
    .bg-gradient {
        background-image: linear-gradient(135deg, #6366f1 0%, #22d3ee 100%) !important;
    }
</style>

@endsection
