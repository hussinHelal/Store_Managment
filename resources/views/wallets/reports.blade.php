@extends('layouts.app')

@section('main')
    @include('wallets._nav')
    @php $m = fn (int $cents) => \App\Support\Money::format($cents); @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0">{{ $mode === 'daily' ? 'التقرير اليومي' : 'التقرير الشهري' }}
            <small class="text-body-secondary fs-6">{{ $mode === 'daily' ? $date : $month }}</small>
        </h3>
        <button type="button" class="btn btn-outline-secondary d-print-none" onclick="window.print()">
            <i class="fa-solid fa-print" aria-hidden="true"></i> طباعة
        </button>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form method="GET" action="{{ route('wallets.reports.index') }}" class="card shadow-sm mb-3 d-print-none">
        <div class="card-body row g-2 align-items-end">
            <div class="col-md-3">
                <label for="mode" class="form-label small mb-1">نوع التقرير</label>
                <select id="mode" name="mode" class="form-select" onchange="document.getElementById('dateBox').classList.toggle('d-none', this.value!=='daily');document.getElementById('monthBox').classList.toggle('d-none', this.value!=='monthly')">
                    <option value="daily" @selected($mode === 'daily')>يومي</option>
                    <option value="monthly" @selected($mode === 'monthly')>شهري</option>
                </select>
            </div>
            <div class="col-md-4 {{ $mode === 'daily' ? '' : 'd-none' }}" id="dateBox">
                <label for="date" class="form-label small mb-1">اليوم</label>
                <input type="date" id="date" name="date" class="form-control" value="{{ $date }}">
            </div>
            <div class="col-md-4 {{ $mode === 'monthly' ? '' : 'd-none' }}" id="monthBox">
                <label for="month" class="form-label small mb-1">الشهر</label>
                <input type="month" id="month" name="month" class="form-control" value="{{ $month }}">
            </div>
            <div class="col-md-3"><button class="btn btn-primary w-100">عرض التقرير</button></div>
        </div>
    </form>

    <div class="row g-3 mb-4">
        @foreach ([
            ['عدد العمليات', (string) $totals['txns'], ''],
            ['إجمالي المرسل', $m($totals['sent']), ''],
            ['إجمالي المستلم', $m($totals['received']), ''],
            ['العمولات', $m($totals['commission']), 'text-success'],
            ['رسوم المزود', $m($totals['fee']), 'text-danger'],
            ['المصروفات', $m($expensesTotal), 'text-danger'],
            ['صافي الربح', $m($net), $net < 0 ? 'text-danger' : 'text-success'],
            ['حركة النقدية في الدرج', $m($cash['movement']), $cash['movement'] < 0 ? 'text-danger' : ''],
        ] as [$label, $value, $class])
            <div class="col-6 col-lg-3">
                <div class="card shadow-sm h-100"><div class="card-body">
                    <div class="text-body-secondary small">{{ $label }}</div>
                    <div class="fs-5 fw-bold {{ $class }}">{{ $value }}</div>
                </div></div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6"><div class="card shadow-sm h-100"><div class="card-body d-flex justify-content-between">
            <span>آجل جديد في الفترة (مستحق على عملاء)</span><strong>{{ $m($cash['new_deferred']) }}</strong>
        </div></div></div>
        <div class="col-md-6"><div class="card shadow-sm h-100"><div class="card-body d-flex justify-content-between">
            <span>مبالغ محصَّلة من الآجل</span><strong>{{ $m($cash['collections']) }}</strong>
        </div></div></div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold">حسب المحفظة</div>
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>المحفظة</th><th>العمليات</th><th>المرسل</th><th>المستلم</th><th>العمولة</th><th>الرسوم</th><th>الربح</th></tr></thead>
                <tbody>
                    @forelse ($perWallet as $row)
                        <tr>
                            <td>{{ $row['name'] }} <small class="text-body-secondary">{{ $row['provider'] }}</small></td>
                            <td>{{ $row['txns'] }}</td><td>{{ $m($row['sent']) }}</td><td>{{ $m($row['received']) }}</td>
                            <td>{{ $m($row['commission']) }}</td><td>{{ $m($row['fee']) }}</td><td class="fw-semibold">{{ $m($row['profit']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-body-secondary py-4">لا توجد عمليات في هذه الفترة.</td></tr>
                    @endforelse
                </tbody>
                @if (count($perWallet) > 1)
                    <tfoot class="table-light fw-bold">
                        <tr><td>الإجمالي</td><td>{{ $totals['txns'] }}</td><td>{{ $m($totals['sent']) }}</td><td>{{ $m($totals['received']) }}</td>
                            <td>{{ $m($totals['commission']) }}</td><td>{{ $m($totals['fee']) }}</td><td>{{ $m($totals['profit']) }}</td></tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    @if ($mode === 'monthly')
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">التفصيل اليومي</div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>اليوم</th><th>العمليات</th><th>المرسل</th><th>المستلم</th><th>العمولة</th><th>الرسوم</th><th>المصروفات</th><th>الصافي</th></tr></thead>
                    <tbody>
                        @forelse ($days as $day)
                            <tr>
                                <td class="text-nowrap">
                                    <a href="{{ route('wallets.reports.index', ['mode' => 'daily', 'date' => $day['day']]) }}" class="d-print-none">{{ $day['day'] }}</a>
                                    <span class="d-none d-print-inline">{{ $day['day'] }}</span>
                                </td>
                                <td>{{ $day['txns'] }}</td><td>{{ $m($day['sent']) }}</td><td>{{ $m($day['received']) }}</td>
                                <td>{{ $m($day['commission']) }}</td><td>{{ $m($day['fee']) }}</td><td>{{ $m($day['expenses']) }}</td>
                                <td class="fw-semibold {{ $day['net'] < 0 ? 'text-danger' : 'text-success' }}">{{ $m($day['net']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-body-secondary py-4">لا توجد بيانات في هذا الشهر.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="card shadow-sm">
            <div class="card-header fw-semibold">مصروفات اليوم</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>البند</th><th>المبلغ</th><th>ملاحظة</th></tr></thead>
                    <tbody>
                        @forelse ($expenseList as $expense)
                            <tr><td>{{ $expense->category }}</td><td>{{ $m(\App\Support\Money::cents($expense->amount)) }}</td><td>{{ $expense->note }}</td></tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-body-secondary py-3">لا توجد مصروفات.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
