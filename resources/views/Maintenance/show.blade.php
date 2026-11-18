@extends('layouts.app')
@section('content')
@php $statusLabel = match($maintenance->status) { 'قيد الانتظار' => 'قيد الانتظار', 'مكتمل' => 'مكتمل', 'مرفوض' => 'مرفوض', default => $maintenance->status }; @endphp
<div class="container py-4" dir="rtl" style="max-width: 56rem">
    <div class="mb-4"><a href="{{ route('maintenance.index') }}" class="link-secondary text-decoration-none">العودة إلى الصيانة</a><h1 class="h3 mt-2">تفاصيل طلب الصيانة #{{ $maintenance->id }}</h1></div>
    <dl class="row border-top pt-3">
        <dt class="col-sm-3">الجهاز</dt><dd class="col-sm-9">{{ $maintenance->name }}</dd>
        <dt class="col-sm-3">المالك</dt><dd class="col-sm-9">{{ $maintenance->owner }}</dd>
        <dt class="col-sm-3">الهاتف</dt><dd class="col-sm-9">{{ $maintenance->phone }}</dd>
        <dt class="col-sm-3">العنوان</dt><dd class="col-sm-9">{{ $maintenance->address }}</dd>
        <dt class="col-sm-3">الحالة</dt><dd class="col-sm-9">{{ $statusLabel }}</dd>
        <dt class="col-sm-3">الوصف</dt><dd class="col-sm-9">{{ $maintenance->description }}</dd>
        <dt class="col-sm-3">ملاحظات</dt><dd class="col-sm-9">{{ $maintenance->notes ?: '—' }}</dd>
        <dt class="col-sm-3">تاريخ الاستلام</dt><dd class="col-sm-9">{{ $maintenance->requested_date?->format('Y-m-d') ?? '—' }}</dd>
        <dt class="col-sm-3">تاريخ الإنجاز</dt><dd class="col-sm-9">{{ $maintenance->completed_date?->format('Y-m-d') ?? '—' }}</dd>
    </dl>
    @if ($maintenance->image_path)
        <div class="mb-3"><h2 class="h5">صورة الجهاز</h2><a href="{{ app(\App\Services\PictureStorageService::class)->url($maintenance->image_path, 'maintenance') }}" target="_blank" rel="noopener"><img src="{{ app(\App\Services\PictureStorageService::class)->url($maintenance->image_path, 'maintenance', true) }}" alt="صورة الجهاز" class="img-thumbnail" style="max-width: 320px"></a></div>
    @endif
    <div class="d-flex flex-wrap gap-2"><a href="{{ route('maintenance.showRepaired', $maintenance) }}" class="btn btn-outline-warning">تغيير الحالة</a><a href="{{ route('maintenance.edit', $maintenance) }}" class="btn btn-outline-primary">تعديل البيانات</a><a href="{{ route('maintenance.index') }}" class="btn btn-outline-secondary">العودة للقائمة</a></div>
</div>
@endsection
