@extends('layouts.app')

@section('content')
<div class="container py-3" style="max-width: 58rem">
    <div class="mb-4">
        <a href="{{ route('roles.index') }}" class="link-secondary text-decoration-none"><i class="fa-solid fa-arrow-right me-1" aria-hidden="true"></i> إدارة الأدوار</a>
        <h1 class="h3 mt-2">{{ $role ? 'تعديل دور' : 'إنشاء دور جديد' }}</h1>
    </div>

    <form method="POST" action="{{ $role ? route('roles.update', $role) : route('roles.store') }}" data-role-form>
        @csrf
        @if($role)
            @method('PUT')
        @endif
        <div class="mb-4">
            <label for="name" class="form-label">اسم الدور</label>
            <input id="name" name="name" value="{{ old('name', $role?->name) }}" class="form-control @error('name') is-invalid @enderror" required maxlength="100" autocomplete="off">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <fieldset class="mb-4">
            <legend class="h5">صلاحيات الصفحات</legend>
            <p class="text-body-secondary small">صلاحية الإدارة تشمل العرض. إدارة الأدوار متاحة للمسؤول الأعلى فقط.</p>
            <div class="table-responsive border rounded">
                <table class="table align-middle mb-0">
                    <thead class="table-light"><tr><th scope="col">الصفحة</th><th scope="col" class="text-center">عرض</th><th scope="col" class="text-center">إدارة</th></tr></thead>
                    <tbody>
                        @foreach($pages as $page => $label)
                            @php
                                $viewName = "page.{$page}.view";
                                $manageName = "page.{$page}.manage";
                                $viewChecked = old("permissions.{$page}.view", $permissions->has($viewName));
                                $manageChecked = old("permissions.{$page}.manage", $permissions->has($manageName));
                            @endphp
                            <tr>
                                <th scope="row">{{ $label }}@if(isset(config('access.descriptions')[$page]))<div class="small fw-normal text-body-secondary mt-1">{{ config("access.descriptions.{$page}.view") }}</div>@endif</th>
                                <td class="text-center"><input class="form-check-input" type="checkbox" name="permissions[{{ $page }}][view]" value="1" @checked($viewChecked) aria-label="عرض {{ $label }}"></td>
                                <td class="text-center"><input class="form-check-input" type="checkbox" name="permissions[{{ $page }}][manage]" value="1" @checked($manageChecked) aria-label="إدارة {{ $label }}">@if(isset(config('access.descriptions')[$page]))<div class="small text-body-secondary mt-1">{{ config("access.descriptions.{$page}.manage") }}</div>@endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @error('permissions')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
        </fieldset>

        <div class="form-actions d-flex flex-wrap align-items-center gap-2 mt-3">
            <button type="submit" class="btn btn-primary">{{ $role ? 'حفظ التغييرات' : 'إنشاء الدور' }}</button>
            <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">إلغاء</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('[data-role-form]').forEach((form) => {
        form.querySelectorAll('input[name$="[manage]"]').forEach((manage) => {
            manage.addEventListener('change', () => {
                if (manage.checked) {
                    const view = form.querySelector(`input[name="${manage.name.replace('[manage]', '[view]')}"]`);
                    if (view) view.checked = true;
                }
            });
        });
        form.addEventListener('submit', () => form.querySelectorAll('button[type="submit"]').forEach((button) => button.disabled = true));
    });
</script>
@endpush
