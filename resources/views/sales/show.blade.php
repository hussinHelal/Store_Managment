@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 48rem">
    <div class="mb-4">
        <a href="{{ route('sales.index') }}" class="link-secondary text-decoration-none"><i class="fa-solid fa-arrow-right me-1" aria-hidden="true"></i> المبيعات</a>
        <h1 class="h3 mt-2">تفاصيل البيع #{{ $sale->id }}</h1>
    </div>
    <dl class="row border-top pt-3">
        <dt class="col-sm-3">المنتج</dt><dd class="col-sm-9">{{ $sale->products?->name ?? '—' }}</dd>
            <dt class="col-sm-3">العميل</dt><dd class="col-sm-9">{{ $sale->customer?->name ?? '—' }}</dd>
            <dt class="col-sm-3">المنتج</dt><dd class="col-sm-9">{{ $sale->products?->name ?? '—' }}</dd>
        <dt class="col-sm-3">الكمية</dt><dd class="col-sm-9">{{ $sale->quantity }}</dd>
        <dt class="col-sm-3">الإجمالي</dt><dd class="col-sm-9">{{ number_format((float) $sale->total, 2) }}</dd>
        <dt class="col-sm-3">طريقة الدفع</dt><dd class="col-sm-9">{{ $sale->payment_type }}</dd>
        <dt class="col-sm-3">التاريخ</dt><dd class="col-sm-9">{{ $sale->created_at?->format('Y-m-d') }}</dd>
    </dl>
</div>
@endsection