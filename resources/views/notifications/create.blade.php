@extends('layouts.app')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <h1>إضافة إشعار جديد</h1>
</div>

<form action="{{ route('admin.notifications.store') }}" method="POST">
    @csrf

    <div class="mb-3">
        <label for="title" class="form-label">العنوان</label>
        <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}" required>
    </div>

    <div class="mb-3">
        <label for="message" class="form-label">الرسالة</label>
        <textarea class="form-control" id="message" name="message" rows="4" required>{{ old('message') }}</textarea>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label for="starts_at" class="form-label">يبدأ في</label>
            <input type="datetime-local" class="form-control" id="starts_at" name="starts_at" value="{{ old('starts_at') }}">
        </div>
        <div class="col-md-6">
            <label for="ends_at" class="form-label">ينتهي في</label>
            <input type="datetime-local" class="form-control" id="ends_at" name="ends_at" value="{{ old('ends_at') }}">
        </div>
    </div>

    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
        <label class="form-check-label" for="is_active">مفعل</label>
    </div>

    <button type="submit" class="btn btn-primary">حفظ الإشعار</button>
    <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary">إلغاء</a>
</form>
@endsection
