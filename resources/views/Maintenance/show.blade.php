@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 56rem">
	<div class="mb-4">
		<a href="{{ route('maintenance.index') }}" class="link-secondary text-decoration-none"><i class="fa-solid fa-arrow-right me-1" aria-hidden="true"></i> الصيانة</a>
		<h1 class="h3 mt-2">تفاصيل طلب الصيانة #{{ $maintenance->id }}</h1>
	</div>
	<dl class="row border-top pt-3">
		<dt class="col-sm-3">الجهاز</dt><dd class="col-sm-9">{{ $maintenance->name }}</dd>
		<dt class="col-sm-3">المالك</dt><dd class="col-sm-9">{{ $maintenance->owner }}</dd>
		<dt class="col-sm-3">الهاتف</dt><dd class="col-sm-9">{{ $maintenance->phone }}</dd>
		<dt class="col-sm-3">العنوان</dt><dd class="col-sm-9">{{ $maintenance->address }}</dd>
		<dt class="col-sm-3">الحالة</dt><dd class="col-sm-9">{{ $maintenance->status }}</dd>
		<dt class="col-sm-3">الوصف</dt><dd class="col-sm-9">{{ $maintenance->description }}</dd>
		<dt class="col-sm-3">ملاحظات</dt><dd class="col-sm-9">{{ $maintenance->notes ?: '—' }}</dd>
		<dt class="col-sm-3">تاريخ الاستلام</dt><dd class="col-sm-9">{{ $maintenance->requested_date?->format('Y-m-d') }}</dd>
		<dt class="col-sm-3">تاريخ الإنجاز</dt><dd class="col-sm-9">{{ $maintenance->completed_date?->format('Y-m-d') ?? '—' }}</dd>
	</dl>
	<a href="{{ route('maintenance.edit', $maintenance) }}" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-1" aria-hidden="true"></i> تعديل</a>
</div>
@endsection
