@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">الموظفون</h1>
            <p class="text-body-secondary mb-0">إدارة حسابات الموظفين ومراجعة الأدوار وحالة الوصول.</p>
        </div>
        @if(auth()->user()->isSuperAdmin() || auth()->user()->can('page.staff.manage'))
            <div class="d-flex flex-wrap gap-2">
                @if(auth()->user()->isSuperAdmin())
                <a class="btn btn-outline-primary" href="{{ route('roles.index') }}">
                    <i class="fa-solid fa-user-shield ms-1" aria-hidden="true"></i> إدارة الأدوار
                </a>
                @endif
                @if(auth()->user()->can('page.staff.manage'))
                <a class="btn btn-primary" href="{{ route('users.create') }}">
                    <i class="fa-solid fa-user-plus ms-1" aria-hidden="true"></i> إضافة موظف جديد
                </a>
                @endif
            </div>
        @endif
    </div>

    <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-end mb-3">
        <div class="col-sm-8 col-lg-5">
            <label for="staff-search" class="form-label">بحث باسم المستخدم أو الاسم أو البريد الإلكتروني</label>
            <input id="staff-search" class="form-control" type="search" name="search" value="{{ $search }}" maxlength="80">
        </div>
        <div class="col-auto">
            <button class="btn btn-outline-secondary" type="submit"><i class="fa-solid fa-magnifying-glass ms-1" aria-hidden="true"></i> بحث</button>
            @if($search !== '')
                <a class="btn btn-link" href="{{ route('users.index') }}">مسح البحث</a>
            @endif
        </div>
    </form>

    @if($users->isEmpty())
        <div class="border rounded p-4 text-center text-body-secondary">
            <i class="fa-solid fa-users fs-3 mb-2" aria-hidden="true"></i>
            <p class="mb-0">لا توجد حسابات موظفين تطابق البحث.</p>
        </div>
    @else
        <div class="table-responsive border rounded">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">اسم المستخدم</th>
                        <th scope="col">الاسم</th>
                        <th scope="col">البريد الإلكتروني</th>
                        <th scope="col">الدور</th>
                        <th scope="col">الحالة</th>
                        @if(auth()->user()->can('page.staff.manage'))
                            <th scope="col" class="text-end">الإجراءات</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td class="fw-semibold">{{ $user->username }}</td>
                            <td>{{ $user->name }}</td>
                            <td dir="ltr" class="text-end">{{ $user->email }}</td>
                            <td>{{ $user->roles->pluck('name')->map(fn ($role) => ['Admin' => 'مدير النظام', 'Cashier' => 'كاشير', 'Inventory Manager' => 'مدير المخزون', 'Sales Manager' => 'مدير المبيعات'][$role] ?? $role)->join('، ') ?: 'غير محدد' }}</td>
                            <td>
                                <span class="badge {{ $user->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $user->is_active ? 'نشط' : 'غير نشط' }}
                                </span>
                            </td>
                            @if(auth()->user()->can('page.staff.manage'))
                                <td class="text-end text-nowrap">
                                    @if(!$user->system_account && !$user->isSuperAdmin())
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('users.edit', $user) }}" aria-label="تعديل {{ $user->username }}">
                                            <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#delete-user-{{ $user->id }}" aria-label="حذف {{ $user->username }}">
                                            <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                        </button>
                                        <div class="modal fade" id="delete-user-{{ $user->id }}" tabindex="-1" aria-labelledby="delete-user-title-{{ $user->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content text-start">
                                                    <div class="modal-header">
                                                        <h2 class="modal-title fs-5" id="delete-user-title-{{ $user->id }}">حذف حساب الموظف؟</h2>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                                                    </div>
                                                    <div class="modal-body">سيؤدي هذا إلى حذف الحساب {{ $user->username }} وإلغاء صلاحية الوصول نهائيًا.</div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                        <form method="POST" action="{{ route('users.destroy', $user) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-danger" type="submit">حذف الحساب</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-body-secondary small">حساب محمي</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @include('components.pagination', ['collection' => $users, 'paginationLabel' => 'موظف'])
    @endif
</div>
@endsection