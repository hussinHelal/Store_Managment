@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">إدارة الأدوار</h1>
            <p class="text-body-secondary mb-0">تحديد صلاحيات الصفحات وتعيين الأدوار للموظفين.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('roles.create') }}">
            <i class="fa-solid fa-plus me-1" aria-hidden="true"></i> إنشاء دور جديد
        </a>
    </div>

    <section class="mb-5" aria-labelledby="role-list-title">
        <h2 id="role-list-title" class="h5 mb-3">الأدوار</h2>
        @if($roles->isEmpty())
            <div class="border rounded p-4 text-center text-body-secondary">لا توجد أدوار مُعدة.</div>
        @else
            <div class="table-responsive border rounded">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th scope="col">الدور</th><th scope="col">الموظفون المرتبطون</th><th scope="col" class="text-end">الإجراءات</th></tr>
                    </thead>
                    <tbody>
                        @foreach($roles as $role)
                            <tr>
                                <td class="fw-semibold">{{ $role->name }}</td>
                                <td>{{ $role->users_count }}</td>
                                <td class="text-end text-nowrap">
                                    @if(strtolower($role->name) !== 'superadmin')
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('roles.edit', $role) }}" aria-label="تعديل {{ $role->name }}">
                                            <i class="fa-solid fa-pen" aria-hidden="true"></i>
                                        </a>
                                    @else
                                        <span class="text-body-secondary small">دور محمي</span>
                                    @endif
                                    @if(!in_array(strtolower($role->name), ['admin', 'superadmin'], true) && $role->users_count === 0)
                                        <button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#delete-role-{{ $role->id }}" aria-label="حذف {{ $role->name }}">
                                            <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                        </button>
                                        <div class="modal fade" id="delete-role-{{ $role->id }}" tabindex="-1" aria-labelledby="delete-role-title-{{ $role->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content text-start">
                                                    <div class="modal-header">
                                                        <h2 class="modal-title fs-5" id="delete-role-title-{{ $role->id }}">حذف الدور؟</h2>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                                                    </div>
                                                    <div class="modal-body">سيتم حذف دور {{ $role->name }} نهائياً.</div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                        <form method="POST" action="{{ route('roles.destroy', $role) }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button class="btn btn-danger" type="submit">حذف الدور</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @include('components.pagination', ['collection' => $roles, 'paginationLabel' => 'دور'])
        @endif
    </section>

    <section aria-labelledby="assignment-title">
        <h2 id="assignment-title" class="h5 mb-3">تعيين الأدوار للموظفين</h2>
        @if($users->isEmpty())
            <div class="border rounded p-4 text-center text-body-secondary">لا توجد حسابات موظفين.</div>
        @else
            <div class="table-responsive border rounded">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th scope="col">اسم المستخدم</th><th scope="col">الاسم</th><th scope="col">الدور الحالي</th><th scope="col">الحالة</th><th scope="col" class="text-end">تعيين الدور</th></tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td class="fw-semibold">{{ $user->username }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->roles->pluck('name')->join(', ') ?: 'بدون دور' }}</td>
                                <td><span class="badge {{ $user->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $user->is_active ? 'نشط' : 'غير نشط' }}</span></td>
                                <td class="text-end">
                                    @if(!$user->system_account && !$user->isSuperAdmin())
                                        <form method="POST" action="{{ route('roles.users.assign', $user) }}" class="d-flex justify-content-end gap-2">
                                            @csrf
                                            <select name="role_id" class="form-select form-select-sm" aria-label="Role for {{ $user->username }}" required>
                                                @foreach($assignableRoles as $role)
                                                    <option value="{{ $role->id }}" @selected($user->roles->contains('id', $role->id))>{{ $role->name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-outline-primary">حفظ</button>
                                        </form>
                                    @else
                                        <span class="text-body-secondary small">حساب محمي</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @include('components.pagination', ['collection' => $users, 'paginationLabel' => 'موظف'])
        @endif
    </section>
</div>
@endsection
