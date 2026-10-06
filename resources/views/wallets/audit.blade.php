@extends('layouts.app')

@section('main')
    @include('wallets._nav')

    <h3 class="mb-3">سجل العمليات</h3>

    <form method="GET" action="{{ route('wallets.audit.index') }}" class="card shadow-sm mb-3">
        <div class="card-body row g-2 align-items-end">
            <div class="col-md-8">
                <label for="action" class="form-label small mb-1">نوع الحدث (يبدأ بـ)</label>
                <input type="text" id="action" name="action" class="form-control" dir="ltr" maxlength="60" value="{{ $action }}" placeholder="transaction. / wallet. / expense. / damaged. / cash.">
            </div>
            <div class="col-md-4"><button class="btn btn-primary w-100">بحث</button></div>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead><tr><th>الوقت</th><th>المستخدم</th><th>الحدث</th><th>العنصر</th><th>التفاصيل</th><th>IP</th></tr></thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="text-nowrap" dir="ltr">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                            <td>{{ $log->user_name }}</td>
                            <td dir="ltr">{{ $log->action }}</td>
                            <td dir="ltr">{{ $log->subject_type }}@if ($log->subject_id) #{{ $log->subject_id }}@endif</td>
                            <td class="small" dir="ltr">{{ $log->meta ? json_encode($log->meta, JSON_UNESCAPED_UNICODE) : '' }}</td>
                            <td class="small" dir="ltr">{{ $log->ip }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-body-secondary py-4">لا توجد سجلات.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())<div class="card-footer">{{ $logs->links() }}</div>@endif
    </div>
@endsection
