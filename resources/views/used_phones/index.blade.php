@extends('layouts.app')
@section('title', 'الموبايلات المستعملة')
@section('content')
<div class="container-fluid py-3" dir="rtl">
    <div class="page-header"><div><h1>الموبايلات المستعملة</h1><p class="text-secondary mb-0">سجل الاستلام والفحص والتسعير وحالة البيع.</p></div></div>
    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @can('page.used_phones.manage')
        <details class="card shadow-sm mb-4"><summary class="card-header fw-bold" style="cursor:pointer">تسجيل هاتف مستعمل جديد</summary><div class="card-body">@include('used_phones.form', ['phone' => null])</div></details>
    @endcan
    <form method="GET" class="row g-2 mb-3" role="search">
        <div class="col-md-6"><label class="visually-hidden" for="used-phone-search">بحث</label><input id="used-phone-search" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="بحث بالماركة أو الموديل أو IMEI أو البائع"></div>
        <div class="col-md-3"><label class="visually-hidden" for="used-phone-status">حالة البيع</label><select id="used-phone-status" name="status" class="form-select"><option value="">كل الحالات</option>@foreach (['in_stock'=>'في المخزون','reserved'=>'محجوز','sold'=>'مباع','returned'=>'مرتجع'] as $key => $label)<option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-3 d-flex gap-2"><button class="btn btn-outline-primary">بحث</button><a class="btn btn-outline-secondary" href="{{ route('used_phones.index') }}">مسح</a></div>
    </form>
    <div class="table-responsive"><table class="table table-striped table-hover align-middle"><thead><tr><th>الصورة</th><th>الجهاز والمعرفات</th><th>الفحص</th><th>الأسعار</th><th>البائع السابق</th><th>الحالة</th><th>الإجراءات</th></tr></thead><tbody>
    @forelse ($phones as $phone)
        <tr><td>@if ($phone->image_path)<a href="{{ app(\App\Services\PictureStorageService::class)->url($phone->image_path, 'used-phones') }}" target="_blank" rel="noopener"><img src="{{ app(\App\Services\PictureStorageService::class)->url($phone->image_path, 'used-phones', true) }}" alt="صورة {{ $phone->brand }} {{ $phone->model }}" class="img-thumbnail" style="width:60px;height:60px;object-fit:cover"></a>@else<span class="text-secondary">بدون صورة</span>@endif</td>
        <td><strong>{{ $phone->brand }} {{ $phone->model }}</strong><div class="small text-secondary">IMEI: {{ $phone->imei ?: '—' }}<br>التسلسلي: {{ $phone->serial_number ?: '—' }}</div></td>
        <td>{{ ['excellent'=>'ممتاز','good'=>'جيد جداً','fair'=>'جيد','poor'=>'يحتاج إلى إصلاح'][$phone->condition] }}<div class="small text-secondary">{{ $phone->notes }}</div></td>
        <td>شراء: {{ \App\Support\Money::format(\App\Support\Money::cents($phone->purchase_price)) }} ج.م<br>بيع: {{ \App\Support\Money::format(\App\Support\Money::cents($phone->asking_price)) }} ج.م</td>
        <td>{{ $phone->seller_name }}<div class="small text-secondary" dir="ltr">{{ $phone->seller_phone }}</div><div class="small text-secondary">{{ $phone->purchase_date->format('Y-m-d') }}</div></td>
        <td>{{ ['in_stock'=>'في المخزون','reserved'=>'محجوز','sold'=>'مباع','returned'=>'مرتجع'][$phone->status] }}</td>
        <td>@can('page.used_phones.manage')<details><summary class="btn btn-sm btn-outline-primary">تعديل</summary><div class="card card-body mt-2" style="min-width: min(80vw, 720px)">@include('used_phones.form', ['phone' => $phone])</div></details><form class="mt-2" action="{{ route('used_phones.destroy', $phone) }}" method="POST" onsubmit="return confirm('هل تريد حذف سجل هذا الهاتف؟')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" @disabled($phone->status === 'sold')>حذف</button></form>@else<span class="text-secondary">عرض فقط</span>@endcan</td></tr>
    @empty<tr><td colspan="7" class="text-center py-4">لا توجد هواتف مستعملة مطابقة للبحث.</td></tr>@endforelse
    </tbody></table></div>
    {{ $phones->links() }}
</div>
@endsection
