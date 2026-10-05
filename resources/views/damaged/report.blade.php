@extends('layouts.app')

@section('main')
    @php
        $money = fn ($value) => number_format((float) $value, 2);
        $reasons = \App\Models\DamagedItem::REASONS;
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0 d-flex align-items-center gap-2">
            <i class="fa-solid fa-chart-column" aria-hidden="true"></i>
            <span>تقرير الهالِك</span>
        </h3>
        <div class="d-flex gap-2 d-print-none">
            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                <i class="fa-solid fa-print" aria-hidden="true"></i> طباعة
            </button>
            <a href="{{ route('damaged.index') }}" class="btn btn-outline-secondary">رجوع</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="GET" action="{{ route('damaged.report') }}" class="card shadow-sm mb-3 d-print-none">
        <div class="card-body row g-2 align-items-end">
            <div class="col-md-4">
                <label for="from" class="form-label small mb-1">من تاريخ</label>
                <input type="date" id="from" name="from" class="form-control" value="{{ $from }}">
            </div>
            <div class="col-md-4">
                <label for="to" class="form-label small mb-1">إلى تاريخ</label>
                <input type="date" id="to" name="to" class="form-control" value="{{ $to }}">
            </div>
            <div class="col-md-4"><button class="btn btn-primary w-100">عرض التقرير</button></div>
        </div>
    </form>

    <p class="text-body-secondary">الفترة: من <strong>{{ $from }}</strong> إلى <strong>{{ $to }}</strong>. السجلات الملغية غير محسوبة.</p>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card shadow-sm h-100"><div class="card-body">
            <div class="text-body-secondary small">عدد السجلات</div><div class="fs-3 fw-bold">{{ $totals->records }}</div>
        </div></div></div>
        <div class="col-md-4"><div class="card shadow-sm h-100"><div class="card-body">
            <div class="text-body-secondary small">إجمالي القطع الهالكة</div><div class="fs-3 fw-bold">{{ $totals->units }}</div>
        </div></div></div>
        <div class="col-md-4"><div class="card shadow-sm h-100"><div class="card-body">
            <div class="text-body-secondary small">إجمالي الخسارة</div><div class="fs-3 fw-bold text-danger">{{ $money($totals->value) }} ج.م</div>
        </div></div></div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-semibold">حسب السبب</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>السبب</th><th>السجلات</th><th>القطع</th><th>القيمة</th></tr></thead>
                        <tbody>
                            @forelse ($byReason as $row)
                                <tr>
                                    <td>{{ $reasons[$row->reason] ?? $row->reason }}</td>
                                    <td>{{ $row->records }}</td><td>{{ $row->units }}</td>
                                    <td class="text-nowrap">{{ $money($row->value) }} ج.م</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-body-secondary py-3">لا توجد بيانات.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm h-100">
                <div class="card-header fw-semibold">أكثر المنتجات هلاكاً (حسب القيمة)</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>المنتج</th><th>القطع</th><th>القيمة</th></tr></thead>
                        <tbody>
                            @forelse ($topProducts as $row)
                                <tr>
                                    <td>{{ $row->product_name }}</td><td>{{ $row->units }}</td>
                                    <td class="text-nowrap">{{ $money($row->value) }} ج.م</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-body-secondary py-3">لا توجد بيانات.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header fw-semibold">التفصيل اليومي</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>التاريخ</th><th>السجلات</th><th>القطع</th><th>القيمة</th></tr></thead>
                        <tbody>
                            @forelse ($daily as $row)
                                <tr>
                                    <td>{{ \Illuminate\Support\Carbon::parse($row->damaged_on)->format('Y-m-d') }}</td>
                                    <td>{{ $row->records }}</td><td>{{ $row->units }}</td>
                                    <td class="text-nowrap">{{ $money($row->value) }} ج.م</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-body-secondary py-3">لا توجد بيانات في هذه الفترة.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
