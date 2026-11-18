@extends('layouts.app')

@section('content')
<div class="container-fluid py-3" dir="rtl">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div><h1 class="h3 mb-1">مخزون الفروع</h1><p class="text-body-secondary mb-0">الأرصدة المتاحة والحدود الدنيا لكل فرع</p></div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-primary" href="{{ route('branch_stock.transfers.index') }}">تحويلات المخزون</a>
            <a class="btn btn-outline-primary" href="{{ route('stock_movements.index') }}">حركات المخزون</a>
            <a class="btn btn-outline-primary" href="{{ route('stocktake.index') }}">جرد المخزون</a>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger" role="alert">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><strong>تعذر حفظ البيانات:</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    @can('page.branch_stock.manage')
    <details class="card mb-3">
        <summary class="card-header fw-semibold">إضافة فرع</summary>
        <form class="card-body row g-3" method="post" action="{{ route('branch_stock.branches.store') }}">
            @csrf
            <div class="col-md-4"><label class="form-label" for="branch-name">اسم الفرع</label><input id="branch-name" class="form-control" name="name" value="{{ old('name') }}" required maxlength="120"></div>
            <div class="col-md-3"><label class="form-label" for="branch-code">رمز الفرع</label><input id="branch-code" class="form-control" name="code" value="{{ old('code') }}" required maxlength="32" pattern="[A-Za-z0-9_-]+"></div>
            <div class="col-md-3"><label class="form-label" for="branch-phone">الهاتف</label><input id="branch-phone" class="form-control" name="phone" maxlength="32"></div>
            <div class="col-md-8"><label class="form-label" for="branch-address">العنوان</label><input id="branch-address" class="form-control" name="address" maxlength="255"></div>
            <div class="col-md-4 d-flex align-items-end"><button class="btn btn-primary w-100">حفظ الفرع</button></div>
        </form>
    </details>
    @endcan

    <details class="card mb-3">
        <summary class="card-header fw-semibold">إدارة الفروع ({{ $branches->count() }})</summary>
        <div class="table-responsive"><table class="table table-striped align-middle mb-0">
            <thead><tr><th>الفرع</th><th>الرمز</th><th>العنوان</th><th>الحالة</th><th>إجراءات</th></tr></thead>
            <tbody>
            @forelse($branches as $branch)
                <tr>
                    <td>{{ $branch->name }} @if($branch->is_main)<span class="badge text-bg-primary">رئيسي</span>@endif</td><td>{{ $branch->code }}</td><td>{{ $branch->address ?: '—' }}</td>
                    <td>{{ $branch->is_active ? 'نشط' : 'متوقف' }}</td>
                    <td>
                        @can('page.branch_stock.manage')
                        <details><summary class="btn btn-sm btn-outline-secondary">تعديل</summary>
                            <form class="d-flex flex-wrap gap-2 mt-2" method="post" action="{{ route('branch_stock.branches.update', $branch) }}">
                                @csrf @method('put')
                                <input class="form-control form-control-sm" name="name" value="{{ $branch->name }}" required aria-label="اسم الفرع">
                                <input class="form-control form-control-sm" name="code" value="{{ $branch->code }}" required aria-label="رمز الفرع">
                                <input class="form-control form-control-sm" name="address" value="{{ $branch->address }}" aria-label="العنوان">
                                <input class="form-control form-control-sm" name="phone" value="{{ $branch->phone }}" aria-label="الهاتف">
                                <select class="form-select form-select-sm" name="is_active" aria-label="الحالة"><option value="1" @selected($branch->is_active)>نشط</option><option value="0" @selected(!$branch->is_active)>متوقف</option></select>
                                <button class="btn btn-sm btn-primary">حفظ</button>
                            </form>
                        </details>
                        @if(!$branch->is_main)<form class="d-inline" method="post" action="{{ route('branch_stock.branches.destroy', $branch) }}" onsubmit="return confirm('حذف الفرع؟ لن يسمح النظام بحذف فرع له مخزون أو تحويلات.')">@csrf @method('delete')<button class="btn btn-sm btn-outline-danger">حذف</button></form>@endif
                        @endcan
                    </td>
                </tr>
            @empty<tr><td colspan="5" class="text-center text-body-secondary py-4">لا توجد فروع بعد.</td></tr>@endforelse
            </tbody>
        </table></div>
    </details>

    <form class="card card-body mb-3" method="get" action="{{ route('branch_stock.index') }}">
        <div class="row g-2 align-items-end">
            <div class="col-md-3"><label class="form-label" for="filter-branch">الفرع</label><select id="filter-branch" class="form-select" name="branch_id"><option value="">كل الفروع</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? '') == $branch->id)>{{ $branch->name }}</option>@endforeach</select></div>
            <div class="col-md-3"><label class="form-label" for="filter-category">التصنيف</label><select id="filter-category" class="form-select" name="category_id"><option value="">كل التصنيفات</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(($filters['category_id'] ?? '') == $category->id)>{{ $category->name }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="form-label" for="availability">حالة المخزون</label><select id="availability" class="form-select" name="availability"><option value="all">الكل</option><option value="low" @selected(($filters['availability'] ?? '') === 'low')>منخفض</option><option value="out" @selected(($filters['availability'] ?? '') === 'out')>نفد</option></select></div>
            <div class="col-md-3"><label class="form-label" for="stock-search">بحث بالاسم أو الباركود</label><input id="stock-search" class="form-control" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="100"></div>
            <div class="col-md-1"><button class="btn btn-primary w-100">بحث</button></div>
        </div>
    </form>

    <div class="table-responsive"><table class="table table-hover align-middle">
        <thead><tr><th>الفرع</th><th>المنتج</th><th>التصنيف</th><th>الباركود</th><th>الكمية</th><th>حد إعادة الطلب</th>@can('page.branch_stock.manage')<th>تسوية</th>@endcan</tr></thead>
        <tbody>
        @forelse($stocks as $row)
            <tr><td>{{ $row->branch->name }}</td><td>{{ $row->product->name }}</td><td>{{ $row->product->category?->name ?? '—' }}</td><td>{{ $row->product->barcode ?? '—' }}</td>
                <td><span class="badge {{ $row->quantity === 0 ? 'text-bg-danger' : ($row->reorder_level > 0 && $row->quantity <= $row->reorder_level ? 'text-bg-warning' : 'text-bg-success') }}">{{ $row->quantity }}</span></td>
                <td>
                    @can('page.branch_stock.manage')
                    <form class="d-flex gap-1" method="post" action="{{ route('branch_stock.reorder-level', $row) }}">@csrf @method('put')<input class="form-control form-control-sm" type="number" min="0" max="1000000" name="reorder_level" value="{{ $row->reorder_level }}" aria-label="حد إعادة الطلب"><button class="btn btn-sm btn-outline-secondary">تحديث</button></form>
                    @else {{ $row->reorder_level }} @endcan
                </td>
                @can('page.branch_stock.manage')<td><form class="d-flex flex-column flex-lg-row gap-1" method="post" action="{{ route('branch_stock.adjust') }}">@csrf<input type="hidden" name="idempotency_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}"><input type="hidden" name="branch_id" value="{{ $row->branch_id }}"><input type="hidden" name="product_id" value="{{ $row->product_id }}"><input class="form-control form-control-sm" type="number" name="quantity_delta" required min="-1000000" max="1000000" placeholder="فرق الكمية" aria-label="فرق الكمية"><input class="form-control form-control-sm" name="reason" required minlength="5" maxlength="500" placeholder="سبب التسوية" aria-label="سبب التسوية"><button class="btn btn-sm btn-outline-primary">تسجيل</button></form></td>@endcan
            </tr>
        @empty<tr><td colspan="7" class="text-center text-body-secondary py-5">لا توجد أرصدة تطابق البحث.</td></tr>@endforelse
        </tbody>
    </table></div>
    {{ $stocks->links() }}
</div>
@endsection
