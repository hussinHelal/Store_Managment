@extends('layouts.app')
@section('title', ' - كشف حساب المورد')

@section('content')
<div class="container-fluid py-3" dir="rtl">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <a href="{{ route('suppliers.show', $supplier) }}" class="link-secondary text-decoration-none">{{ $supplier->name }}</a>
            <h1 class="h3 mt-2 mb-1">كشف حساب المورد</h1>
            <p class="text-body-secondary mb-0">الرصيد الافتتاحي والفواتير والسداد وأعمار المديونية</p>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('suppliers.show', $supplier) }}">العودة إلى المورد</a>
    </div>

    <form method="GET" class="card card-body mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-sm-6 col-lg-3"><label class="form-label" for="from">من تاريخ</label><input class="form-control" type="date" id="from" name="from" value="{{ $filters['from'] ?? '' }}"></div>
            <div class="col-sm-6 col-lg-3"><label class="form-label" for="to">إلى تاريخ</label><input class="form-control" type="date" id="to" name="to" value="{{ $filters['to'] ?? '' }}"></div>
            <div class="col-sm-6 col-lg-3"><label class="form-label" for="status">حالة الفواتير</label><select class="form-select" id="status" name="status"><option value="all">الكل</option><option value="open" @selected(($filters['status'] ?? 'all') === 'open')>المتبقي فقط</option><option value="paid" @selected(($filters['status'] ?? '') === 'paid')>المسددة</option></select></div>
            <div class="col-sm-6 col-lg-3 d-flex gap-2"><button class="btn btn-primary flex-grow-1">تطبيق</button><a class="btn btn-outline-secondary" href="{{ route('suppliers.statement', $supplier) }}">مسح</a></div>
        </div>
    </form>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="text-body-secondary small">الرصيد الافتتاحي</div><div class="h4 mt-2 mb-0">{{ \App\Support\Money::decimal($statement['opening_balance']) }} ج.م</div></div></div></div>
        <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="text-body-secondary small">الرصيد المستحق المسجل</div><div class="h4 mt-2 mb-0 text-danger">{{ \App\Support\Money::decimal($statement['recorded_balance']) }} ج.م</div></div></div></div>
        @foreach(['current' => 'غير مستحق بعد', 'days_1_30' => 'من يوم إلى 30 يوماً', 'days_31_60' => 'من 31 إلى 60 يوماً', 'days_61_90' => 'من 61 إلى 90 يوماً', 'over_90_days' => 'أكثر من 90 يوماً'] as $key => $label)
            <div class="col-sm-6 col-xl-3"><div class="card h-100"><div class="card-body"><div class="text-body-secondary small">{{ $label }}</div><div class="h4 mt-2 mb-0">{{ \App\Support\Money::decimal($statement['aging'][$key]) }} ج.م</div></div></div></div>
        @endforeach
        @if($statement['unallocated_difference'] !== 0)
            <div class="col-12"><div class="alert alert-warning mb-0">يوجد فرق غير موزع بين الرصيد المسجل والفواتير والرصيد الافتتاحي: {{ \App\Support\Money::decimal($statement['unallocated_difference']) }} ج.م. راجع الرصيد الافتتاحي أو السجلات القديمة قبل إجراء تسوية.</div></div>
        @endif
    </div>

    <section class="mb-4" aria-labelledby="statement-purchases">
        <h2 id="statement-purchases" class="h5 mb-2">فواتير الشراء</h2>
        <div class="table-responsive border rounded"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>رقم الفاتورة</th><th>التاريخ</th><th>الإجمالي</th><th>المدفوع</th><th>المتبقي</th><th>الحالة</th></tr></thead>
            <tbody>@forelse($statement['purchases'] as $purchase)
                <tr><td>{{ $purchase->invoice_number }}</td><td>{{ $purchase->purchase_date?->format('Y-m-d') }}</td><td>{{ number_format((float) $purchase->total_amount, 2) }} ج.م</td><td>{{ number_format((float) $purchase->paid_amount, 2) }} ج.م</td><td>{{ number_format((float) $purchase->remaining, 2) }} ج.م</td><td><span class="badge {{ $purchase->statusBadgeClass() }}">{{ $purchase->statusLabel() }}</span></td></tr>
            @empty<tr><td colspan="6" class="text-center text-body-secondary py-4">لا توجد فواتير ضمن الفترة المحددة.</td></tr>@endforelse</tbody>
        </table></div>
        @include('components.pagination', ['collection' => $statement['purchases']])
    </section>

    <section aria-labelledby="statement-payments">
        <h2 id="statement-payments" class="h5 mb-2">سندات السداد</h2>
        <div class="table-responsive border rounded"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>رقم السند</th><th>التاريخ</th><th>الفاتورة</th><th>المبلغ</th><th>ملاحظات</th></tr></thead>
            <tbody>@forelse($statement['payments'] as $payment)
                <tr><td>{{ $payment->receipt_number }}</td><td>{{ $payment->payment_date?->format('Y-m-d') }}</td><td>{{ $payment->purchase?->invoice_number ?: '—' }}</td><td>{{ number_format((float) $payment->amount, 2) }} ج.م</td><td>{{ $payment->notes ?: '—' }}</td></tr>
            @empty<tr><td colspan="5" class="text-center text-body-secondary py-4">لا توجد سندات ضمن الفترة المحددة.</td></tr>@endforelse</tbody>
        </table></div>
        @include('components.pagination', ['collection' => $statement['payments']])
    </section>
</div>
@endsection
