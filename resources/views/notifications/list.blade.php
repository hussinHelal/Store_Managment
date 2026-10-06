@extends('layouts.app')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>الإشعارات</h1>
</div>

<form method="POST" action="{{ route('notifications.markAllRead') }}" class="mb-3 d-flex justify-content-end">
    @csrf
    <button type="submit" class="btn btn-sm btn-outline-primary">وضع الكل كمُقروء</button>
</form>

<div class="list-group">
    @forelse($notifications as $notification)
        <button type="button" class="list-group-item list-group-item-action mb-2 text-start {{ $notification->is_read ? 'text-muted' : '' }}" data-notification-read data-notification-id="{{ $notification->id }}" data-read-url="{{ route('notifications.markRead', $notification->id) }}" aria-pressed="{{ $notification->is_read ? 'true' : 'false' }}">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">{{ $notification->title }} @if(!$notification->is_read) <span class="badge bg-primary">جديد</span> @endif</h5>
                    <p class="mb-1">{{ $notification->message }}</p>
                </div>
                <small class="text-muted">{{ $notification->created_at->format('Y-m-d') }}</small>
            </div>
        </button>
    @empty
        <div class="alert alert-info">لا توجد إشعارات نشطة في الوقت الحالي.</div>
    @endforelse
</div>

@include('components.pagination', ['collection' => $notifications])
@endsection
