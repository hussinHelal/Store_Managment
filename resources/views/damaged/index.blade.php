@extends('layouts.app')

@section('main')
    @php
        $money = fn ($value) => number_format((float) $value, 2);
        $pic = fn (?string $path) => app(\App\Services\PictureStorageService::class)->url($path, 'damaged', true);
        $reasonBadge = [
            'broken' => 'text-bg-danger', 'water' => 'text-bg-info', 'defective' => 'text-bg-warning',
            'not_working' => 'text-bg-warning', 'lost' => 'text-bg-dark', 'expired' => 'text-bg-secondary',
            'returned' => 'text-bg-primary', 'other' => 'text-bg-light border text-dark',
        ];
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="mb-0 d-flex align-items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i>
            <span>هالِك</span>
        </h3>
        <div class="d-flex gap-2">
            <a href="{{ route('damaged.report') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-chart-column" aria-hidden="true"></i> التقرير
            </a>
            @can('page.damaged.manage')
                <a href="{{ route('damaged.create') }}" class="btn btn-danger">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i> تسجيل هالِك
                </a>
            @endcan
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card shadow-sm h-100"><div class="card-body">
                <div class="text-body-secondary small">هالِك اليوم</div>
                <div class="fs-4 fw-bold">{{ $summary['today']->units }} <small class="fs-6">قطعة</small></div>
                <div class="text-danger">{{ $money($summary['today']->value) }} ج.م</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100"><div class="card-body">
                <div class="text-body-secondary small">هالِك هذا الشهر</div>
                <div class="fs-4 fw-bold">{{ $summary['month']->units }} <small class="fs-6">قطعة</small></div>
                <div class="text-danger">{{ $money($summary['month']->value) }} ج.م</div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100"><div class="card-body">
                <div class="text-body-secondary small">إجمالي نتيجة البحث الحالي</div>
                <div class="fs-4 fw-bold">{{ $summary['filtered']->units }} <small class="fs-6">قطعة في {{ $summary['filtered']->records }} سجل</small></div>
                <div class="text-danger">{{ $money($summary['filtered']->value) }} ج.م</div>
            </div></div>
        </div>
    </div>

    <form method="GET" action="{{ route('damaged.index') }}" class="card shadow-sm mb-3">
        <div class="card-body row g-2 align-items-end">
            <div class="col-md-3">
                <label for="q" class="form-label small mb-1">اسم المنتج</label>
                <input type="search" id="q" name="q" class="form-control" maxlength="80" value="{{ $filters['q'] ?? '' }}" placeholder="ابحث بالاسم">
            </div>
            <div class="col-md-2">
                <label for="reason" class="form-label small mb-1">السبب</label>
                <select id="reason" name="reason" class="form-select">
                    <option value="">الكل</option>
                    @foreach ($reasons as $key => $label)
                        <option value="{{ $key }}" @selected(($filters['reason'] ?? '') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label small mb-1">الحالة</label>
                <select id="status" name="status" class="form-select">
                    <option value="">الكل</option>
                    <option value="recorded" @selected(($filters['status'] ?? '') === 'recorded')>مسجَّل</option>
                    <option value="voided" @selected(($filters['status'] ?? '') === 'voided')>ملغي</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="from" class="form-label small mb-1">من تاريخ</label>
                <input type="date" id="from" name="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label for="to" class="form-label small mb-1">إلى تاريخ</label>
                <input type="date" id="to" name="to" class="form-control" value="{{ $filters['to'] ?? '' }}">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button class="btn btn-primary flex-grow-1" aria-label="بحث"><i class="fa-solid fa-magnifying-glass"></i></button>
            </div>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>التاريخ</th><th>المنتج</th><th>الكمية</th><th>السبب</th>
                        <th>القيمة</th><th>الحالة</th><th>سجّله</th><th><span class="visually-hidden">تفاصيل</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        @php $thumb = $pic($item->image_path); @endphp
                        <tr class="{{ $item->isVoided() ? 'text-body-secondary' : '' }}">
                            <td class="text-nowrap">{{ $item->damaged_on->format('Y-m-d') }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if ($thumb)
                                        <img src="{{ $thumb }}" alt="" width="40" height="40" loading="lazy"
                                             class="rounded object-fit-cover flex-shrink-0" onerror="this.remove()">
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ $item->product_name }}</div>
                                        @if ($item->product_barcode)<small class="text-body-secondary" dir="ltr">{{ $item->product_barcode }}</small>@endif
                                    </div>
                                </div>
                            </td>
                            <td class="fw-semibold">{{ $item->quantity }}</td>
                            <td><span class="badge {{ $reasonBadge[$item->reason] ?? 'text-bg-light border text-dark' }}">{{ $item->reasonLabel() }}</span></td>
                            <td class="text-nowrap">{{ $money($item->total_value) }} ج.م</td>
                            <td>
                                @if ($item->isVoided())
                                    <span class="badge text-bg-secondary">ملغي</span>
                                @else
                                    <span class="badge text-bg-success">مسجَّل</span>
                                @endif
                            </td>
                            <td class="small">{{ $item->created_by_name }}</td>
                            <td><a href="{{ route('damaged.show', $item) }}" class="btn btn-sm btn-outline-primary">تفاصيل</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-body-secondary py-5">لا توجد سجلات هالِك.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($items->hasPages())
            <div class="card-footer">{{ $items->links() }}</div>
        @endif
    </div>
@endsection
