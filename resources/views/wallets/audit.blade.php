@extends('layouts.app')

@section('main')
    @include('wallets._nav')

    <h3 class="mb-3">سجل العمليات</h3>

    <form method="GET" action="{{ route('wallets.audit.index') }}" class="card shadow-sm mb-3">
        <div class="card-body row g-2 align-items-end">
            <div class="col-md-3">
                <label for="q" class="form-label small mb-1">بحث</label>
                <input type="text" id="q" name="q" class="form-control"
                       value="{{ $filters['q'] ?? '' }}"
                       placeholder="اسم، IP، رقم، كلمة عربية...">
            </div>

            <div class="col-md-3">
                <label for="action" class="form-label small mb-1">نوع الحدث</label>
                <select id="action" name="action" class="form-select">
                    <option value="">الكل</option>
                    @foreach ($actions as $code => $label)
                        <option value="{{ $code }}" @selected(($filters['action'] ?? '') === $code)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label for="from" class="form-label small mb-1">من تاريخ</label>
                <input type="date" id="from" name="from" class="form-control"
                       value="{{ $filters['from'] ?? '' }}">
            </div>

            <div class="col-md-2">
                <label for="to" class="form-label small mb-1">إلى تاريخ</label>
                <input type="date" id="to" name="to" class="form-control"
                       value="{{ $filters['to'] ?? '' }}">
            </div>

            <div class="col-md-2">
                <button class="btn btn-primary w-100">بحث</button>
            </div>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th>الوقت</th>
                        <th>المستخدم</th>
                        <th>الحدث</th>
                        <th>العنصر</th>
                        <th>التفاصيل</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="text-nowrap" dir="ltr">{{ $log['time'] }}</td>
                            <td>{{ $log['user'] }}</td>
                            <td>
                                <span class="badge {{ $log['badge'] }}">{{ $log['action'] }}</span>
                            </td>
                            <td>{{ $log['subject'] }}</td>
                            <td class="small">
                                @forelse ($log['details'] as [$label, $value])
                                    <div>
                                        <span class="text-body-secondary">{{ $label }}:</span>
                                        {{ $value }}
                                    </div>
                                @empty
                                    —
                                @endforelse
                            </td>
                            <td class="small" dir="ltr">{{ $log['ip'] ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-body-secondary py-4">
                                لا توجد سجلات.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="card-footer">{{ $logs->links() }}</div>
        @endif
    </div>
@endsection
