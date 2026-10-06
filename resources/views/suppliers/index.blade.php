@extends('layouts.app')
@section('title', ' - الموردون')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">الموردون والحسابات الدائنة</h1>
            <p class="text-body-secondary mb-0">إدارة الموردين وفواتير الشراء والسداد.</p>
        </div>
        @if(auth()->user()->can('page.suppliers.manage'))
            <a href="{{ route('suppliers.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus ms-1" aria-hidden="true"></i> إضافة مورد</a>
        @endif
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-body-secondary small mb-2">إجمالي المستحقات للموردين</div>
                <div class="h3 fw-bold mb-0 text-danger">{{ number_format($totalDue, 2) }} ج.م</div>
            </div></div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100"><div class="card-body">
                <div class="text-body-secondary small mb-2">عدد الموردين</div>
                <div class="h3 fw-bold mb-0">{{ $suppliers->total() }}</div>
            </div></div>
        </div>
    </div>

    <form method="GET" action="{{ route('suppliers.index') }}" class="row g-2 align-items-end mb-3">
        <div class="col-sm-8 col-lg-5">
            <label for="supplier-search" class="form-label">بحث بالاسم أو الهاتف أو البريد</label>
            <input id="supplier-search" class="form-control" type="search" name="search" value="{{ $search }}" maxlength="100">
        </div>
        <div class="col-auto"><button class="btn btn-outline-secondary" type="submit"><i class="fa-solid fa-magnifying-glass ms-1" aria-hidden="true"></i> بحث</button></div>
        @if($search !== '')<div class="col-auto"><a class="btn btn-link" href="{{ route('suppliers.index') }}">مسح البحث</a></div>@endif
    </form>

    <div class="table-responsive border rounded">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr>
                <th scope="col">المورد</th><th scope="col">الهاتف</th><th scope="col">ملاحظات</th><th scope="col">الفواتير</th>
                <th scope="col">الرصيد الافتتاحي</th><th scope="col">المستحق</th><th scope="col">الإجراءات</th>
            </tr></thead>
            <tbody>
            @forelse($suppliers as $supplier)
                <tr>
                    <td class="fw-semibold">{{ $supplier->name }}</td>
                    <td>{{ $supplier->phone ?: '—' }}</td>
                    <td>{{ $supplier->notes ?: '—' }}</td>
                    <td>{{ $supplier->purchases_count }}</td>
                    <td>{{ number_format((float) $supplier->opening_balance, 2) }} ج.م</td>
                    <td class="fw-semibold {{ (float) $supplier->balance > 0 ? 'text-danger' : 'text-success' }}">{{ number_format((float) $supplier->balance, 2) }} ج.م</td>
                    <td><a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-sm btn-outline-primary">التفاصيل</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-body-secondary py-4">لا يوجد موردون مطابقون للبحث.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @include('components.pagination', ['collection' => $suppliers, 'paginationLabel' => 'مورد'])
</div>
@endsection
