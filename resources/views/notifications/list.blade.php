@extends('layouts.app')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>الإشعارات</h1>
</div>

<div class="mb-3 d-flex justify-content-end">
    <a href="{{ route('notifications.markAllRead') }}" class="btn btn-sm btn-outline-primary">وضع الكل كمُقروء</a>
</div>

<div class="list-group">
    @forelse($notifications as $notification)
        <a href="{{ route('notifications.markRead', $notification->id) }}" class="list-group-item mb-2 text-decoration-none {{ $notification->is_read ? 'text-muted' : '' }}">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">{{ $notification->title }} @if(!$notification->is_read) <span class="badge bg-primary">جديد</span> @endif</h5>
                    <p class="mb-1">{{ $notification->message }}</p>
                </div>
                <small class="text-muted">{{ $notification->created_at->format('Y-m-d') }}</small>
            </div>
        </a>
    @empty
        <div class="alert alert-info">لا توجد إشعارات نشطة في الوقت الحالي.</div>
    @endforelse
</div>
@endsection
