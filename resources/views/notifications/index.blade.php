@extends('layouts.app')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>إدارة الإشعارات</h1>
    <a href="{{ route('admin.notifications.create') }}" class="btn btn-primary">إضافة إشعار جديد</a>
</div>

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>العنوان</th>
                <th>الرسالة</th>
                <th>مباشر من</th>
                <th>فعال</th>
                <th>ينتهي في</th>
                <th>إجراءات</th>
            </tr>
        </thead>
        <tbody>
            @foreach($notifications as $notification)
                <tr>
                    <td>{{ $notification->id }}</td>
                    <td>{{ $notification->title }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($notification->message, 80) }}</td>
                    <td>{{ optional($notification->creator)->name ?? 'N/A' }}</td>
                    <td>{{ $notification->is_active ? 'نعم' : 'لا' }}</td>
                    <td>{{ optional($notification->ends_at)->format('Y-m-d H:i') ?? 'بدون' }}</td>
                    <td>
                        <form action="{{ route('admin.notifications.destroy', $notification->id) }}" method="POST" onsubmit="return confirm('هل تريد حذف هذا الإشعار؟');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger">حذف</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{ $notifications->links() }}
@endsection
