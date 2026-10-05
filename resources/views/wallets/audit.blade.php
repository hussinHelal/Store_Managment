@extends('layouts.app')

@section('main')
    @include('wallets._nav')

    <h3 class="mb-3">سجل العمليات</h3>

    <form method="GET" action="{{ route('wallets.audit.index') }}" class="card shadow-sm mb-3">
        <div class="card-body row g-2 align-items-end">
            <div class="col-md-8">
                <label for="action" class="form-label small mb-1">تصفية حسب نوع السجل</label>
                <select id="action" name="action" class="form-select">
                    <option value="">كل السجلات</option>
                    @foreach ($actionFilters as $prefix => $label)
                        <option value="{{ $prefix }}" @selected($action === $prefix)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4"><button class="btn btn-primary w-100" type="submit"><i class="fa-solid fa-magnifying-glass ms-1" aria-hidden="true"></i> بحث</button></div>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>التاريخ والوقت</th><th>المستخدم</th><th>الحدث</th><th>العنصر</th><th>التفاصيل</th><th>عنوان IP</th></tr></thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="text-nowrap" dir="ltr">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                            <td>{{ $log->user_name }}</td>
                            <td>{{ $log->action_label }}</td>
                            <td>{{ $log->subject_label }}@if ($log->subject_id) #{{ $log->subject_id }}@endif</td>
                            <td class="small">
                                @forelse ($log->meta_details as $detail)
                                    <div><span class="text-body-secondary">{{ $detail['label'] }}:</span> {{ $detail['value'] }}</div>
                                @empty
                                    <span class="text-body-secondary">—</span>
                                @endforelse
                            </td>
                            <td class="small" dir="ltr">{{ $log->ip }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-body-secondary py-4">لا توجد سجلات مطابقة.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('components.pagination', ['collection' => $logs, 'paginationLabel' => 'سجل'])
    </div>
@endsection
