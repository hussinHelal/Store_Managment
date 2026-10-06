@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 58rem">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <a href="{{ route('installments.index') }}" class="link-secondary text-decoration-none"><i class="fa-solid fa-arrow-right me-1" aria-hidden="true"></i> الديون</a>
            <h1 class="h3 mt-2">تفاصيل الدين #{{ $installment->id }}</h1>
        </div>
        @if(!$installment->invoice_id)
            <a href="{{ route('installments.edit', $installment) }}" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-1" aria-hidden="true"></i> تعديل</a>
        @endif
    </div>

    <dl class="row border-top pt-3">
        <dt class="col-sm-4">العميل</dt><dd class="col-sm-8">{{ $installment->customer }}</dd>
        <dt class="col-sm-4">المنتجات</dt><dd class="col-sm-8">{{ $installment->item_names ?: ($installment->product?->name ?? 'لا يوجد اسم') }}</dd>
        <dt class="col-sm-4">إجمالي المبلغ</dt><dd class="col-sm-8">{{ number_format((float) $installment->product_price, 2) }}</dd>
        <dt class="col-sm-4">الكمية</dt><dd class="col-sm-8">{{ $installment->item_quantity ?: $installment->quantity }}</dd>
        <dt class="col-sm-4">المدفوع</dt><dd class="col-sm-8">{{ number_format((float) $installment->paid_amount, 2) }}</dd>
        <dt class="col-sm-4">المتبقي</dt><dd class="col-sm-8">{{ number_format((float) $installment->remaining, 2) }}</dd>
        <dt class="col-sm-4">الحالة</dt><dd class="col-sm-8">{{ $installment->status }}</dd>
        <dt class="col-sm-4">تاريخ الدفع</dt><dd class="col-sm-8">{{ $installment->payment_date?->format('Y-m-d') ?? '—' }}</dd>
        <dt class="col-sm-4">موعد الدفع التالي</dt><dd class="col-sm-8">{{ $installment->next_payment_date?->format('Y-m-d') ?? '—' }}</dd>
    </dl>
</div>
@endsection