@extends('layouts.app')
@section('content')
<div class="container py-4" dir="rtl">
    <h1 class="h3 mb-4">تغيير حالة طلب الصيانة #{{ $maintenance->id }}</h1>
    @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form action="{{ route('maintenance.repaired', $maintenance) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="status" class="form-label">الحالة</label>
            <select class="form-select" id="status" name="status" required>
                <option value="قيد الانتظار" @selected(old('status', $maintenance->status) === 'قيد الانتظار')>قيد الانتظار</option>
                <option value="مكتمل" @selected(old('status', $maintenance->status) === 'مكتمل')>مكتمل</option>
                <option value="مرفوض" @selected(old('status', $maintenance->status) === 'مرفوض')>مرفوض</option></select>
        </div>
        <div class="d-flex gap-2"><button type="submit" class="btn btn-primary">حفظ الحالة</button><a href="{{ route('maintenance.index') }}" class="btn btn-outline-secondary">العودة</a></div>
    </form>
</div>
@endsection